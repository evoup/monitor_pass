import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import IDC
from monitor_web.serializers import IDCSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class IdcInfo(APIView):
    @method_decorator(permission_required('monitor_web.add_idc', raise_exception=True))
    def post(self, *args, **kwargs):
        """
        创建机房
        """
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建机房失败'
        }
        try:
            IDC.objects.create(name=data['name'], floor=data['floor'] if data['floor'] else None,
                               desc=data['desc'] if data['desc'] else None)
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建机房成功'
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.delete_idc', raise_exception=True))
    def delete(self, *args, **kwargs):
        """
        删除机房
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '删除机房失败'
        }
        try:
            idc = models.IDC.objects.get(id=self.request.query_params['id'])
            idc.delete()
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '删除机房成功'
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class IdcList(APIView):
    @method_decorator(permission_required('monitor_web.view_idc', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取机房列表
        """
        page_data, count = paging_request(request, models.IDC, self)
        # 对数据进行序列化
        serializer = IDCSerializer(instance=page_data, many=True)
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
