import json

from django.http import HttpResponse, JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated, AllowAny
from rest_framework.views import APIView
from rest_framework_jwt.views import obtain_jwt_token, verify_jwt_token, refresh_jwt_token

from monitor_web.models import Server, UserProfile
# Create your views here.
from monitor_web.serializers import ServerSerializer, UserProfileSerializer


def index(request):
    return HttpResponse("hello")


class UserViewSet(viewsets.ModelViewSet):
    """
    API endpoint that allows users to be viewed or edited.
    """
    queryset = UserProfile.objects.all()
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
        response =  obtain_jwt_token(self.request._request, *args, **kwargs)
        response.data['code']=20000
        response.data['data']={'token':response.data['token']}
        # 返回格式{"code":20000,"data":{"token":"admin"}}
        return response


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
