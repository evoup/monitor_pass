from django.contrib.auth.models import User
from django.db import models


# Create your models here.
from django.db.models.signals import post_save
from django.dispatch import receiver


# ----------扩展django用户开始-----------------
from web.common import TinyIntegerField


class UserProfile(models.Model):
    """
    用户信息，扩展自django auth_user
    """
    name = models.CharField(u'姓名', max_length=32)
    # email = models.EmailField(u'邮箱')
    telephone = models.CharField(u'座机', max_length=32)
    mobile = models.IntegerField(u'手机')
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='profile', default="", editable=False)

    class Meta:
        verbose_name_plural = '用户信息表'
        db_table = 'user_profile'

    def __str__(self):
        return self.name

@receiver(post_save, sender=User)
def create_user_profile(sender, instance, created, **kwargs):
    if created:
        UserProfile.objects.create(user=instance)

@receiver(post_save, sender=User)
def save_user_profile(sender, instance, **kwargs):
    instance.user_profile.save()
# ----------扩展django用户结束-----------------


# ----------扩展django用户组开始-----------------
class UserGroup(models.Model):
    """
    Overwrites original Django Group.
    用户组表 扩展自django auth_group
    """
    group = models.OneToOneField('auth.Group', unique=True, on_delete=models.CASCADE, default="", editable=False)
    desc = models.CharField(max_length=512, blank=True, default="")
    server_group = models.ManyToManyField('ServerGroup', db_table='r_user_group_server_group')

    class Meta:
        verbose_name_plural = "用户组表"
        db_table = 'user_group'

    def __str__(self):
        return "{}".format(self.group.name)
# ----------扩展django用户组结束-----------------


class Server(models.Model):
    """
    服务器
    """
    status_choices = (
        (0, '宕机'),
        (1, '在线'),
        (2, '不监控'),
    )
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'服务器主机名', max_length=40, null=False)
    ip = models.CharField(u'IP地址', max_length=20, null=False)
    status = models.IntegerField(choices=status_choices, default=0)
    asset = models.OneToOneField('Asset', on_delete=models.CASCADE, default="", editable=False)

    hostname = models.CharField(max_length=128, unique=True, default='')
    sn = models.CharField('SN号', max_length=64, db_index=True, default='')
    manufacturer = models.CharField(verbose_name='制造商', max_length=64, null=True, blank=True)
    model = models.CharField('型号', max_length=64, null=True, blank=True)

    manage_ip = models.GenericIPAddressField('管理IP', null=True, blank=True)

    os_platform = models.CharField('系统', max_length=16, null=True, blank=True)
    os_version = models.CharField('系统版本', max_length=16, null=True, blank=True)

    cpu_count = models.IntegerField('CPU个数', null=True, blank=True)
    cpu_physical_count = models.IntegerField('CPU物理个数', null=True, blank=True)
    cpu_model = models.CharField('CPU型号', max_length=128, null=True, blank=True)

    create_at = models.DateTimeField(auto_now_add=True, blank=True)
    server_group = models.ManyToManyField('ServerGroup', db_table='r_server_server_group')
    data_collector = models.ForeignKey('DataCollector', verbose_name='数据收集器', null=True, blank=True, on_delete=models.CASCADE)

    class Meta:
        # ordering = ('id',)
        verbose_name_plural = '服务器表'
        db_table = 'server'

    def __str__(self):
        return self.name


class ServerGroup(models.Model):
    """
    服务器组
    """
    alarm_type_choices = (
        (0, '不接收'),
        (1, '普通报警'),
        (2, '严重报警'),
        (3, '素有报警')
    )
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'服务器组名', max_length=40, null=False)
    desc = models.CharField(u'描述', max_length=512, null=True)
    alarm_type = models.IntegerField(u'告警类型', choices=alarm_type_choices, default=0)

    class Meta:
        verbose_name_plural = '服务器组名'
        db_table = 'server_group'


class DataCollector(models.Model):
    """
    数据收集器
    """
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'数据收集器名', max_length=40, null=False)
    ip = models.CharField(u'IP地址', max_length=20, null=False)
    port = models.IntegerField(u'端口号')
    desc = models.CharField(u'描述', max_length=50, null=True)

    class Meta:
        # ordering = ('id',)
        verbose_name_plural = '数据收集器表'
        db_table = 'data_collector'

    def __str__(self):
        return self.name


class Event(models.Model):
    """
    监控事件，类似zabix的history，这里记录所有事件，最终是一个大表，会用mysql进行分区
    """
    id = models.BigAutoField(primary_key=True)
    event = models.CharField(u'监控事件', max_length=200, null=False)
    date = models.DateTimeField(u'发生时间')
    host_id = models.ForeignKey('Server', on_delete=models.CASCADE)
    class Meta:
        # ordering = ('id',)
        verbose_name_plural = '监控事件表'
        db_table = 'event'

    def __str__(self):
        return self.event


class Template(models.Model):
    """
    模板  类似zabbix的template，模板是一组set的集合
    """
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(u'模板名字', max_length=40, default='')
    server_id = models.ManyToManyField('Server', db_table = 'r_template_server')
    monitor_set_id = models.ManyToManyField('MonitorSet', db_table='r_template_set')
    class Meta:
        verbose_name_plural = "模板表"
        db_table = 'template'

    def __str__(self):
        return self.name


class MonitorSet(models.Model):
    """
    监控集，类似zabbix的application
    """
    name = models.CharField(u'监控集名', max_length=40, null=False)
    class Meta:
        # ordering = ('id',)
        verbose_name_plural = '监控集表'
        db_table = 'set'

    def __str__(self):
        return self.name


class MonitorItem(models.Model):
    """
    监控项，agent可以直接下发，SNMP只是负责检查，JMX的任务数据收集器负责执行，
    所有监控项都从数据收集器落地到TSD
    """
    data_type_choices = (
        (0, 'agent'),
        (1, 'SNMP'),
        (2, 'JMX'),
    )
    name = models.CharField(u'监控项名', max_length=40, null=False)
    data_type = TinyIntegerField(u'数据类型', choices=data_type_choices, default=0)
    delay = models.IntegerField(u'轮询间隔秒数', default=1)
    desc = models.CharField(u'描述', max_length=50, default='')
    error = models.CharField(u'错误', max_length=50, default='')
    key = models.CharField(u'opentsdb指标名', max_length=128, default='')
    multiplier = models.FloatField(u'自定义乘子', default=1.0)
    unit = models.CharField(u'单位', max_length=12, default='')
    host_id = models.ForeignKey('Server', on_delete=models.CASCADE, default="", editable=False)

    class Meta:
        verbose_name_plural = '监控项表'
        db_table = 'item'

    def __str__(self):
        return self.name


class Trigger(models.Model):
    """
    触发器
    """
    id = models.BigAutoField(primary_key=True)
    expression = models.CharField(u'触发器表达式', max_length=256, default='')
    template_id = models.ForeignKey('Template', verbose_name='所属模板', related_name='t', on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = '触发器表'
        db_table = 'trigger'

    def __str__(self):
        return self.expression


class BusinessUnit(models.Model):
    """
    业务线
    """
    name = models.CharField(u'业务线', max_length=64, unique=True)
    contact = models.ForeignKey('UserGroup', verbose_name='业务联系人', related_name='c', on_delete=models.CASCADE)  # 多个人
    manager = models.ForeignKey('UserGroup', verbose_name='系统管理员', related_name='m', on_delete=models.CASCADE)  # 多个人

    class Meta:
        verbose_name_plural = "业务线表"
        db_table = 'business_unit'

    def __str__(self):
        return self.name


class IDC(models.Model):
    """
    机房信息
    """
    name = models.CharField('机房', max_length=32)
    floor = models.IntegerField('楼层', default=1)

    class Meta:
        verbose_name_plural = "机房表"
        db_table = 'idc'

    def __str__(self):
        return self.name


class Tag(models.Model):
    """
    资产标签
    """
    name = models.CharField('标签', max_length=32, unique=True)

    class Meta:
        verbose_name_plural = "标签表"
        db_table = 'tag'

    def __str__(self):
        return self.name


class Asset(models.Model):
    """
    资产信息表，所有资产公共信息（交换机，服务器，防火墙等）
    """
    device_type_choices = (
        (1, '服务器'),
        (2, '交换机'),
        (3, '防火墙'),
    )
    device_status_choices = (
        (1, '上架'),
        (2, '在线'),
        (3, '离线'),
        (4, '下架'),
    )

    device_type_id = models.IntegerField(choices=device_type_choices, default=1)
    device_status_id = models.IntegerField(choices=device_status_choices, default=1)

    cabinet_num = models.CharField('机柜号', max_length=30, null=True, blank=True)
    cabinet_order = models.CharField('机柜中序号', max_length=30, null=True, blank=True)

    idc = models.ForeignKey('IDC', verbose_name='IDC机房', null=True, blank=True, on_delete=models.CASCADE)
    business_unit = models.ForeignKey('BusinessUnit', verbose_name='属于的业务线', null=True, blank=True,
                                      on_delete=models.CASCADE)

    tag = models.ManyToManyField('Tag', db_table = 'r_asset_tag')

    latest_date = models.DateField(null=True)
    create_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name_plural = "资产表"
        db_table = 'asset'

    def __str__(self):
        return "%s-%s-%s" % (self.idc.name, self.cabinet_num, self.cabinet_order)


class NetworkDevice(models.Model):
    """
    网络设备
    """
    asset = models.OneToOneField('Asset', on_delete=models.CASCADE, default="", editable=False)
    management_ip = models.CharField('管理IP', max_length=64, blank=True, null=True)
    vlan_ip = models.CharField('VlanIP', max_length=64, blank=True, null=True)
    intranet_ip = models.CharField('内网IP', max_length=128, blank=True, null=True)
    sn = models.CharField('SN号', max_length=64, unique=True)
    manufacture = models.CharField(verbose_name=u'制造商', max_length=128, null=True, blank=True)
    model = models.CharField('型号', max_length=128, null=True, blank=True)
    port_num = models.SmallIntegerField('端口个数', null=True, blank=True)
    device_detail = models.CharField('设置详细配置', max_length=255, null=True, blank=True)

    class Meta:
        verbose_name_plural = "网络设备"
