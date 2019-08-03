import re

from django.contrib.auth.models import Group, User
from rest_framework import serializers

from monitor_web import models
from monitor_web.models import Server, Profile, IDC, Asset, Tag, ServerGroup, UserGroup, Template, MonitorItem, Trigger, \
    Function, DataCollector


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
    tag = TagSerializer(required=True)
    idc = IDCSerializer(required=True)

    class Meta:
        model = Asset
        fields = '__all__'


class ServerSerializer(serializers.ModelSerializer):
    asset = AssetSerializer(required=True)
    data_collector = serializers.SerializerMethodField()

    class Meta:
        model = Server
        fields = '__all__'

    def get_data_collector(self, obj):
        d = models.DataCollector.objects.filter(id=obj.id).all()
        if len(d):
            return d[0].name
        else:
            return None


class ServerGroupSerializer(serializers.ModelSerializer):
    class Meta:
        model = ServerGroup
        fields = '__all__'


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

    class Meta:
        model = Template
        fields = '__all__'

    def get_items(self, obj):
        template_id = obj.id
        return len(models.MonitorItem.objects.filter(template_id=template_id).all())

    def get_triggers(self, obj):
        # 找template下的item，每个item汇总trigger数目
        triggers = 0
        template_id = obj.id
        for item in models.MonitorItem.objects.filter(template_id=template_id).all():
            triggers = triggers + len(models.Function.objects.filter(item=item.id).all())
        return triggers


class TriggerSerializer(serializers.ModelSerializer):
    class Meta:
        model = Trigger
        fields = '__all__'


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
    desc = serializers.SerializerMethodField()

    class Meta:
        model = Group
        fields = ['id', 'name', 'members', 'desc']

    def get_members(self, obj):
        members = []
        users = User.objects.all()
        if users is not None:
            for user in users:
                group = Group.objects.get(id=obj.id)
                if group in user.groups.all():
                    members.append(user.first_name + user.last_name)
        return ', '.join(members)

    def get_desc(self, obj):
        if len(UserGroup.objects.filter(group_id=obj.id).all()) > 0:
            return UserGroup.objects.filter(group_id=obj.id).all()[0].desc
