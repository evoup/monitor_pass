from rest_framework import serializers
from monitor_web.models import Server, Profile, IDC
from monitor_web.validation import MyCustomValidators


class UserProfileSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = Profile
        # fields = {'id', 'name', 'email', 'telephone', 'mobile'}
        fields = '__all__'  # all model fields will be included


class ServerSerializer(serializers.ModelSerializer):
    class Meta:
        model = Server
        # fields = ('id', 'name', 'ip')
        fields = '__all__'


class IDCSerializer(serializers.ModelSerializer):
    class Meta:
        model = IDC
        fields = ('name', 'floor')
        # validators = [MyCustomValidators(), ]

    def create(self, attrs, instance=None):
        assert instance is None, 'Cannot create idc with IDCSerializer'
        a = {}
        a['name'] = attrs.get('name')
        if attrs.get('floor'):
            a['floor'] = attrs.get('floor')
        (idc_object, created) = IDC.objects.get_or_create(a)
        return idc_object

    def update(self, attrs, instance=None):
        assert instance is None, 'Cannot update idc with IDCSerializer'
        a = {}
        a['name'] = attrs.get('name')
        if attrs.get('floor'):
            a['floor'] = attrs.get('floor')
        (idc_object, created) = IDC.objects.get_or_create(a)

        return idc_object


