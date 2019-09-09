from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import NotificationModeConfigSerializer
from web.common import constant


@permission_classes((IsAuthenticated,))
class NotificationModeList(APIView):
    @method_decorator(permission_required('monitor_web.view_notificationmode', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取告警接收设置列表
        """
        n = models.NotificationMode.objects.filter(id__gte=0).all()
        serializer = NotificationModeConfigSerializer(instance=n, many=True)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "items": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)
