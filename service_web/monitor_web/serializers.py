from rest_framework import serializers
from monitor_web.models import Server


class ServerSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = Server
        fields = ('id', 'name', 'ip')

    # def create(self, validated_data):
    #     return Server.objects.create(**validated_data)
    #
    # def update(self, instance, validated_data):
    #     instance.name = validated_data.get('name', instance.name)
    #     instance.ip = validated_data.get('ip', instance.ip)
    #     instance.save()
    #     return instance


