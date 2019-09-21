import re
import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import TriggerFunctionSerializer
from web.common import constant
from web.common.order import param_to_order
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class TriggerList(APIView):
    @method_decorator(permission_required('monitor_web.view_trigger', raise_exception=True))
    def get(self, request, pk=None, format=None):
        template_id = self.request.query_params['template_id']
        item_ids = []
        for item in models.MonitorItem.objects.filter(template_id=template_id, item_copy_from=0).all():
            item_ids.append(item.id)
        # 汇总每个item对应的触发器

        page_data, count = paging_request(request, models.Function, self, filter={'item__in': item_ids})
        # 对数据进行序列化

        serializer = TriggerFunctionSerializer(instance=page_data, many=True)

        # 字段在Function表外，手动再对trigger_name排序
        data = sorted(serializer.data, key=lambda kv: kv['trigger_name'],
                      reverse=param_to_order(request)) if param_to_order(request) is not None else serializer.data
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "count": count,
                "items": data,
                "page": {
                    "currPage": request.GET.get('page', constant.DEFAULT_CURRENT_PAGE),
                    "pageSize": request.GET.get('size', constant.DEFAULT_PAGE_SIZE)
                }
            }
        }
        return JsonResponse(ret, safe=False)


class TriggerInfo(APIView):
    @method_decorator(permission_required('monitor_web.change_trigger', raise_exception=True))
    def get(self, request, pk=None, format=None):
        trigger_id = self.request.query_params['id']
        data = models.Function.objects.filter(trigger=trigger_id).all()[0]
        serializer = TriggerFunctionSerializer(instance=data, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.add_trigger', raise_exception=True))
    def post(self, request, pk=None, format=None):
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建触发器失败'
        }
        # 匹配触发器中的值
        pattern = re.compile(r'{([^}]*)}(.*)', re.S)
        trigger = models.Trigger.objects.create(name=data['name'], expression=data['expression'],
                                                template_id=data['template_id'])
        db_express = re.sub(pattern, expression_replace_callback2(extra_arg={'trigger_id': trigger.id}),
                            data['expression'])
        try:
            models.Trigger.objects.filter(id=trigger.id).update(expression=db_express)
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        # TODO 这里需要数据库需要加锁
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '创建触发器成功'
        }
        return JsonResponse(ret, safe=False)


class expression_replace_callback2(object):
    """
    re.sub的回调函数，替换调原本表达式中item key成id
    """

    # 初始化属性
    def __init__(self, extra_arg):
        self.extra_arg = extra_arg

    # 使类的实例变得callable
    def __call__(self, match_obj):
        # 'proc.num[].avg(5m,0)'
        # 把监控项和函数以及参数入库
        item_function = match_obj.group(1)
        operator_value = match_obj.group(2)
        m = re.match(r"(.*)\.([avg|last|diff|change]*)((.*))", item_function).groups()
        item_key = m[0]
        function_name = m[1]
        param = m[2].replace('(', '')
        param = param.replace(')', '')
        # 分别入库得到id
        item_object = models.MonitorItem.objects.get(key=item_key, host_id=0)
        function = models.Function.objects.create(name=function_name, parameter=param,
                                                  item=models.MonitorItem.objects.get(key=item_key, host_id=0),
                                                  trigger=models.Trigger.objects.get(id=self.extra_arg['trigger_id']))
        x = "{%s}%s" % (function.id, operator_value)
        return x
