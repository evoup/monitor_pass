import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
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


@permission_classes((IsAuthenticated,))
class OperationInfo(APIView):
    @method_decorator(permission_required('monitor_web.add_operation', raise_exception=True))
    def post(self, request, *args, **kwargs):
        """
        创建操作
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建操作失败'
        }
        data = JSONParser().parse(request)
        try:
            operation = models.Operation.objects.create(name=data['name'], title=data['subject'],
                                                        content=data['message'])
            models.OperationCondition.objects.create(operation=operation, type=0, operator=0,
                                                     value=0 if not data['triggerId'] else data['triggerId'])
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建操作成功',
            'id': operation.id
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.view_operation', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        读取操作
        """
        operation = models.Operation.objects.get(
            id=self.request.query_params['id']) if self.request.query_params.__contains__('id') else None
        serializer = OperationSerializer(instance=operation, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class OperationItemInfo(APIView):
    @method_decorator(permission_required('monitor_web.add_operation', raise_exception=True))
    def post(self, request, *args, **kwargs):
        """
        创建操作项
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建操作项失败'
        }
        data = JSONParser().parse(request)
        form = data['form']
        # 发送信息
        if data['type'] == '1':
            send_interval = form['send_interval']
            step = form['step']
            user_groups = form['userGroupSelectModel']
            send_types = []
            for send_type in form['checkedSendTypes']:
                if send_type == '邮件':
                    send_types.append(0)
                if send_type == '企业微信':
                    send_types.append(1)
            pass
        pass
