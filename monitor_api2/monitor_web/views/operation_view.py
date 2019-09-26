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
        try:
            # 发送信息
            if data['type'] == '1':
                send_interval = form['send_interval']
                step = form['step']
                user_groups = form['userGroupSelectModel']
                # 传递来2种元素，usergroup|数字和user|数字，其实只要user就可以了
                send_users = []
                for user_group in user_groups:
                    sp_arr = user_group.split('|')
                    if sp_arr[0] == 'user':
                        send_users.append(sp_arr[1])

                send_types = []
                operation = models.Operation.objects.filter(id=data['operationId']).get()
                for send_type in form['checkedSendTypes']:
                    # TODO 这里最好不要写死
                    if send_type == '邮件':
                        send_types.append(0)
                    if send_type == '企业微信':
                        send_types.append(1)
                # 创建operation_step，返回id，基于此id创建operation_message
                start = int(step.split('-')[0])
                end = int(step.split('-')[1])
                operation_step = models.OperationStep.objects.create(
                    operation=operation,
                    start_step=start, end_step=end,
                    inteval=int(send_interval),
                    run_type=int(data['type']))
                operation_message = models.OperationMessage.objects.create(operation_step=operation_step,
                                                                           subject=operation.title,
                                                                           message=operation.content)
                for send_user in send_users:
                    # TODO 方法不好，应该直接查询到user
                    models.RelationOperationMessageUser.objects.get_or_create(operation_message=operation_message,
                                                                              user=models.Profile.objects.get(
                                                                                  user=models.User.objects.get(
                                                                                      id=send_user)))
            elif data['type'] == '2':
                pass
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)

        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建服务器成功'
        }
        return JsonResponse(ret, safe=False)
