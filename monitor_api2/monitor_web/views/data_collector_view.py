import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import IDC, DataCollector
from monitor_web.serializers import IDCSerializer, DataCollectorSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class DataCollectorInfo(APIView):
    @method_decorator(permission_required('monitor_web.add_datacollector', raise_exception=True))
    def post(self, *args, **kwargs):
        """
        创建机房
        """
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建数据收集器失败'
        }
        try:
            DataCollector.objects.create(name=data['name'], ip=data['ip'], port=data['port'])
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建数据收集器成功'
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.delete_datacollector', raise_exception=True))
    def delete(self, *args, **kwargs):
        """
        删除数据收集器
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '删除数据收集器失败'
        }
        try:
            idc = models.DataCollector.objects.get(id=self.request.query_params['id'])
            idc.delete()
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '删除数据收集器成功'
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class DataCollectorList(APIView):
    @method_decorator(permission_required('monitor_web.view_datacollector', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取数据收集器列表
        """
        page_data, count = paging_request(request, models.DataCollector, self)
        # 对数据进行序列化
        serializer = DataCollectorSerializer(instance=page_data, many=True)
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
