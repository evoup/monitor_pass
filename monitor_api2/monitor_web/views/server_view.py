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
from web.settings import GRAFANA_UNIT_MAP

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
            i = None
            if data['idc'] is not None:
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
                    a = Asset.objects.filter(id=server.asset.id)
                    a.update(idc=i)
                    a = a.get()
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
                                                        asset=a, data_collector=d, config_updated=False,
                                                        status=3 if data['monitoring'] else 2)
            match_monitor_items = {}
            srv = Server.objects.get(id=data['id'])
            # 删除全部监控项，这样会删除监控项对应的function
            # 首先要清理老的数据,虽然function和item是级联删除，function和trigger也是，但是event对应的target_id和trigger不是
            # 对于每个item为host的，找出id,进入function，干掉对应的trigger，同时需要干掉对应target_id的event
            # FIXME 这里到底怎么写是不用all还是要all
            functions = models.Function.objects.filter(item__in=models.MonitorItem.objects.filter(host_id=srv.id)).all()
            delete_trigger_id = []
            for func in functions:
                delete_trigger_id.append(func.trigger.id)
            functions = models.Function.objects.filter(
                item__in=models.MonitorItem.objects.filter(host_id=srv.id).all()).all()
            for func in functions:
                delete_trigger_id.append(func.trigger.id)
            models.Event.objects.filter(target_id__in=delete_trigger_id).delete()
            models.MonitorItem.objects.filter(host_id=srv.id).delete()
            models.Trigger.objects.filter(id__in=delete_trigger_id).delete()
            for sg in data['server_groups']:
                # 指向服务器组
                srv.server_groups.add(sg)
                # 服务器组中模板有监控项
                template = models.Template.objects.filter(server_group=sg).get()
                monitor_items = models.MonitorItem.objects.filter(template_id=template.id).all()
                for monitor_item in monitor_items:
                    match_monitor_items[monitor_item.id] = 1
            # FIXME 这里templates不知道是否有传参
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
            diagrams_units = {}
            for d in models.Diagram.objects.all():
                diagrams_tsdb_keys[str(d.id)] = []
                diagrams_names[str(d.id)] = []
                diagrams_units[str(d.id)] = []
            if diagram_items.count() > 0:
                for diagram_item in diagram_items:
                    diagrams_names[str(diagram_item.diagram.id)] = diagram_item.diagram.name
                    diagrams_tsdb_keys[str(diagram_item.diagram.id)].append(diagram_item.item.key)
                    diagrams_units[str(diagram_item.diagram.id)] = diagram_item.item.unit
            panes = []
            grafana_url = 'http://localhost/grafana/api/dashboards/db'
            grafana_delete_url = 'http://localhost/grafana/api/dashboards/uid/%s'
            for diagram_id in diagrams_names.keys():
                # 先删除以前的图表
                old = models.GrafanaDashboard.objects.filter(device_id=srv.id, device_type=1,
                                                             diagram=models.Diagram.objects.filter(
                                                                 id=diagram_id).get())
                gconf = models.GeneralConfig.objects.all()[0]
                headers = {'Authorization': gconf.grafana_api_key, 'Accept': 'application/json',
                           'Content-Type': 'application/json'}
                if old.count() > 0:
                    requests.delete(grafana_delete_url % old.get().dashboard_uid, headers=headers)
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
                if not diagrams_names[diagram_id]:
                    continue
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
                			"format": "%s",
                			"label": null,
                			"logBase": 1,
                			"max": null,
                			"min": null,
                			"show": true
                		},
                		{
                			"format": "%s",
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
                                    """ % (
                    diagram_id, targets, diagrams_names[diagram_id], GRAFANA_UNIT_MAP[diagrams_units[diagram_id]],
                    GRAFANA_UNIT_MAP[diagrams_units[diagram_id]])
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
            r = requests.post(grafana_url, data=dashboard.encode(), headers=headers)
            if r.status_code == 502:
                ret['message'] = "grafana异常信息：502错误的网关"
                return JsonResponse(ret, safe=False)
            elif r.status_code != 200:
                ret['message'] = "grafana异常信息：" + r.json()['message']
                return JsonResponse(ret, safe=False)
            else:
                for diagram_id in diagrams_names.keys():
                    if not diagrams_names[diagram_id]:
                        continue
                    uid = r.json()['uid']
                    if (models.GrafanaDashboard.objects.filter(device_id=srv.id, device_type=1,
                                                               diagram=models.Diagram.objects.filter(
                                                                   id=diagram_id).get()).all().count() > 0):
                        models.GrafanaDashboard.objects.filter(device_id=srv.id, device_type=1,
                                                               diagram=models.Diagram.objects.filter(
                                                                   id=diagram_id).get()).update(dashboard_uid=uid)
                    else:
                        models.GrafanaDashboard.objects.create(dashboard_uid=uid, device_id=srv.id, device_type=1,
                                                               diagram=models.Diagram.objects.filter(
                                                                   id=diagram_id).get())
            # [从模板创建出的逻辑]
            #     一上来，先利用model.CASCAND把指定服务器的item对应的function自动删除删除了。
            #     item表中如果host_id是0，则为系统默认的item，它的templateId是严格对应到template表的，item_copy_from是0
            # hostid>0的，都是外建到server的新建出来的服务器的item，它的templateId严格对应到template表的，并且item_copy_from对应到复制源的itemId
            #     item复制完成后，遍历trigger的trigger_copy_from为0的，其中的expression的{}内的function需要重新生成，但是生成后会出现新的trigger，
            # 并且老的有的trigger的function id已经找不到了，这需要之后用计划任务删掉。
            #     diagram同样的，需要复制出来。
            #    最后触发的时候，根据trigger倒推出function,function找出item，item有他对应的host，就能对应到一台服务器了。
            pattern = re.compile(r'{([^}]*)}', re.S)
            for sg in data['server_groups']:
                srv.server_groups.add(sg)
                template = models.Template.objects.filter(server_group=sg).get()
                # item
                monitor_items = models.MonitorItem.objects.filter(template_id=template.id).all()
                for monitor_item in monitor_items:
                    if monitor_item.host_id == 0:
                        monitor_item.host_id = srv.id
                        monitor_item.item_copy_from = monitor_item.pk
                        monitor_item.pk = None
                        try:
                            # TODO 这边的速度要优化成bulk insert
                            monitor_item.save()
                        except:
                            pass
                # trigger, trigger_copy_from=0的是系统默认的
                triggers = models.Trigger.objects.filter(template_id=template.id, trigger_copy_from=0).all()
                for trigger in triggers:
                    # {5}>300 找出{}中的function的id，对其中的记录进行复制
                    trigger.trigger_copy_from = trigger.pk
                    # 这样就是复制新的
                    trigger.pk = None
                    trigger.save()
                    new_expression = re.sub(pattern,
                                            expression_replace_callback({'host_id': srv.id, 'trigger_id': trigger.id}),
                                            trigger.expression)
                    models.Trigger.objects.filter(id=trigger.id).update(expression=new_expression)
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
        ft = {'server_groups__in': request.query_params.get('serverGroup')} if request.query_params.get(
            'serverGroup') is not '0' else None
        page_data, count = paging_request(request, models.Server, self, filter=ft)
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
        models.ServerGroup.objects.filter(id=self.request.query_params['id']).delete()
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
        try:
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
        except:
            return JsonResponse(ret, safe=False)
        ret = {
            'code': constant.BACKEND_CODE_DELETED,
            'message': '创建服务组成功'
        }
        return JsonResponse(ret, safe=False)


class expression_replace_callback(object):
    """
    re.sub的回调函数，替换调原本表达式中对用的function id
    """

    # 初始化属性
    def __init__(self, extra_arg):
        self.extra_arg = extra_arg

    # 使类的实例变得callable
    def __call__(self, match_obj):
        functionId = int(match_obj.group(1))
        f = models.Function.objects.filter(id=functionId)
        if f.count() == 0:
            return ""
        else:
            function = f.get()
            # 寻找已经复制出来的itemCopyFrom的item.name和parameter保存原来的
            new_item = models.MonitorItem.objects.filter(host_id=self.extra_arg['host_id'],
                                                         item_copy_from=function.item.id).get()
            # 确定trigger
            new_trigger = models.Trigger.objects.filter(id=self.extra_arg['trigger_id']).get()
            function.item = new_item
            function.trigger = new_trigger
            function.pk = None
            function.save()
            return '{%s}' % function.id
