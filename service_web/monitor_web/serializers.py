from rest_framework import serializers
from monitor_web.models import Server, UserProfile


class UserProfileSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = UserProfile
        # fields = {'id', 'name', 'email', 'telephone', 'mobile'}
        fields = '__all__'  # all model fields will be included


class ServerSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = Server
        fields = ('id', 'name', 'ip')



