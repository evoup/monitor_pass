import json
import logging
import re
import traceback

import requests
from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from django.views.decorators.csrf import csrf_exempt
from rest_framework.decorators import permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.models import Server, Asset, DataCollector, IDC
from monitor_web.serializers import ServerSerializer, ServerGroupSerializer
from web.common import constant
from web.common.paging import paging_request

logger = logging.getLogger(__name__)


@permission_classes((IsAuthenticated,))
class ServerInfo(APIView):

    def get(self, request, *args, **kwargs):
        """
        获取单台服务器
        """
        server = models.Server.objects.get(
            id=self.request.query_params['id']) if self.request.query_params.__contains__('id') else None
        serializer = ServerSerializer(instance=server, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.add_server', raise_exception=True))
    def post(self, *args, **kwargs):
        """
        创建单台服务器
        """
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建服务器失败'
        }
        try:
            i, _ = IDC.objects.get_or_create(name=data['idc'])
            a = Asset.objects.create(device_type_id=1, device_status_id=1, idc=i, host_name=data['name'])
            if not data['data_collector']:
                ret['message'] = ret['message'] + ":需要先创建数据收集器"
                return JsonResponse(ret, safe=False)
            if not data['name']:
                ret['message'] = ret['message'] + ":服务器名字不能为空"
                return JsonResponse(ret, safe=False)
            d = DataCollector.objects.get(id=data['data_collector'])
            server, created = Server.objects.get_or_create(name=data['name'], agent_address=data['agent_addr'],
                                                           ssh_address=data['ssh_addr'],
                                                           jmx_address=data['jmx_addr'],
                                                           snmp_address=data['snmp_addr'], asset=a,
                                                           data_collector=d, status=2 if data['monitoring'] else 3)
            srv = Server.objects.get(id=server.id)
            for sg in data['server_groups']:
                srv.server_groups.add(sg)
            for sg in data['templates']:
                srv.templates.add(sg)
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '创建服务器成功'
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.change_server', raise_exception=True))
    def put(self, *args, **kwargs):
        """
        修改单台服务器
        """
        data = JSONParser().parse(self.request)
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '修改服务器失败'
        }
        try:
            server = Server.objects.get(id=data['id'])
            a = None
            if data['idc'] is not None:
                i, _ = IDC.objects.get_or_create(name=data['idc'])
                if server.asset is not None:
                    a = Asset.objects.filter(id=server.asset.id).update(idc=i)
                else:
                    a = Asset.objects.create(device_type_id=1, device_status_id=1, idc=i, host_name=data['name'])
            if not data['data_collector']:
                ret['message'] = ret['message'] + ":需要先创建数据收集器"
                return JsonResponse(ret, safe=False)
            if not data['name']:
                ret['message'] = ret['message'] + ":服务器名字不能为空"
                return JsonResponse(ret, safe=False)
            d = DataCollector.objects.get(id=data['data_collector'])
            Server.objects.filter(id=data['id']).update(name=data['name'], agent_address=data['agent_addr'],
                                                        ssh_address=data['ssh_addr'],
                                                        jmx_address=data['jmx_addr'],
                                                        snmp_address=data['snmp_addr'],
                                                        asset=a, data_collector=d,
                                                        status=3 if data['monitoring'] else 2)
            match_monitor_items = {}
            srv = Server.objects.get(id=data['id'])
            for sg in data['server_groups']:
                # 指向服务器组
                srv.server_groups.add(sg)
                # 服务器组中模板有监控项
                template = models.Template.objects.filter(server_group=sg).get()
                monitor_items = models.MonitorItem.objects.filter(template_id=template.id).all()
                for monitor_item in monitor_items:
                    match_monitor_items[monitor_item.id] = 1
            for tp in data['templates']:
                # 指向模板
                srv.templates.add(tp)
                # 服务器组中模板有监控项
                template = models.Template.objects.filter(server_group=tp).get()
                monitor_items = models.MonitorItem.objects.filter(template_id=template.id).all()
                for monitor_item in monitor_items:
                    match_monitor_items[monitor_item.id] = 1
            diagram_items = models.DiagramItem.objects.filter(item__in=match_monitor_items.keys()).all()
            # 将所有要生成的图表准备好数据,一个图表就是grafana的一个pane
            diagrams_tsdb_keys = {}
            diagrams_names = {}
            for d in models.Diagram.objects.all():
                diagrams_tsdb_keys[str(d.id)] = []
                diagrams_names[str(d.id)] = []
            if diagram_items.count() > 0:
                for diagram_item in diagram_items:
                    diagrams_names[str(diagram_item.diagram.id)] = diagram_item.diagram.name
                    diagrams_tsdb_keys[str(diagram_item.diagram.id)].append(diagram_item.item.key)
            panes = []
            for diagram_id in diagrams_names.keys():
                targets = []
                for tsdb_key in diagrams_tsdb_keys[str(diagram_id)]:
                    target = """
                    {
                		"aggregator": "sum",
                		"disableDownsampling": false,
                		"downsampleAggregator": "avg",
                		"downsampleFillPolicy": "none",
                		"hide": false,
                		"metric": "apps.backend.%s.%s",
                		"refId": "A"
                	}
                	""" % (srv.name, tsdb_key)
                    targets.append(target)
                targets = ','.join(targets)
                pane = """
                                    {
                	"aliasColors": {},
                	"bars": false,
                	"cacheTimeout": null,
                	"dashLength": 10,
                	"dashes": false,
                	"fill": 1,
                	"fillGradient": 10,
                	"gridPos": {
                		"h": 9,
                		"w": 12,
                		"x": 0,
                		"y": 0
                	},
                	"id": %s,
                	"legend": {
                		"avg": true,
                		"current": false,
                		"max": true,
                		"min": true,
                		"show": true,
                		"total": false,
                		"values": true
                	},
                	"lines": true,
                	"linewidth": 1,
                	"links": [],
                	"nullPointMode": "null",
                	"options": {
                		"dataLinks": []
                	},
                	"percentage": false,
                	"pluginVersion": "6.3.3",
                	"pointradius": 2,
                	"points": false,
                	"renderer": "flot",
                	"seriesOverrides": [],
                	"spaceLength": 10,
                	"stack": true,
                	"steppedLine": false,
                	"targets": [%s],
                	"thresholds": [],
                	"timeFrom": null,
                	"timeRegions": [],
                	"timeShift": null,
                	"title": "%s",
                	"tooltip": {
                		"shared": true,
                		"sort": 0,
                		"value_type": "individual"
                	},
                	"transparent": true,
                	"type": "graph",
                	"xaxis": {
                		"buckets": null,
                		"mode": "time",
                		"name": null,
                		"show": true,
                		"values": []
                	},
                	"yaxes": [{
                			"format": "short",
                			"label": null,
                			"logBase": 1,
                			"max": null,
                			"min": null,
                			"show": true
                		},
                		{
                			"format": "short",
                			"label": null,
                			"logBase": 1,
                			"max": null,
                			"min": null,
                			"show": true
                		}
                	],
                	"yaxis": {
                		"align": false,
                		"alignLevel": null
                	}
                }
                                    """ % (diagram_id, targets, diagrams_names[diagram_id])
                panes.append(pane)
            dashboard = """
                {
                	"dashboard": {
                		"id": null,
                		"uid": null,
                		"title": "Dashboard %s",
                		"overwrite": true,
                		"panels": [%s]
                	}
                }
                """ % (srv.name, ','.join(panes))
            dashboard = re.sub('\s+', ' ', dashboard)
            grafana_url = 'http://localhost/grafana/api/dashboards/db'
            gconf = models.GeneralConfig.objects.all()[0]
            headers = {'Authorization': gconf.grafana_api_key, 'Accept': 'application/json',
                       'Content-Type': 'application/json'}
            r = requests.post(grafana_url, data=dashboard, headers=headers)
            if r.status_code == 502:
                ret['message'] = "grafana异常信息：502错误的网关"
                return JsonResponse(ret, safe=False)
            elif r.status_code != 200:
                ret['message'] = "grafana异常信息：" + r.json()['message']
                return JsonResponse(ret, safe=False)
            else:
                for diagram_id in diagrams_names.keys():
                    uid = r.json()['uid']
                    try:
                        models.GrafanaDashboard.objects.update_or_create(dashboard_uid=uid, device_id=srv.id,
                                                                         device_type=1,
                                                                         diagram=models.Diagram.objects.filter(
                                                                             id=diagram_id).get())
                    except:
                        # 忽略唯一索引错误
                        pass
        except:
            print(traceback.format_exc())
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_CREATED,
            'message': '修改服务器成功'
        }
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.delete_server', raise_exception=True))
    def delete(self, request, *args, **kwargs):
        """
        删除单台服务器
        """
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '删除服务器成功'
        }
        server = models.Server.objects.get(id=self.request.query_params['id'])
        server.delete()
        return JsonResponse(ret, safe=False)

    @csrf_exempt
    def dispatch(self, *args, **kwargs):
        return super(ServerInfo, self).dispatch(*args, **kwargs)


@permission_classes((IsAuthenticated,))
class ServerList(APIView):
    @method_decorator(permission_required('monitor_web.view_server', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取服务器列表
        """
        # 0是服务器组
        filter = {'server_groups__in': request.query_params.get('serverGroup')} if request.query_params.get(
            'serverGroup') is not '0' else None
        page_data, count = paging_request(request, models.Server, self, filter=filter)
        # 对数据进行序列化
        serializer = ServerSerializer(instance=page_data, many=True)
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
class ServerGroupList(APIView):
    @method_decorator(permission_required('monitor_web.view_servergroup', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取服务器组列表
        """
        page_data, count = paging_request(request, models.ServerGroup, self)
        serializer = ServerGroupSerializer(instance=page_data, many=True)
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
class ServerGroupInfo(APIView):
    @method_decorator(permission_required('monitor_web.delete_servergroup', raise_exception=True))
    def delete(self, *args, **kwargs):
        """
        删除服务器组
        """
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '删除服务器组成功'
        }
        serverGroup = models.ServerGroup.objects.get(id=self.request.query_params['id'])
        serverGroup.delete()
        return JsonResponse(ret, safe=False)

    @method_decorator(permission_required('monitor_web.add_servergroup', raise_exception=True))
    def post(self, request, *args, **kwargs):
        """
        创建服务器组
        """
        ret = {
            'code': constant.BACKEND_CODE_OPT_FAIL,
            'message': '创建服务器组失败'
        }
        data = JSONParser().parse(request)
        new_group, created = models.ServerGroup.objects.get_or_create(name=data['name'], desc=data['desc'],
                                                                      alarm_type=data['alarm_type'])
        new_group_id = new_group.id
        # 维护服务器组用户组关系
        user_groups = models.UserGroup.objects.filter(id__in=data['user_groups']).all()
        for user_group in user_groups:
            user_group.server_group.add(new_group_id)
        # 维护服务器组模板关系
        templates = models.Template.objects.filter(id__in=data['templates']).all()
        for template in templates:
            template.server_group.add(new_group_id)
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '创建服务组成功'
        }
        return JsonResponse(ret, safe=False)
