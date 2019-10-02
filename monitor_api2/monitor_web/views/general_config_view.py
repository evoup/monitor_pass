import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import GeneralConfigSerializer
from web.common import constant


@permission_classes((IsAuthenticated,))
class GeneralConfig(APIView):
    @method_decorator(permission_required('monitor_web.view_generalconfig', raise_exception=True))
    def get(self, request, pk=None, format=None):
        config = models.GeneralConfig.objects.all()[0]
        serializer = GeneralConfigSerializer(instance=config, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.change_generalconfig', raise_exception=True))
    def put(self, request, pk=None, format=None):
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '修改常规设置失败'
        }
        config = models.GeneralConfig.objects.all()[0]
        try:
            models.GeneralConfig.objects.filter(id=config.id).update(grafana_api_key=data['api_key'],
                                                                     send_warn=data['send_warn'] == '1',
                                                                     stop_command=data['stop_command'],
                                                                     ssh_private_key_dir=data['ssh_private_key_dir'])
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '更新常规设置成功'
        }
        return JsonResponse(ret, safe=False)
