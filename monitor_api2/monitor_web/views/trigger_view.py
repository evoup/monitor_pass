import operator
from collections import OrderedDict

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import TriggerFunctionSerializer
from web.common import constant
from web.common.paging import paging_request, param_to_order


@permission_classes((IsAuthenticated,))
class TriggerList(APIView):
    @method_decorator(permission_required('monitor_web.view_trigger', raise_exception=True))
    def get(self, request, pk=None, format=None):
        template_id = self.request.query_params['template_id']
        item_ids = []
        for item in models.MonitorItem.objects.filter(template_id=template_id).all():
            item_ids.append(item.id)
        # 汇总每个item对应的触发器

        page_data, count = paging_request(request, models.Function, self, filter={'item__in': item_ids})
        # 对数据进行序列化

        serializer = TriggerFunctionSerializer(instance=page_data, many=True)

        # 字段在Function表外，手动再对trigger_name排序
        data = sorted(serializer.data, key=lambda kv: kv['trigger_name'], reverse=param_to_order(request)) if param_to_order(request) is not None else serializer.data
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
