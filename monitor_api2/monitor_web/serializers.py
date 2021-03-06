import re

from django.contrib.auth.models import Group, User
from rest_framework import serializers

from monitor_web import models
from monitor_web.models import Server, Profile, IDC, Asset, Tag, ServerGroup, UserGroup, Template, MonitorItem, Trigger, \
    Function, DataCollector, AssetRecord, Diagram, DiagramItem, GeneralConfig, NotificationMode, Event, Operation


class DashboardServersSerializer(serializers.ModelSerializer):
    class Meta:
        model = Server
        fields = ('name', 'status')


class DataCollectorSerializer(serializers.ModelSerializer):
    class Meta:
        model = DataCollector
        fields = ('id', 'name', 'ip', 'port')

    def create(self, attrs, instance=None):
        assert instance is None, 'Cannot create data_collector with DataCollectorSerializer'
        (object, created) = DataCollector.objects \
            .get_or_create(name=attrs.get('name'), ip=attrs.get('ip'), port=attrs.get('port'))
        return object

    def update(self, attrs, instance=None):
        assert instance is None, 'Cannot update data collector with DataCollectorSerializer'
        a = {'name': attrs.get('name')}
        if attrs.get('ip'):
            a['ip'] = attrs.get('ip')
        if attrs.get('port'):
            a['port'] = attrs.get('port')
        (object, created) = DataCollector.objects.get_or_create(a)
        return object


class IDCSerializer(serializers.ModelSerializer):
    class Meta:
        model = IDC
        fields = ('id', 'name', 'floor', 'desc')

    def create(self, attrs, instance=None):
        assert instance is None, 'Cannot create idc with IDCSerializer'
        (idc_object, created) = IDC.objects \
            .get_or_create(name=attrs.get('name'), floor=attrs.get('floor') if attrs.get('floor') else None)
        return idc_object

    def update(self, attrs, instance=None):
        assert instance is None, 'Cannot update idc with IDCSerializer'
        a = {'name': attrs.get('name')}
        if attrs.get('floor'):
            a['floor'] = attrs.get('floor')
        (idc_object, created) = IDC.objects.get_or_create(a)

        return idc_object


class TagSerializer(serializers.ModelSerializer):
    class Meta:
        model = Tag
        fields = '__all__'


class AssetSerializer(serializers.ModelSerializer):
    server = serializers.SerializerMethodField()
    tag = TagSerializer(required=True)
    idc = IDCSerializer(required=True)

    class Meta:
        model = Asset
        fields = '__all__'

    @staticmethod
    def get_server(obj):
        s = models.Server.objects.filter(asset=obj)
        if s.count() > 0:
            server = s.get()
            nics = []
            nic = models.NIC.objects.filter(server_obj=server)
            if nic.count() > 0:
                for n in nic:
                    nics.append({'name': n.name, 'hwaddr': n.hwaddr, 'ip': n.ipaddrs, 'netmask': n.netmask, 'up': n.up})
            disks = []
            ds = models.Disk.objects.filter(server_obj=server)
            if ds.count() > 0:
                for d in ds:
                    disks.append({'slot': d.slot, 'model': d.model, 'size': d.capacity, 'pd_type': d.pd_type})
            mems = []
            mem = models.Memory.objects.filter(server_obj=server)
            if mem.count() > 0:
                for m in mem:
                    mems.append({'slot': m.slot, 'size': m.capacity, 'model': m.model, 'serial_number': m.sn,
                                 'manufacturer': m.manufacturer, 'speed': m.speed})

            return {'name': server.name, 'cpu_count': server.cpu_count, 'cpu_model': server.cpu_model,
                    'cpu_physical_count': server.cpu_physical_count, 'serial_number': server.sn,
                    'os_platform': server.os_platform, 'os_version': server.os_version, 'model': server.model,
                    'manufacturer': server.manufacturer, 'nics': nics, 'disks': disks, 'memories': mems}
        else:
            return None


class AssetRecordSerializer(serializers.ModelSerializer):
    type = serializers.SerializerMethodField()
    name = serializers.SerializerMethodField()
    create_at = serializers.DateTimeField(format="%Y-%m-%d %H:%M:%S")

    class Meta:
        model = AssetRecord
        fields = '__all__'

    def get_type(self, obj):
        a = models.Asset.objects.filter(id=obj.id).all()
        if len(a):
            return a[0].device_type_id
        else:
            return 0

    def get_name(self, obj):
        a = obj.asset_obj
        if a:
            # 服务器
            if a.device_type_id == 1:
                return a.server.name
            # 防火墙
            elif a.device_type_id == 2:
                return a.sn
            # 交换机
            elif a.device_type_id == 3:
                return a.sn
        else:
            return None


class ServerSerializer(serializers.ModelSerializer):
    asset = AssetSerializer(required=True)
    data_collector_name = serializers.SerializerMethodField()
    last_online = serializers.DateTimeField(format="%Y-%m-%d %H:%M:%S")

    class Meta:
        model = Server
        fields = '__all__'

    def get_data_collector_name(self, obj):
        return obj.data_collector.name


class ServerGroupSerializer(serializers.ModelSerializer):
    down = serializers.SerializerMethodField()
    on = serializers.SerializerMethodField()
    unmonitoring = serializers.SerializerMethodField()
    unknown = serializers.SerializerMethodField()

    class Meta:
        model = ServerGroup
        fields = '__all__'

    def get_down(self, obj):
        return models.Server.objects.filter(status=0, server_groups__in=str(obj.id)).all().count()

    def get_on(self, obj):
        return models.Server.objects.filter(status=1, server_groups__in=str(obj.id)).all().count()

    def get_unmonitoring(self, obj):
        return models.Server.objects.filter(status=2, server_groups__in=str(obj.id)).all().count()

    def get_unknown(self, obj):
        return models.Server.objects.filter(status=3, server_groups__in=str(obj.id)).all().count()


class ItemSerializer(serializers.ModelSerializer):
    status = serializers.SerializerMethodField()
    triggers = serializers.SerializerMethodField()

    class Meta:
        model = MonitorItem
        fields = '__all__'

    def get_status(self, obj):
        # 查询用户监控项
        uid = self.context.get("user_id")
        record = models.RelationUserItem.objects.filter(template_id=obj.template_id, item_id=obj.id, user_id=uid).all()
        if len(record) > 0:
            return "1" if record[0].status else "0"
        else:
            return "1"

    def get_triggers(self, obj):
        # 返回其下触发器数
        # 再找function表中对应item的trigger
        record = models.Function.objects.filter(item=obj.id).all()
        return len(record)


class TemplateSerializer(serializers.ModelSerializer):
    items = serializers.SerializerMethodField()
    triggers = serializers.SerializerMethodField()
    diagrams = serializers.SerializerMethodField()

    class Meta:
        model = Template
        fields = '__all__'

    def get_items(self, obj):
        return models.MonitorItem.objects.filter(template_id=obj.id, host_id=0).count()

    def get_triggers(self, obj):
        return models.Trigger.objects.filter(template_id=obj.id, trigger_copy_from=0).count()

    def get_diagrams(self, obj):
        return models.Diagram.objects.filter(template_id=obj.id).count()


class TriggerSerializer(serializers.ModelSerializer):
    class Meta:
        model = Trigger
        fields = '__all__'


class DiagramSerializer(serializers.ModelSerializer):
    class Meta:
        model = Diagram
        fields = '__all__'


class DiagramItemSerializer(serializers.ModelSerializer):
    item = serializers.SerializerMethodField()

    class Meta:
        model = DiagramItem
        fields = '__all__'

    def get_item(self, obj):
        return obj.item.name


class TriggerFunctionSerializer(serializers.ModelSerializer):
    trigger_name = serializers.SerializerMethodField()
    expression = serializers.SerializerMethodField()
    level = serializers.SerializerMethodField()
    status = serializers.SerializerMethodField()
    desc = serializers.SerializerMethodField()

    class Meta:
        model = Function
        fields = '__all__'

    def get_trigger_name(self, obj):
        # obj.name是avg,通过function表查trigger表
        record = models.Trigger.objects.filter(id=obj.trigger.id).all()
        if len(record) > 0:
            trigger = record[0]
            return trigger.name
        return None

    def get_expression(self, obj):
        record = models.Trigger.objects.filter(id=obj.trigger.id).all()
        if len(record) > 0:
            trigger = record[0]
            m = re.match(r"{(\d+)}", trigger.expression)
            function_ids = m.groups()
            for function_id in function_ids:
                function = models.Function.objects.filter(id=function_id).all()[0]
                item = models.MonitorItem.objects.filter(id=function.item_id).all()[0]
                item_function = "{%s.%s(%s)}" % (item.key, function.name, function.parameter)
                return re.sub(r"{(\d+)}", item_function, trigger.expression, count=1)

    def get_desc(self, obj):
        record = models.Trigger.objects.filter(id=obj.trigger.id).all()
        if len(record) > 0:
            trigger = record[0]
            return trigger.desc

    def get_level(self, obj):
        return "警告"

    def get_status(self, obj):
        return "1"


class EventSerializer(serializers.ModelSerializer):
    server = serializers.SerializerMethodField()
    detail = serializers.SerializerMethodField()

    class Meta:
        model = Event
        fields = '__all__'

    def get_server(self, obj):
        # obj.target_id是function,再找item的host_id
        function = Function.objects.filter(id=obj.target_id).get()
        host_id = function.item.host_id
        return Server.objects.filter(id=host_id).get().name

    def get_detail(self, obj):
        function = Function.objects.filter(id=obj.target_id).get()
        host_id = function.item.host_id
        host_name = Server.objects.filter(id=host_id).get().name
        return re.sub(r'{HOST.NAME}', host_name, function.trigger.name)


class ProfileSerializer(serializers.ModelSerializer):
    user = serializers.SerializerMethodField()

    class Meta:
        model = Profile
        fields = '__all__'

    def get_user(self, obj):
        # 查询Auth user表中的记录
        user = User.objects.filter(id=obj.user_id).all()[0]
        return {'username': user.username, 'email': user.email}


class UserSerializer(serializers.ModelSerializer):
    profile = ProfileSerializer(required=True)

    class Meta:
        model = User
        # 不下发密码
        fields = ('date_joined', 'email', 'first_name', 'groups', 'id', 'is_active', 'is_staff',
                  'is_superuser', 'last_login', 'last_name', 'profile', 'user_permissions', 'username')


class ProfileBelongUserGroupSerializer(ProfileSerializer):
    belong_group = serializers.SerializerMethodField()

    def get_belong_group(self, obj):
        # 要手动加入该组
        return "0"


class GroupSerializer(serializers.ModelSerializer):
    members = serializers.SerializerMethodField()
    member_list = serializers.SerializerMethodField()
    desc = serializers.SerializerMethodField()

    class Meta:
        model = Group
        fields = ['id', 'name', 'members', 'desc', 'member_list']

    # TODO 删除members返回字段，member_list有完整信息
    def get_members(self, obj):
        members = []
        users = User.objects.all()
        if users is not None:
            for user in users:
                group = Group.objects.get(id=obj.id)
                if group in user.groups.all():
                    members.append(user.first_name + user.last_name)
        return ', '.join(members)

    def get_member_list(self, obj):
        members = []
        users = User.objects.all()
        if users is not None:
            for user in users:
                group = Group.objects.get(id=obj.id)
                if group in user.groups.all():
                    members.append({'id': user.id, 'first_name': user.first_name, 'last_name': user.last_name})
        return members

    def get_desc(self, obj):
        if len(UserGroup.objects.filter(group_id=obj.id).all()) > 0:
            return UserGroup.objects.filter(group_id=obj.id).all()[0].desc


class GeneralConfigSerializer(serializers.ModelSerializer):
    class Meta:
        model = GeneralConfig
        fields = '__all__'


class NotificationModeConfigSerializer(serializers.ModelSerializer):
    detail = serializers.SerializerMethodField()

    class Meta:
        model = NotificationMode
        fields = '__all__'

    def get_detail(self, obj):
        if obj.type == 1:
            return "Smtp服务器:%s Smtp域名:%s 邮件地址:%s" % (obj.smtp_server, obj.smtp_domain, obj.name)
        if obj.type == 2:
            return "Wechat corp id:%s Wechat agent id:%s" % (obj.wechat_corp_id, obj.wechat_agent_id)


class OperationSerializer(serializers.ModelSerializer):
    condition = serializers.SerializerMethodField()
    operation_items = serializers.SerializerMethodField()

    class Meta:
        model = Operation
        fields = '__all__'

    def get_condition(self, obj):
        c = models.OperationCondition.objects.filter(operation=models.Operation.objects.filter(id=obj.id).get())
        if c.count() > 0:
            condition = c.get()
            v = condition.value
            if condition.type == 0:
                v = int(v)
                trigger = models.Trigger.objects.filter(id=v)
                if trigger.count() > 0:
                    # 将后面多余的变量替换掉
                    return trigger.get().name.replace(' on {HOST.NAME}', '')
                else:
                    return '当检查出现问题时'
            else:
                return '%s时间段内的' % v
        else:
            return '当检查出有问题时'

    def get_operation_items(self, obj):
        operation_items = []
        steps = models.OperationStep.objects.filter(operation=models.Operation.objects.get(id=obj.id))
        if steps.count() > 0:
            for step in steps.all():
                start_step = step.start_step
                end_step = step.end_step
                run_type = ''
                if step.run_type == 1:
                    run_type = '发送通知'
                elif step.run_type == 2:
                    run_type = '执行命令'
                try:
                    messages = models.OperationMessage.objects.filter(
                        operation_step=models.OperationStep.objects.get(id=step.id))
                    for message in messages.all():
                        users = models.RelationOperationMessageUser.objects.filter(operation_message=message)
                        if users.count() > 0:
                            u = []
                            for user in users.all():
                                u.append(user.user.user.first_name)
                            u = ','.join(u)
                            ms = ''
                            if message.send_type == 0:
                                ms = "电子邮件"
                            elif message.send_type == 1:
                                ms = "企业微信"
                            operation_items.append(
                                {'id': message.id,
                                 'name': '轮次(%s-%s)使用%s%s到用户%s' % (start_step, end_step, ms, run_type, u)})
                except models.OperationMessage.DoesNotExist:
                    pass

        # return [{'id': 1, 'name': '轮次(1-3)使用企业微信发送通知到运维组'},
        #         {'id': 2, 'name': '轮次(4-0)使用邮件发送通知到运维组'}]
        return operation_items
