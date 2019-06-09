from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import TemplateSerializer
from web.common import constant
from web.common.order import getOrderList
from web.common.paging import CustomPageNumberPagination, paging_request


@permission_classes((IsAuthenticated,))
class TemplateList(APIView):
    @method_decorator(permission_required('monitor_web.view_template', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        获取全部模板
        """
        page_data, count = paging_request(request, models.Template, self)
        # 对数据进行序列化
        serializer = TemplateSerializer(instance=page_data, many=True)
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


@permission_classes((IsAuthenticated,))
class TemplateInfo(APIView):
    @method_decorator(permission_required('monitor_web.add_template', raise_exception=True))
    def post(self, *args, **kwargs):
        """
        更新模板
        """
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '更新模板成功'
        }
        return JsonResponse(ret, safe=False)
