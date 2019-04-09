from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import permission_classes
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework_jwt.views import obtain_jwt_token, verify_jwt_token, refresh_jwt_token

from monitor_web.models import Profile
# Create your views here.
from monitor_web.serializers import UserProfileSerializer


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
                "avatar": "user.png",
                "group": "super user"
            }
        }
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(UserInfo, self).dispatch(*args, **kwargs)
