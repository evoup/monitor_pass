import traceback

from django.http import JsonResponse
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import tasks, models
from web.common import constant


@permission_classes((IsAuthenticated,))
class BatchSendCommand(APIView):
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
                    res = tasks.exec_command.delay(server.get().ip, 22, data['username'], data['command'])
                    task_ids.append(res.task_id)
                except:
                    print(traceback.format_exc())
        # 任务逻辑
        return JsonResponse(
            {'code': constant.BACKEND_CODE_CREATED,
             'message': '命令发送中...',
             'task_ids': task_ids}
        )
