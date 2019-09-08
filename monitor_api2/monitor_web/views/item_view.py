import traceback

from django.contrib.auth.decorators import permission_required
from django.contrib.auth.models import User
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import MonitorItem
from monitor_web.serializers import ItemSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class ItemList(APIView):
    @method_decorator(permission_required('monitor_web.view_monitoritem', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取指定模板下的监控项列表
        """
        # 没有指定就是1，不能全部查询太慢了
        cond = {'template_id': request.GET['template_id']} if 'template_id' in request.GET else {'template_id': 1}
        if 'ids' in request.GET:
            cond = {'id__in': request.GET['ids'].split(',')}
        page_data, count = paging_request(request, models.MonitorItem, self,
                                          filter=cond)
        # 对数据进行序列化
        serializer = ItemSerializer(instance=page_data, many=True, context={'user_id': request.user.id})
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "count": count,
                "items": serializer.data,
                "page": {
                    "currPage": request.GET.get('page', constant.DEFAULT_CURRENT_PAGE),
                    "pageSize": request.GET.get('size', constant.DEFAULT_PAGE_SIZE)
                }
            }
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class ItemInfo(APIView):
    @method_decorator(permission_required('monitor_web.view_monitoritem', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取指定监控项
        """
        item = models.MonitorItem.objects.get(
            id=self.request.query_params['id']) if self.request.query_params.__contains__('id') else None
        serializer = ItemSerializer(instance=item, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.add_monitoritem', raise_exception=True))
    def post(self, request, pk=None, format=None):
        """
        在指定模板下添加监控项
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建监控项失败'
        }
        data = JSONParser().parse(self.request)
        item = MonitorItem.objects.filter(key=data['key'])
        if item.count() > 0:
            ret = {
                'code': constant.BACKEND_CODE_OPT_FAIL,
                'message': '创建监控项失败，已经存在'
            }
            return JsonResponse(ret, safe=False)
        else:
            try:
                MonitorItem.objects.create(name=data['name'], delay=data['interval'], key=data['key'], desc=data['desc'],
                                           multiplier=data['multiplier'], unit=data['unit'],
                                           template_id=data['template_id'], host_id=0)
            except:
                print(traceback.format_exc())
                return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建监控项成功'
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class ItemStatus(APIView):
    @method_decorator(permission_required('monitor_web.change_monitoritem', raise_exception=True))
    def put(self, request, pk=None, format=None):
        """
        修改指定监控项状态，开始或者关闭
        """
        user = User.objects.get(id=request.user.id)
        item = models.MonitorItem.objects.get(id=request.data['id'])
        template = models.Template.objects.get(id=request.data['template_id'])
        qs = models.RelationUserItem.objects.filter(user=user, item=item, template=template)
        if qs.count() is 0:
            qs.create(user=user, item=item, template=template, status=request.data['status'])
        else:
            qs.update(user=user, item=item, template=template, status=request.data['status'])
        ret = {
            'code': constant.BACKEND_CODE_OK,
            'message': '更新监控项状态成功'
        }
        return JsonResponse(ret, safe=False)
