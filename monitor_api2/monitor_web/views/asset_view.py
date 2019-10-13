import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import AssetSerializer, AssetRecordSerializer
from web.common import constant
from web.common.paging import paging_request
from rest_framework.permissions import AllowAny


@permission_classes((IsAuthenticated,))
class AssetList(APIView):
    @method_decorator(permission_required('monitor_web.view_asset', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取资产列表
        """
        from monitor_web import models
        page_data, count = paging_request(request, models.Asset, self)
        # 对数据进行序列化
        serializer = AssetSerializer(instance=page_data, many=True)
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


class AssetRecordList(APIView):
    @method_decorator(permission_required('monitor_web.view_asset', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取资产变更记录列表
        """
        from monitor_web import models
        page_data, count = paging_request(request, models.AssetRecord, self)
        # 对数据进行序列化
        serializer = AssetRecordSerializer(instance=page_data, many=True)
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


@permission_classes((AllowAny,))
class AssetAgentInfo(APIView):
    """
    接收来自监控代理提交的资产信息
    """

    def post(self, request, *args, **kwargs):
        ret = {
            "code": constant.BACKEND_CODE_OK
        }
        # TODO 数字签名认证
        try:
            records = models.Server.objects.filter(name=request.data['host'])
            if records.count() == 1:
                server = records.get()
                for _key in request.data:
                    if _key == "cpu":
                        models.Server.objects.filter(id=server.id).update(cpu_count=request.data['cpu']['cpu_count'],
                                                                          cpu_physical_count=request.data['cpu']['cpu_physical'], cpu_model=request.data['cpu']['cpu_model_name'])
                        pass
                    if _key == "mem":
                        pass
                    if _key == "main_board":
                        pass
                    if _key == "disk":
                        pass
                    if _key == "nic":
                        pass
        except:
            print(traceback.format_exc())
        return JsonResponse(ret, safe=False)
