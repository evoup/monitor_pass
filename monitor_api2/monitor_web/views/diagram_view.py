from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import DiagramSerializer, DiagramItemSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class DiagramList(APIView):
    @method_decorator(permission_required('monitor_web.view_diagram', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        【模板下的】获取图表列表
        """
        from monitor_web import models
        if self.request.query_params.__contains__('id'):
            page_data, count = paging_request(request, models.Diagram, self,
                                              filter={'id__in': request.query_params['id']})
        else:
            page_data, count = paging_request(request, models.Diagram, self)
        # 对数据进行序列化
        serializer = DiagramSerializer(instance=page_data, many=True)
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
class DiagramInfo(APIView):
    @method_decorator(permission_required('monitor_web.view_diagram', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        【模板下的】获取图表
        """
        diagram = models.Diagram.objects.get(
            id=self.request.query_params['id']) if self.request.query_params.__contains__('id') else None

        di = models.DiagramItem.objects.filter(diagram__in=str(diagram.id)).all()
        serializer = DiagramItemSerializer(instance=di, many=True)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "items": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class ServerDiagramList(APIView):
    @method_decorator(permission_required('monitor_web.view_diagram', raise_exception=True))
    def get(self, request, *args, **kwargs):
        """
        grafana图表
        """
        server_id = self.request.query_params['id']
        server = models.Server.objects.filter(id=server_id).get()
        server_name = server.name.lower()
        dashboards = models.GrafanaDashboard.objects.filter(device_id=server_id, device_type=1).all()
        ret_urls = []
        if dashboards.count() > 0:
            for d in dashboards:
                # 图表id，暂时没用
                did = d.diagram_id
                url = '/grafana/d-solo/%s/dashboard-%s?&panelId=%s&to=' % (d.dashboard_uid, server_name, d.diagram_id)
                ret_urls.append({'did': did, 'url': url})
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "items": ret_urls
            }
        }
        return JsonResponse(ret, safe=False)
