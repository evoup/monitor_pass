import traceback

from django.contrib.auth.decorators import permission_required
from django.contrib.auth.models import User, Group
from django.contrib.contenttypes.models import ContentType
from django.db import IntegrityError
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework_jwt.views import obtain_jwt_token, verify_jwt_token, refresh_jwt_token

from monitor_web import models
from monitor_web.models import Profile, Server
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
    def get(self, request, *args, **kwargs):
        l = []
        for g in request.user.groups.all():
            l.append(g.name)
        user = User.objects.get(username=request.user.username)
        username = user.profile.name
        if not username:
            username = user.username
        ret = {
            "code": 20000,
            "data": {
                "roles": [
                    "admin"
                ],
                "name": username,
                "avatar": "user.png",
                "group": ",".join(l)
            }
        }
        return JsonResponse(ret, safe=False)

    """
    创建用户
    """
    @method_decorator(permission_required('auth.add_user', raise_exception=True))
    def post(self, *args, **kwargs):
        from django.db import transaction
        data = JSONParser().parse(self.request)
        ret = {
            'code': 40000,
            'message': '创建用户失败'
        }
        try:
            with transaction.atomic():
                user = User.objects.create_user(data['login_name'], data['email'], data['password'])
                Profile.objects.filter(pk=user.id).update(name=data['name'], desc=data['desc'])
        except IntegrityError:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': 20001,
            'message': '创建用户成功'
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class UserList(APIView):
    @method_decorator(permission_required('auth.view_user', raise_exception=True))
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
class UserGroupInfo(APIView):
    """
    单个用户组
    """
    @method_decorator(permission_required('auth.view_group', raise_exception=True))
    def get(self, *args, **kwargs):
        ret = {
            "code": 20000,
            "data": {
                "name": "usergroup1"
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('auth.add_group', raise_exception=True))
    def post(self, *args, **kwargs):
        data = JSONParser().parse(self.request)
        ret = {
            'code': 40000,
            'message': '创建用户组失败'
        }
        try:
            new_group, created = Group.objects.get_or_create(name=data['name'])
            MODELS = ['Server', 'ServerGroup', '']
            pass
            # Profile.objects.filter(pk=user.id).update(name=data['name'], desc=data['desc'])
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': 20001,
            'message': '创建用户组成功'
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class UserGroupList(APIView):
    @method_decorator(permission_required('auth.view_group', raise_exception=True))
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

