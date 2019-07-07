from django.contrib.auth.models import Group, User
from rest_framework import serializers

from monitor_web import models
from monitor_web.models import Server, Profile, IDC, Asset, Tag, ServerGroup, UserGroup, Template, MonitorItem, Trigger


class IDCSerializer(serializers.ModelSerializer):
    class Meta:
        model = IDC
        fields = ('name', 'floor')

    def create(self, attrs, instance=None):
        assert instance is None, 'Cannot create idc with IDCSerializer'
        (idc_object, created) = IDC.objects \
            .get_or_create(name=attrs.get('name'), floor=attrs.get('floor') if attrs.get('floor') else 0)
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

    class Meta:
        model = Server
        fields = '__all__'


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
        return 3


class TriggerSerializer(serializers.ModelSerializer):
    class Meta:
        model = Trigger
        fields = '__all__'


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
