from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import permission_classes
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework_jwt.views import obtain_jwt_token, verify_jwt_token, refresh_jwt_token

from monitor_web import models
from monitor_web.models import Profile, UserGroup
# Create your views here.
from monitor_web.serializers import UserGroupSerializer, ProfileSerializer
from web.common.order import getOrderList
from web.common.paging import MyPageNumberPagination


class UserViewSet(viewsets.ModelViewSet):
    """
    API endpoint that allows users to be viewed or edited.
    """
    queryset = Profile.objects.all()
    serializer_class = ProfileSerializer


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
    """
    返回用户角色数据
    """
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


@permission_classes((IsAuthenticated,))
class UserList(APIView):

    def get(self, request, pk=None, format=None):
        order_list, prop = getOrderList(request)
        # 获取所有数据
        records = models.Profile.objects.all() if prop == '' else models.Profile.objects.order_by(*order_list)
        # 创建分页对象，这里是自定义的MyPageNumberPagination
        pg = MyPageNumberPagination(request.GET.get('size', 7))
        # 获取分页的数据
        page_roles = pg.paginate_queryset(queryset=records, request=request, view=self)
        # 对数据进行序列化
        ser = ProfileSerializer(instance=page_roles, many=True)
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
class UserGroupList(APIView):

    def get(self, request, pk=None, format=None):
        order_list, prop = getOrderList(request)
        # 获取所有数据
        records = models.UserGroup.objects.all() if prop == '' else models.UserGroup.objects.order_by(*order_list)
        # 创建分页对象，这里是自定义的MyPageNumberPagination
        pg = MyPageNumberPagination(request.GET.get('size', 7))
        # 获取分页的数据
        page_roles = pg.paginate_queryset(queryset=records, request=request, view=self)
        # 对数据进行序列化
        ser = UserGroupSerializer(instance=page_roles, many=True)
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

