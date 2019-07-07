from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import TriggerSerializer
from web.common import constant


@permission_classes((IsAuthenticated,))
class TriggerList(APIView):
    @method_decorator(permission_required('monitor_web.view_trigger', raise_exception=True))
    def get(self, request, pk=None, format=None):
        item = models.MonitorItem.objects.get(id=self.request.query_params['id']) if self.request.query_params.__contains__('id') else None
        serializer = TriggerSerializer(instance=item, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
            "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)
