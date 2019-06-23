import logging
import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from django.views.decorators.csrf import csrf_exempt
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import Server, Asset
# Create your views here.
from monitor_web.serializers import ServerSerializer, IDCSerializer, ServerGroupSerializer
from web.common import constant
from web.common.paging import paging_request

logger = logging.getLogger(__name__)


@permission_classes((IsAuthenticated,))
class ServerInfo(APIView):

    def get(self, *args, **kwargs):
        """
        获取单台服务器
        """
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "name": "server1"
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.add_server', raise_exception=True))
    def post(self, *args, **kwargs):
        """
        创建单台服务器
        """
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '服务器创建失败'
        }
        try:
            ser = IDCSerializer(data={'name': data['idc']})
            if not ser.is_valid():
                return JsonResponse({'code': 40001, 'message': ser.errors}, safe=False)
            else:
                i = ser.create(ser.validated_data)
                i.save()
                a = Asset.objects.create(device_type_id=1, device_status_id=1, idc=i)
                Server.objects.create(name=data['name'], agent_address=data['agent_addr'], jmx_address=data['jmx_addr'],
                                      snmp_address=data['snmp_addr'], asset=a)
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '服务器创建成功'
        }
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(ServerInfo, self).dispatch(*args, **kwargs)


@permission_classes((IsAuthenticated,))
class ServerList(APIView):
    @method_decorator(permission_required('monitor_web.view_server', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取服务器列表
        """
        page_data, count = paging_request(request, models.Server, self)
        # 对数据进行序列化
        serializer = ServerSerializer(instance=page_data, many=True)
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
class ServerGroupList(APIView):
    @method_decorator(permission_required('monitor_web.view_servergroup', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取服务器组列表
        """
        page_data, count = paging_request(request, models.ServerGroup, self)
        serializer = ServerGroupSerializer(instance=page_data, many=True)
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
class ServerGroupInfo(APIView):
    @method_decorator(permission_required('monitor_web.delete_server_group', raise_exception=True))
    def delete(self, *args, **kwargs):
        """
        删除服务组
        """
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '删除服务组成功'
        }
        serverGroup = models.ServerGroup.objects.get(id=self.request.query_params['id'])
        serverGroup.delete()
        return JsonResponse(ret, safe=False)
