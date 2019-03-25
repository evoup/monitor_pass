import logging

from django.http import HttpResponse, JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated, AllowAny
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework_jwt.views import obtain_jwt_token, verify_jwt_token, refresh_jwt_token

from monitor_web import models
from monitor_web.models import Server, Profile
# Create your views here.
from monitor_web.serializers import ServerSerializer, UserProfileSerializer
from web.common.paging import MyPageNumberPagination
from web.common.order import getOrderList

logger = logging.getLogger(__name__)

def index(request):
    return HttpResponse("hello")


class UserViewSet(viewsets.ModelViewSet):
    """
    API endpoint that allows users to be viewed or edited.
    """
    queryset = Profile.objects.all()
    serializer_class = UserProfileSerializer


@permission_classes((AllowAny,))
class LoginStatus(APIView):
    """
    获取登录状态
    """

    def post(self, *args, **kwargs):
        refresh_jwt_token(self.request._request, *args, **kwargs)
        return verify_jwt_token(self.request._request, *args, **kwargs)


@permission_classes((AllowAny,))
class Login(APIView):
    """
    进行登录
    """

    def post(self, *args, **kwargs):
        jwt_response =  obtain_jwt_token(self.request._request, *args, **kwargs)
        if 'token' in jwt_response.data:
            jwt_response.data['code']=20000
            jwt_response.data['data']={'token':jwt_response.data['token']}
            # 返回格式{"code":20000,"data":{"token":"admin"}}
            return jwt_response
        else:
            return Response(data={'code':12, 'message':'登录失败，用户名或者密码错误！'})


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
class UserInfo(APIView):

    def get(self, *args, **kwargs):
        ret = {
            "code": 20000,
            "data": {
                "roles": [
                    "admin"
                ],
                "name": "admin",
                "avatar": "https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif"
            }
        }
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(UserInfo, self).dispatch(*args, **kwargs)



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
            "code": 20000,
            'message': 'f '
        }
        # logger.info(data)
        try:
            Server.objects.create(name=data['name'], agent_address=data['agent_addr'], jmx_address=data['jmx_addr'], snmp_address=data['snmp_addr'])
        except:
            # logger.error("error")
            ret = {
                'code': 40000,
                'message': '服务器创建失败'
            }
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(ServerInfo, self).dispatch(*args, **kwargs)


@permission_classes((IsAuthenticated,))
class ServerList(APIView):

    def get(self, request, pk=None, format=None):
        orderList, prop = getOrderList(request)
        # 获取所有数据
        records = models.Server.objects.all() if prop == '' else models.Server.objects.order_by(*orderList)
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


@csrf_exempt
@permission_classes((IsAuthenticated,))
def logout(request):
    """
    登出
    :return:
    TODO：JWT下更好的做法是对过期的token做redis标记
    """
    if request.method == 'POST':
        ret = {
            "code": 20000,
            "data": None
        }
        return JsonResponse(ret, safe=False)
