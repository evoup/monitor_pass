from django.http import JsonResponse
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import tasks, models


@permission_classes((IsAuthenticated,))
class BatchSendCommand(APIView):
    def post(self, request, pk=None, format=None):
        data = JSONParser().parse(self.request)
        for host in data['hosts']:
            # 查询端口
            server = models.Server.objects.filter(name=host)
            if server.count() > 0:
                # server.ssh_address
                res = tasks.add.delay(host, 22, 'madhouse', 'ls -la /')
        # 任务逻辑
        return JsonResponse({'status': 'successful', 'task_id': res.task_id})
