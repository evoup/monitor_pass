from django.http import JsonResponse
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import DashboardServersSerializer
from web.common import constant


@permission_classes((IsAuthenticated,))
class DashboardServerList(APIView):
    def get(self, request, pk=None, format=None):
        server = models.Server.objects.all()
        serializer = DashboardServersSerializer(instance=server, many=True)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "count": server.count(),
                "items": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)
