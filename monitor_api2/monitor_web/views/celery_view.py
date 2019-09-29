from django.http import JsonResponse
from rest_framework.views import APIView

from monitor_web import tasks


class CeleryInfo(APIView):
    def get(self, request, pk=None, format=None):
        res = tasks.add.delay(1, 3)
        # 任务逻辑
        return JsonResponse({'status': 'successful', 'task_id': res.task_id})
