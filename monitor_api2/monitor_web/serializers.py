from django.contrib import auth
from django.contrib.auth.models import Group, User
from rest_framework import serializers

from monitor_web.models import Server, Profile, IDC, Asset, Tag, ServerGroup, UserGroup


class IDCSerializer(serializers.ModelSerializer):
    class Meta:
        model = IDC
        fields = ('name', 'floor')

    def create(self, attrs, instance=None):
        assert instance is None, 'Cannot create idc with IDCSerializer'
        (idc_object, created) = IDC.objects\
            .get_or_create(name=attrs.get('name'), floor=attrs.get('floor') if attrs.get('floor') else 0)
        return idc_object

    def update(self, attrs, instance=None):
        assert instance is None, 'Cannot update idc with IDCSerializer'
        a = {}
        a['name'] = attrs.get('name')
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


class ProfileSerializer(serializers.ModelSerializer):
    user = serializers.SerializerMethodField()
    class Meta:
        model = Profile
        fields = '__all__'

    def get_user(self, obj):
        # 查询Auth user表中的记录
        user = User.objects.filter(id=obj.user_id).all()[0]
        return {'username': user.username, 'email': user.email}


class UserGroupSerializer(serializers.ModelSerializer):
    name = serializers.SerializerMethodField()
    profile = ProfileSerializer(required=True, many=True)
    members = serializers.SerializerMethodField()

    class Meta:
        model = UserGroup
        fields = '__all__'

    def get_name(self, obj):
        return obj.group.name

    def get_members(self, obj):
        # 归并UserGroup中的Profile的name
        profiles = UserGroup.objects.filter(id=obj.id).all().values('profile')
        x = []
        if profiles is not None:
            for p in profiles:
                if p['profile'] is not None:
                    x.append(Profile.objects.filter(id=p['profile']).values('name')[0]['name'])
        all_name = ','.join(x)
        return all_name

