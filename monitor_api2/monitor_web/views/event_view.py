from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import EventSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class EventList(APIView):
    @method_decorator(permission_required('monitor_web.view_event', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        获取事件列表
        """
        # TODO 需要写一个清理计划，删除事件中的target_id已经失效的记录
        trigger_ids = []
        if 'server' in self.request.query_params:
            # 寻找该服务器的所有触发器,找服务器对应的模板找function，找到item对应的所有trigger
            functions = models.Function.objects.filter(
                item__in=models.MonitorItem.objects.filter(host_id=self.request.query_params['server'])).all()
            for func in functions:
                trigger_ids.append(func.trigger.id)
        else:
            trigger_ids = list(models.Trigger.objects.all().values_list('id', flat=True))
        page_data, count = paging_request(request, models.Event, self, filter={'target_id__in': trigger_ids})
        serializer = EventSerializer(instance=page_data, many=True)
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
