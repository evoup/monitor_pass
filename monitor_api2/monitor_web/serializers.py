from rest_framework import serializers
from monitor_web.models import Server, Profile


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




