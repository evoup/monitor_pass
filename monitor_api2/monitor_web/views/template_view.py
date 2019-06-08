from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from web.common import constant


@permission_classes((IsAuthenticated,))
class TemplateList(APIView):
    # @method_decorator(permission_required('monitor_web.view_template', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        获取全部模板
        """
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "count": 1,
                "items": [{"id":2, "name": "x", "triggers": 3}],
                "page": {
                    "currPage": request.GET.get('page', constant.DEFAULT_CURRENT_PAGE),
                    "pageSize": request.GET.get('size', constant.DEFAULT_PAGE_SIZE)
                }
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.view_template', raise_exception=True))
    def post(self, request):
        """
        更新模板
        """
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '更新模板成功'
        }
        return JsonResponse(ret, safe=False)
