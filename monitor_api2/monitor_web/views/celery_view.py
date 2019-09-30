import celery
from django.http import JsonResponse
from rest_framework.views import APIView

from monitor_web import tasks


class CeleryInfo(APIView):
    def get(self, request, pk=None, format=None):
        res = tasks.add.delay('172.16.25.155', 22, 'madhouse', 'ls -la /')
        # 任务逻辑
        return JsonResponse({'status': 'successful', 'task_id': res.task_id})


class CeleryTaskInfo(APIView):
    def get(self, request, pk=None, format=None):
        # grab the AsyncResult
        if 'task_id' not in self.request.query_params:
            return JsonResponse({'status': 40000})
        result = None
        try:
            result = tasks.add.AsyncResult(self.request.query_params['task_id'])
            x = result.get(timeout=10)
            return JsonResponse(x)
        except:
            return JsonResponse({'status': 40001})
