import traceback

from django.http import JsonResponse
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser, FileUploadParser, MultiPartParser, FormParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import tasks, models
from web.common import constant


@permission_classes((IsAuthenticated,))
class BatchSendCommand(APIView):
    """
    发送执行命令的任务到celery
    """

    def post(self, request, pk=None, format=None):
        data = JSONParser().parse(self.request)
        task_ids = []
        for host in data['hosts']:
            if len(host['id'].split('server|')) < 2:
                continue
            host_id = host['id'].split('server|')[1]
            # 查询端口
            server = models.Server.objects.filter(id=host_id)
            if server.count() > 0:
                # server.ssh_address
                try:
                    server = server.get()
                    res = tasks.exec_command.delay(server.name, server.ip, 22, data['username'], data['command'])
                    task_ids.append(res.task_id)
                except:
                    print(traceback.format_exc())
        # 任务逻辑
        return JsonResponse(
            {'code': constant.BACKEND_CODE_CREATED,
             'message': '命令发送中...',
             'data': {'items': task_ids}}
        )

    def get(self, request, pk=None, format=None):
        """
        从celery中查询任务的执行结果
        """
        # grab the AsyncResult
        if 'task_id' not in self.request.query_params:
            return JsonResponse({'code': constant.BACKEND_CODE_OK, 'message': '仍在继续...'})
        try:
            result = tasks.exec_command.AsyncResult(self.request.query_params['task_id'])
            res = result.get(timeout=30)
            return JsonResponse({'code': constant.BACKEND_CODE_OK, 'message': '执行完毕', 'data': {'item': res}})
        except:
            print(traceback.format_exc())
            return JsonResponse({'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '遇到问题'})


# 只实现了Postman最后一种binary上传方式
class FileUploadView0(APIView):
    parser_classes = [FileUploadParser]

    # def post(self, request, filename, format='jpg'):
    def post(self, request, filename, format=None):
        up_file = request.FILES['file']
        destination = None
        try:
            destination = open('/tmp/' + up_file.name, 'wb+')
            for chunk in up_file.chunks():
                destination.write(chunk)
            destination.close()
            request.FILES['file'].close()
        except:
            request.FILES['file'].close()
            if destination is not None:
                destination.close()
            return JsonResponse({'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '文件%s上传失败' % up_file.name})
        return JsonResponse({'code': constant.BACKEND_CODE_CREATED, 'message': '文件%s上传成功' % up_file.name})


# MultiPartParser multipart/form-data没有实现
class FileUploadView(APIView):
    parser_classes = [MultiPartParser]

    def post(self, request, filename, format=None):
        if hasattr(request.FILES['file'].file, 'file'):
            new_file = request.FILES['file'].file.name
            # todo new_file是临时文件，需要之后close，接下来要分发
        else:
            up_file = request.FILES['file'].file.getvalue()
            destination = None
            try:
                destination = open('/tmp/' + request.FILES['file'].name, 'wb+')
                destination.write(up_file)
                destination.close()
                request.FILES['file'].close()
            except:
                print(traceback.format_exc())
                request.FILES['file'].close()
                if destination is not None:
                    destination.close()
                return JsonResponse(
                    {'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '文件%s上传失败' % request.FILES['file'].name})
        return JsonResponse({'code': constant.BACKEND_CODE_CREATED, 'message': '文件%s上传成功' % request.FILES['file'].name})
