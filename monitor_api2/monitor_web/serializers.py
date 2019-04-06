from rest_framework import serializers, viewsets
from monitor_web.models import Server, Profile, IDC, Asset, Tag, ServerGroup
from monitor_web.validation import MyCustomValidators


class UserProfileSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = Profile
        # fields = {'id', 'name', 'email', 'telephone', 'mobile'}
        fields = '__all__'  # all model fields will be included


class IDCSerializer(serializers.ModelSerializer):
    class Meta:
        model = IDC
        fields = ('name', 'floor')
        # validators = [MyCustomValidators(), ]

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



