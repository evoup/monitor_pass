import socket
import struct
import traceback

from django.contrib.auth.decorators import permission_required
from django.http import JsonResponse
from django.utils.decorators import method_decorator
from rest_framework.decorators import permission_classes
from rest_framework.permissions import AllowAny
from rest_framework.permissions import IsAuthenticated
from rest_framework.views import APIView

from monitor_web import models
from monitor_web.serializers import AssetSerializer, AssetRecordSerializer
from web.common import constant
from web.common.paging import paging_request


@permission_classes((IsAuthenticated,))
class AssetInfo(APIView):
    @method_decorator(permission_required('monitor_web.view_asset', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取资产详情
        """
        asset = models.Asset.objects.get(id=self.request.query_params['id'])
        serializer = AssetSerializer(instance=asset, many=False)
        ret = {
            "code": constant.BACKEND_CODE_OK,
            "data": {
                "item": serializer.data
            }
        }
        return JsonResponse(ret, safe=False)


@permission_classes((IsAuthenticated,))
class AssetList(APIView):
    @method_decorator(permission_required('monitor_web.view_asset', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取资产列表
        """
        from monitor_web import models
        page_data, count = paging_request(request, models.Asset, self)
        # 对数据进行序列化
        serializer = AssetSerializer(instance=page_data, many=True)
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


class AssetRecordList(APIView):
    @method_decorator(permission_required('monitor_web.view_asset', raise_exception=True))
    def get(self, request, pk=None, format=None):
        """
        获取资产变更记录列表
        """
        ft = {'asset_obj__in': request.query_params.get('asset_obj')} if 'asset_obj' in request.query_params else None
        from monitor_web import models
        page_data, count = paging_request(request, models.AssetRecord, self, filter=ft)
        # 对数据进行序列化
        serializer = AssetRecordSerializer(instance=page_data, many=True)
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


@permission_classes((AllowAny,))
class AssetAgentInfo(APIView):
    """
    接收来自监控代理提交的资产信息
    """

    def post(self, request, *args, **kwargs):
        ret = {
            "code": constant.BACKEND_CODE_OK
        }
        # TODO 数字签名认证
        main_content = ""
        try:
            records = models.Server.objects.filter(name=request.data['host'])
            if records.count() == 1:
                server = records.get()
                for _key in request.data:
                    if _key == "main_board":
                        # 取出原先的主板对比
                        if not server.os_platform and request.data['base']['os']:
                            main_content += '系统从无变更为%s;' % request.data['base']['os']
                        if not server.os_version and request.data['base']['osv']:
                            main_content += '系统版本从无变更为%s;' % request.data['base']['osv']
                        if not server.sn and request.data['main_board']['sn']:
                            main_content += '主板SN从无变更为%s;' % request.data['main_board']['sn']
                        if not server.manufacturer and request.data['main_board']['manufacturer']:
                            main_content += '主板厂商从无变更为%s;' % request.data['main_board']['manufacturer']
                        if not server.model and request.data['main_board']['model']:
                            main_content += '主板型号从无变更为%s;' % request.data['main_board']['model']
                        models.Server.objects.filter(id=server.id).update(sn=request.data['main_board']['sn'],
                                                                          manufacturer=request.data['main_board'][
                                                                              'manufacturer'],
                                                                          model=request.data['main_board'][
                                                                              'model'],
                                                                          os_platform=request.data['base']['os'],
                                                                          os_version=request.data['base']['osv'])
                    # cpu
                    if _key == "cpu":
                        # 取出原先的cpu对比
                        if not server.cpu_count and request.data['cpu']['cpu_count']:
                            main_content += 'CPU逻辑核数从无变更为%s;' % request.data['cpu']['cpu_count']
                        if not server.cpu_physical_count and request.data['cpu']['cpu_physical']:
                            main_content += 'CPU物理核数从无变更为%s;' % request.data['cpu']['cpu_physical']
                        if not server.cpu_model and request.data['cpu']['cpu_model_name']:
                            main_content += 'CPU型号从无变更为%s;' % request.data['cpu']['cpu_model_name']
                        models.Server.objects.filter(id=server.id).update(cpu_count=request.data['cpu']['cpu_count'],
                                                                          cpu_physical_count=request.data['cpu'][
                                                                              'cpu_physical'],
                                                                          cpu_model=request.data['cpu'][
                                                                              'cpu_model_name'])
                    # 内存
                    if _key == "mem":
                        querySet = models.Memory.objects.filter(server_obj=server)
                        if querySet.count() == 0:
                            for memory in request.data[_key]:
                                # 取出原先的内存对比
                                # "mem": [{
                                #     "capacity": "8192 MB",
                                #     "slot": "DIMM_A",
                                #     "model": "DDR3",
                                #     "speed": "1600 MHz",
                                #     "manufacturer": "Kingston",
                                #     "sn": "1F051A17"
                                # }],
                                if not models.Memory.objects.filter(
                                        manufacturer=memory['manufacturer'], model=memory['model'],
                                        capacity=convert_to_gb(memory['capacity']),
                                        sn=memory['sn'], speed=memory['speed'],
                                        server_obj=server).count():
                                    (object, created) = models.Memory.objects.get_or_create(slot=memory['slot'],
                                                                                            manufacturer=memory[
                                                                                                'manufacturer'],
                                                                                            model=memory['model'],
                                                                                            capacity=convert_to_gb(
                                                                                                memory['capacity']),
                                                                                            sn=memory['sn'],
                                                                                            speed=memory['speed'],
                                                                                            server_obj=server)
                                    mem_content = '[新增内存]插槽为:%s;容量为:%sG;速度为:%s;序列号为:%s;制造商为:%s;型号为:%s' % (
                                        memory['slot'], memory['capacity'], memory['speed'], memory['sn'],
                                        memory['manufacturer'], memory['model'])
                                    models.AssetRecord.objects.create(content=mem_content)
                                # todo 新增也要写asset record
                    if _key == "disk":
                        for disk in request.data[_key]:
                            # todo add sn
                            if not models.Disk.objects.filter(model=disk['model'], capacity=disk['size'],
                                                          pd_type=disk['type'], server_obj=server):
                                (object, created) = models.Disk.objects.get_or_create(model=disk['model'],
                                                                                      capacity=disk['size'],
                                                                                      pd_type=disk['type'],
                                                                                      server_obj=server)
                                disk_content = '[新增硬盘]插槽为xx;容量为%sG;硬盘类型为%s;型号为%s' % (
                                    disk['size'], disk['type'], disk['model'])
                                models.AssetRecord.objects.create(content=disk_content)
                    if _key == "nic":
                        for nic in request.data[_key]:
                            if not models.NIC.objects.filter(name=nic['nic_name'],
                                                         hwaddr="" if nic['mac_addr'] is None else nic['mac_addr'],
                                                         server_obj=server):
                                netmask = "" if nic['netmask'] is None else cidr_to_netmask(nic['netmask'])
                                models.NIC.objects.get_or_create(name=nic['nic_name'],
                                                                 hwaddr="" if nic['mac_addr'] is None else nic[
                                                                     'mac_addr'],
                                                                 netmask=netmask,
                                                                 ipaddrs="" if nic['ip_addr'] is None else nic[
                                                                     'ip_addr'],
                                                                 up=nic['status'] == "UP",
                                                                 server_obj=server)
                                nic_content = '[新增网卡]%s:mac地址为%s;状态为%s;掩码为%s;IP地址为%s' % (
                                    nic['nic_name'], nic['mac_addr'], nic['status'], netmask, nic['ip_addr'])
                                models.AssetRecord.objects.create(content=nic_content)
        except:
            print(traceback.format_exc())
        if main_content:
            models.AssetRecord.objects.create(content=main_content)
        return JsonResponse(ret, safe=False)


def convert_to_gb(size_mb):
    arr = str(size_mb).split("MB")
    return int(str(arr[0]).strip()) / 1024


def cidr_to_netmask(net_bits):
    host_bits = 32 - int(net_bits)
    netmask = socket.inet_ntoa(struct.pack('!I', (1 << 32) - (1 << host_bits)))
    return netmask
