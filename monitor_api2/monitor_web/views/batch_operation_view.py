import json
import os
import tempfile
import traceback
from shutil import copyfile

from django.core.cache import cache
from django.http import JsonResponse
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser, MultiPartParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import tasks, models
from web.common import constant
from web.common.constant import TEMPFILE_SUFFIX


def dispatch(request, src_file, dest_file):
    task_ids = []
    for host in json.loads(request.POST['hosts']):
        if len(host['id'].split('server|')) < 2:
            continue
        host_id = host['id'].split('server|')[1]
        # 查询端口
        server = models.Server.objects.filter(id=host_id)
        if server.count() > 0:
            # server.ssh_address
            try:
                server = server.get()
                res = tasks.file_dispatch.s(server.name, server.ip, 22, request.POST['username'], src_file,
                                            dest_file).delay()
                task_ids.append(res.task_id)
            except:
                print(traceback.format_exc())
    return task_ids


@permission_classes((IsAuthenticated,))
class CeleryTaskInfoView(APIView):
    def get(self, request, pk=None, format=None):
        """
        从celery中查询任务的执行结果
        """
        if 'task_id' not in self.request.query_params:
            return JsonResponse({'code': constant.BACKEND_CODE_OK, 'message': '仍在继续...'})
        try:
            res = cache.get("task_id:%s" % self.request.query_params['task_id'])
            # 如果celery backend使用rpc（rabbitmq/amqp），则不能使用reuslt.get()，消息是阅后即焚的，参见https://docs.celeryproject.org/en/latest/userguide/tasks.html
            # 由于文档上提到不同的进程不能获得相同的结果，所以用redis事先保存，这里来获取
            return JsonResponse({'code': constant.BACKEND_CODE_OK, 'message': '执行完毕', 'data': {'item': res}})
        except:
            print(traceback.format_exc())
            return JsonResponse({'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '遇到问题'})


@permission_classes((IsAuthenticated,))
class BatchSendCommandView(APIView):
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
                    res = tasks.exec_command.apply_async(
                        (server.name, server.ip, 22, data['username'], data['command']), expires=60)
                    task_ids.append(res.task_id)
                except:
                    print(traceback.format_exc())
        # 任务逻辑
        return JsonResponse(
            {'code': constant.BACKEND_CODE_CREATED,
             'message': '命令发送中...',
             'data': {'items': task_ids}}
        )


# MultiPartParser multipart/form-data的方式
class FileUploadView(APIView):
    parser_classes = [MultiPartParser]

    def post(self, request, filename, format=None):
        task_ids = []
        # 如果较大的文件，根据测试，应该大于2M
        if hasattr(request.FILES['file'].file, 'file'):
            real_file_name = request.FILES['file'].name
            # python自动将上传的文件放到/tmp目录下，文件名new_file是随机生成的
            tmp_file_name = request.FILES['file'].file.name
            copied_tmp_file_name = "%s%s" % (tmp_file_name, TEMPFILE_SUFFIX)
            copyfile(tmp_file_name, copied_tmp_file_name)
            # 临时文件会被python回收，需要复制一份
            dispatched_tasks = dispatch(request, copied_tmp_file_name,
                                        os.path.join(request.POST['send_dir'], real_file_name))
            for dispatched_task in dispatched_tasks:
                task_ids.append(dispatched_task)
        # 如果小文件
        else:
            real_file_name = request.FILES['file'].name
            up_file = request.FILES['file'].file.getvalue()
            try:
                # 不能马上删除，任务完成后，交给后续的任务去清理
                fp = tempfile.NamedTemporaryFile(delete=False, suffix=TEMPFILE_SUFFIX)
                fp.write(up_file)
                # close后会删除临时文件，up_file还存在,'/tmp/tmpzmvv_r4x'
                tmp_file_name = fp.name
                dispatched_tasks = dispatch(request, tmp_file_name,
                                            os.path.join(request.POST['send_dir'], real_file_name))
                for dispatched_task in dispatched_tasks:
                    task_ids.append(dispatched_task)
                # todo 需要写一个清理任务，定期清楚这些临时文件
                fp.close()
                request.FILES['file'].close()
            except:
                print(traceback.format_exc())
                request.FILES['file'].close()
                return JsonResponse(
                    {'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '文件%s上传失败' % request.FILES['file'].name})
        return JsonResponse({'code': constant.BACKEND_CODE_CREATED, 'message': '文件%s上传成功' % request.FILES['file'].name,
                             'data': {'items': task_ids}})

# 实现了Postman最后一种binary上传方式，很遗憾el-upload不支持，postman的binary是可以的
# class FileUploadView0(APIView):
#     parser_classes = [FileUploadParser]
#
#     # def post(self, request, filename, format='jpg'):
#     def post(self, request, filename, format=None):
#         up_file = request.FILES['file']
#         destination = None
#         try:
#             destination = open('/tmp/' + up_file.name, 'wb+')
#             for chunk in up_file.chunks():
#                 destination.write(chunk)
#             destination.close()
#             request.FILES['file'].close()
#         except:
#             request.FILES['file'].close()
#             if destination is not None:
#                 destination.close()
#             return JsonResponse({'code': constant.BACKEND_CODE_OPT_FAIL, 'message': '文件%s上传失败' % up_file.name})
#         return JsonResponse({'code': constant.BACKEND_CODE_CREATED, 'message': '文件%s上传成功' % up_file.name})
