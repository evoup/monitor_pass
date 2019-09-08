import time
import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
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
        if self.request.query_params.__contains__('templateId'):
            page_data, count = paging_request(request, models.Diagram, self,
                                              filter={'template__in': [request.query_params['templateId']]})
        elif self.request.query_params.__contains__('id'):
            page_data, count = paging_request(request, models.Diagram, self,
                                              filter={'id__in': [request.query_params['id']]})
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

    @method_decorator(permission_required('monitor_web.add_diagram', raise_exception=True))
    def post(self, request, *args, **kwargs):
        """
        【模板下的】创建图表
        """
        data = JSONParser().parse(self.request)
        # 创建图表
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建图表失败'
        }
        try:
            t = models.Template.objects.filter(id=data['template_id'])
            if t.count() > 0:
                d = models.Diagram.objects.create(name=data['name'], width=data['width'], height=data['height'], template=t.get())
            # 创建图表项
            itemIds = data['item_ids'].split(",")
            for itemId in itemIds:
                models.DiagramItem.objects.create(diagram=d, item=models.MonitorItem.objects.filter(id=itemId).get())
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)

        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建图表成功'
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
        ret_iframes = ''
        if dashboards.count() > 0:
            for d in dashboards:
                # 图表id，暂时没用
                did = d.diagram_id
                ret_iframes = ret_iframes + '<iframe src="http://localhost/grafana/d-solo/%s/dashboard-%s?&panelId=%s&to=%s" width="%s" height="%s" frameborder=0 ></iframe>' % (d.dashboard_uid, server_name, d.diagram_id, time.time()*1000, d.diagram.width, d.diagram.height)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": ret_iframes
            }
        }
        return JsonResponse(ret, safe=False)
