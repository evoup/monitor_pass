from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import OperationSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class OperationList(APIView):
    @method_decorator(permission_required('monitor_web.view_operation', raise_exception=True))
    def get(self, request, pk=None, format=None):
        page_data, count = paging_request(request, models.Operation, self)
        # 对数据进行序列化
        serializer = OperationSerializer(instance=page_data, many=True)
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
