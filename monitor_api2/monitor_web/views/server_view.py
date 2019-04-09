import logging
import traceback

from django.http import HttpResponse, JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import Server, Asset
# Create your views here.
from monitor_web.serializers import ServerSerializer, IDCSerializer, ServerGroupSerializer
from web.common.order import getOrderList
from web.common.paging import MyPageNumberPagination

logger = logging.getLogger(__name__)

def index(request):
    return HttpResponse("hello")


@permission_classes((IsAuthenticated,))
class ServerList(APIView):

    def get(self, *args, **kwargs):
        servers = Server.objects.all()
        serializer = ServerSerializer(servers, many=True)
        return JsonResponse(serializer.data, safe=False)

    def post(self, *args, **kwargs):
        data = JSONParser().parse(self.request)
        serializer = ServerSerializer(data=data)
        if serializer.is_valid():
            serializer.save()
            return JsonResponse(serializer.data, status=201)
        return JsonResponse(serializer.errors, status=400)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(ServerList, self).dispatch(*args, **kwargs)


@permission_classes((IsAuthenticated,))
class ServerInfo(APIView):
    """
    单台服务器
    """

    def get(self, *args, **kwargs):
        ret = {
            "code": 20000,
            "data": {
                "name": "server1"
            }
        }
        return JsonResponse(ret, safe=False)

    def post(self, *args, **kwargs):
        data = JSONParser().parse(self.request)
        ret = {
            'code': 40000,
            'message': '服务器创建失败'
        }
        try:
            ser = IDCSerializer(data={'name': data['idc']})
            if not ser.is_valid():
                return JsonResponse({'code':40001, 'message':ser.errors}, safe=False)
            else:
                i = ser.create(ser.validated_data)
                i.save()
                a = Asset.objects.create(device_type_id=1, device_status_id=1, idc=i)
                Server.objects.create(name=data['name'], agent_address=data['agent_addr'], jmx_address=data['jmx_addr'], snmp_address=data['snmp_addr'], asset=a)
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': 20001,
            'message': '服务器创建成功'
        }
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(ServerInfo, self).dispatch(*args, **kwargs)


@permission_classes((IsAuthenticated,))
class ServerList(APIView):

    def get(self, request, pk=None, format=None):
        order_list, prop = getOrderList(request)
        # 获取所有数据
        records = models.Server.objects.all() if prop == '' else models.Server.objects.order_by(*order_list)
        # 创建分页对象，这里是自定义的MyPageNumberPagination
        pg = MyPageNumberPagination(request.GET.get('size', 7))
        # 获取分页的数据
        page_roles = pg.paginate_queryset(queryset=records, request=request, view=self)
        # 对数据进行序列化
        ser = ServerSerializer(instance=page_roles, many=True)
        ret = {
            "code": 20000,
            "data": {
                "count": len(records),
                "items": ser.data,
                "page": {
                    "currPage": request.GET.get('page', 1),
                    "pageSize": request.GET.get('size', 5)
                }
            }
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class ServerGroupList(APIView):

    def get(self, request, pk=None, format=None):
        order_list, prop = getOrderList(request)
        records = models.ServerGroup.objects.all() if prop == '' else models.ServerGroup.objects.order_by(*order_list)
        pg = MyPageNumberPagination(request.GET.get('size', 7))
        page_roles = pg.paginate_queryset(queryset=records, request=request, view=self)
        ser = ServerGroupSerializer(instance=page_roles, many=True)
        ret = {
            "code": 20000,
            "data": {
                "count": len(records),
                "items": ser.data,
                "page": {
                    "currPage": request.GET.get('page', 1),
                    "pageSize": request.GET.get('size', 5)
                }
            }
        }
        return JsonResponse(ret, safe=False)

