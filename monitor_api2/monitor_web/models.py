from django.contrib.auth.models import User
from django.db import models
from django.db.models.signals import post_save
from django.dispatch import receiver
from django_unixdatetimefield import UnixDateTimeField

from web.common.db_fields import TinyIntegerField


class Profile(models.Model):
    """
    用户附加信息，扩展自django auth_user
    """
    # 姓名和email放在User表的first_name里，因为分页排序字段要在嵌套Serializer外层，不然drf会不认识字段，坑!
    telephone = models.CharField(u'座机', max_length=32, blank=True, null=True)
    mobile = models.CharField(u'手机', max_length=32, blank=True, null=True)
    desc = models.CharField(u'描述', max_length=255, blank=True, null=True)
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='profile')
    wechat_id = models.CharField(u'企业微信id', max_length=255, blank=True, null=True)

    class Meta:
        verbose_name_plural = '用户信息表'
        db_table = 'user_profile'

    def __str__(self):
        return self.user.username if self.user.first_name is None else self.user.first_name


@receiver(post_save, sender=User)
def create_user_profile(sender, instance, created, **kwargs):
    if created:
        Profile.objects.create(user=instance)


@receiver(post_save, sender=User)
def save_user_profile(sender, instance, **kwargs):
    instance.profile.save()


# ----------扩展django用户结束-----------------


# ----------扩展django用户组开始-----------------
class UserGroup(models.Model):
    """
    Overwrites original Django Group.
    用户组表 扩展自django auth_group
    """
    group = models.OneToOneField('auth.Group', unique=True, on_delete=models.CASCADE)
    profile = models.ManyToManyField('Profile', db_table='r_usergroup_profile', blank=True)
    desc = models.CharField(max_length=512, blank=True, default="")
    server_group = models.ManyToManyField('ServerGroup', db_table='r_user_group_server_group', blank=True)

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
        (3, '状态未知'),
    )
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'服务器主机名', max_length=40, null=False)
    ip = models.CharField(u'IP地址', max_length=20, null=True)
    status = models.IntegerField(choices=status_choices, default=0)
    asset = models.OneToOneField('Asset', on_delete=models.CASCADE, null=True, blank=True)

    hostname = models.CharField(max_length=128, unique=True, null=True)
    # 主板sn
    sn = models.CharField('SN号', max_length=64, db_index=True, default='', null=True)
    # 主板制造商
    manufacturer = models.CharField(verbose_name='制造商', max_length=64, null=True, blank=True)
    # 主板型号
    model = models.CharField('型号', max_length=64, null=True, blank=True)

    manage_ip = models.GenericIPAddressField('管理IP', null=True, blank=True)

    os_platform = models.CharField('系统', max_length=16, null=True, blank=True)
    os_version = models.CharField('系统版本', max_length=16, null=True, blank=True)

    cpu_count = models.IntegerField('CPU个数', null=True, blank=True)
    cpu_physical_count = models.IntegerField('CPU物理个数', null=True, blank=True)
    cpu_model = models.CharField('CPU型号', max_length=128, null=True, blank=True)

    create_at = models.DateTimeField(auto_now_add=True, blank=True)
    last_online = models.DateTimeField(blank=True, null=True)
    server_groups = models.ManyToManyField('ServerGroup', db_table='r_server_server_group')
    templates = models.ManyToManyField('Template', db_table='r_server_template')
    agent_address = models.CharField('监控代理地址', max_length=50, default='', null=True)
    ssh_address = models.CharField('SSH地址', max_length=50, default='', null=True)
    jmx_address = models.CharField('JMX地址', max_length=50, default='', null=True)
    snmp_address = models.CharField('SNMP地址', max_length=50, default='', null=True)
    data_collector = models.ForeignKey('DataCollector', verbose_name='数据收集器', null=True, blank=True,
                                       on_delete=models.SET_NULL)
    # 数据收集器会扫描这个字段，如果未更新则会触发rpc调用监控代理的更新
    config_updated = models.BooleanField(default=True)

    class Meta:
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
        (3, '所有报警')
    )
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'服务器组名', max_length=40, null=False)
    desc = models.CharField(u'描述', max_length=512, blank=True, null=True)
    alarm_type = models.IntegerField(u'告警类型', choices=alarm_type_choices, default=0)

    class Meta:
        verbose_name_plural = '服务器组表'
        db_table = 'server_group'

    def __str__(self):
        return self.name


class DataCollector(models.Model):
    """
    数据收集器
    """
    id = models.AutoField(primary_key=True)
    name = models.CharField(u'数据收集器名', max_length=40, null=False)
    ip = models.GenericIPAddressField(u'IP地址', null=False)
    port = models.IntegerField(u'端口号')

    class Meta:
        verbose_name_plural = '数据收集器表'
        db_table = 'data_collector'

    def __str__(self):
        return self.name


class Event(models.Model):
    """
    监控事件，类似zabbix的event，这里记录所有事件，最终是一个大表，最好用mysql进行分区
    """
    event_type_choice = (
        (0, 'normal'),
        (1, 'caution'),
        (2, 'warning')
    )
    id = models.BigAutoField(primary_key=True)
    event = models.CharField(u'监控事件', max_length=200, null=False)
    time = UnixDateTimeField(u'发生时间')
    type = TinyIntegerField(u'事件类型', choices=event_type_choice, default=0)
    acknowledged = models.BooleanField(default=False, null=True)
    acknowledge = models.CharField(u'确认文字', max_length=200, default='', null=True)
    target_id = models.IntegerField(u'事件对象id', null=False)

    class Meta:
        verbose_name_plural = '监控事件表'
        db_table = 'event'
        indexes = [models.Index(fields=['target_id']), models.Index(fields=['time'])]

    def __str__(self):
        return self.event


class Alert(models.Model):
    """
    告警
    """
    id = models.BigAutoField(primary_key=True)
    time = models.DateTimeField(auto_now_add=True)
    send_to = models.ForeignKey('Profile', on_delete=models.CASCADE, default="", editable=False)
    subject = models.CharField(u'告警正文', max_length=255, default='', null=False)
    monitor_item = models.ForeignKey('MonitorItem', on_delete=models.CASCADE, default="", editable=False)

    class Meta:
        verbose_name_plural = '告警表'
        db_table = 'alert'

    def __str__(self):
        return self.subject


class Template(models.Model):
    """
    模板  类似zabbix的template，模板是一组set的集合
    """
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(u'模板名字', max_length=40, default='')
    server_id = models.ManyToManyField('Server', db_table='r_template_server')
    monitor_set_id = models.ManyToManyField('MonitorSet', db_table='r_template_set')
    server_group = models.ManyToManyField('ServerGroup', db_table='r_template_server_group')
    template_id = models.ManyToManyField('Template', db_table='r_template_template')

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
    name = models.CharField(u'监控项名', max_length=80, null=False)
    data_type = TinyIntegerField(u'数据类型', choices=data_type_choices, default=0)
    delay = models.IntegerField(u'轮询间隔秒数', default=1)
    desc = models.CharField(u'描述', max_length=512, default='')
    error = models.CharField(u'错误', max_length=128, default='')
    key = models.CharField(u'opentsdb指标名', max_length=128, default='')
    multiplier = models.FloatField(u'自定义乘子', default=1.0)
    unit = models.CharField(u'单位', max_length=12, default='')
    # 0代表不属于任何
    host_id = models.IntegerField(u'对应主机id', 0)
    # 0代表不属于任何
    template_id = models.IntegerField(u'对应模板id', 0)
    delta = models.IntegerField(u'增量类型', default='0', editable=False)
    item_copy_from = models.IntegerField(u'监控项复制自', default='0', editable=False, null=True)

    class Meta:
        verbose_name_plural = '监控项表'
        db_table = 'item'
        unique_together = ('key', 'host_id', 'template_id')
        indexes = [models.Index(fields=['host_id']), models.Index(fields=['template_id']),
                   models.Index(fields=['item_copy_from'])]

    def __str__(self):
        return self.name


class RelationUserItem(models.Model):
    """
    用户监控项关系表
    """
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    template = models.ForeignKey(Template, on_delete=models.CASCADE)
    item = models.ForeignKey(MonitorItem, on_delete=models.CASCADE)
    status = models.BooleanField(default=True)

    class Meta:
        verbose_name_plural = '用户监控项关系表'
        unique_together = ('user', 'item')
        db_table = 'r_user_item'

    def __str__(self):
        return '%s:%s:%s' % (self.user_id, self.template, self.item_id)


class Trigger(models.Model):
    """
    触发器
    """
    level_choices = (
        (0, '信息'),
        (1, '警告'),
        (2, '严重警告'),
        (3, '灾难警告'),
    )
    id = models.BigAutoField(primary_key=True)
    expression = models.CharField(u'触发器表达式', max_length=256, default='')
    template = models.ForeignKey('Template', verbose_name='所属模板', related_name='t', on_delete=models.CASCADE)
    name = models.CharField(u'触发器名', max_length=256, default='', null=False)
    desc = models.CharField(u'描述', max_length=256, default='', null=True)
    level = TinyIntegerField(u'严重等级', choices=level_choices, null=True, default=0)
    trigger_copy_from = models.IntegerField(u'触发器复制自', default='0', editable=False, null=True)

    class Meta:
        verbose_name_plural = '触发器表'
        db_table = 'trigger'
        indexes = [models.Index(fields=['trigger_copy_from'])]

    def __str__(self):
        return self.expression


class Diagram(models.Model):
    """
    图表
    """
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(u'图表名称', max_length=256, default='')
    width = models.CharField(u'宽度', max_length=40, default='100%')
    height = models.CharField(u'高度', max_length=40, default='230')
    template = models.ForeignKey('Template', verbose_name='所属模板', related_name='d', on_delete=models.SET_NULL,
                                 null=True)

    class Meta:
        verbose_name_plural = '图表表'
        db_table = 'diagram'


class DiagramItem(models.Model):
    """
    图表项
    """
    id = models.BigAutoField(primary_key=True)
    diagram = models.ForeignKey(Diagram, on_delete=models.SET_NULL, null=True)
    item = models.ForeignKey(MonitorItem, on_delete=models.SET_NULL, null=True)

    class Meta:
        verbose_name_plural = '图表项表'
        db_table = 'diagram_item'


class GrafanaDashboard(models.Model):
    """
    grafana仪表盘
    """
    device_type_choices = (
        (1, '服务器'),
        (2, '交换机'),
        (3, '防火墙'),
    )
    id = models.BigAutoField(primary_key=True)
    dashboard_uid = models.CharField(u'仪表盘uid', max_length=100, default='', null=False)
    device_id = models.IntegerField('服务器或网络设备的id', null=True)
    device_type = models.IntegerField(choices=device_type_choices, default=1)
    diagram = models.ForeignKey(Diagram, on_delete=models.SET_NULL, null=True)

    class Meta:
        verbose_name_plural = 'Grafana仪表盘表'
        db_table = 'grafana_dashboard'
        unique_together = ('device_id', 'device_type', 'diagram')
        indexes = [models.Index(fields=['device_id'])]


class DashBoard(models.Model):
    """
    首页dashboard
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        verbose_name_plural = '首页仪表盘表'
        db_table = 'dashboard'


class NotificationMode(models.Model):
    """
    告警通知方式设置
    """
    notification_mode_choices = (
        (1, '邮件'),
        (2, '企业微信'),
    )
    id = models.BigAutoField(primary_key=True)
    type = models.IntegerField(choices=notification_mode_choices, default=1)
    name = models.CharField(u'名称', max_length=20, default='', null=False)
    smtp_server = models.CharField(u'smtp服务器', max_length=255, default='', null=True)
    smtp_domain = models.CharField(u'smtp域', max_length=255, default='', null=True)
    smtp_port = models.IntegerField('smtp端口', null=True)
    username = models.CharField(u'用户名', max_length=255, default='', null=True)
    passwd = models.CharField(u'密码', max_length=255, default='', null=True)
    wechat_agent_id = models.CharField(u'企业微信agent id', max_length=255, default='', null=True)
    wechat_corp_id = models.CharField(u'企业微信corp id', max_length=255, default='', null=True)
    wechat_secret = models.CharField(u'企业微信secret', max_length=255, default='', null=True)
    enabled = models.BooleanField(default=True)

    class Meta:
        verbose_name_plural = '告警通知方式设置'
        db_table = 'notification_mode'


class Function(models.Model):
    """
    函数
    """
    id = models.BigAutoField(primary_key=True)
    item = models.ForeignKey(MonitorItem, on_delete=models.CASCADE)
    trigger = models.ForeignKey(Trigger, on_delete=models.CASCADE)
    name = models.CharField(u'函数', max_length=10, default='', null=False)
    parameter = models.CharField(u'参数', max_length=10, default='', null=False)

    class Meta:
        verbose_name_plural = '函数表'
        db_table = 'function'
        unique_together = ('item', 'trigger', 'name', 'parameter')

    def validate_unique(self, *args, **kwargs):
        super(Function, self).validate_unique(*args, **kwargs)

        if self.__class__.objects. \
                filter(item=self.item, trigger=self.trigger, name=self.name, parameter=self.parameter). \
                exists():
            from django.core.exceptions import ValidationError
            raise ValidationError(
                message='MyModel with this (fk, my_field) already exists.',
                code='unique_together',
            )


class BusinessUnit(models.Model):
    """
    业务线
    """
    name = models.CharField(u'业务线', max_length=64, unique=True)
    contact = models.ForeignKey('Profile', verbose_name='业务联系人', related_name='c', on_delete=models.CASCADE)  # 多个人
    manager = models.ForeignKey('Profile', verbose_name='系统管理员', related_name='m', on_delete=models.CASCADE)  # 多个人

    class Meta:
        verbose_name_plural = "业务线表"
        db_table = 'business_unit'

    def __str__(self):
        return self.name


class IDC(models.Model):
    """
    机房信息
    """
    id = models.AutoField(primary_key=True)
    name = models.CharField('机房', max_length=32, blank=False)
    floor = models.IntegerField('楼层', null=True)
    desc = models.CharField(u'备注', max_length=256, default='', null=True)

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
    host_name = models.CharField(max_length=256, unique=False, null=True)
    network_device_name = models.CharField(max_length=256, unique=False, null=True)
    device_type_id = models.IntegerField(choices=device_type_choices, default=1)
    device_status_id = models.IntegerField(choices=device_status_choices, default=1)

    cabinet_num = models.CharField('机柜号', max_length=30, null=True, blank=True)
    cabinet_order = models.CharField('机柜中序号', max_length=30, null=True, blank=True)

    idc = models.ForeignKey('IDC', verbose_name='IDC机房', null=True, blank=True, on_delete=models.SET_NULL)
    business_unit = models.ForeignKey('BusinessUnit', verbose_name='属于的业务线', null=True, blank=True,
                                      on_delete=models.CASCADE)

    tag = models.ManyToManyField('Tag', db_table='r_asset_tag')

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
        db_table = 'network_device'

    def __str__(self):
        return self.sn


class Disk(models.Model):
    """
    硬盘信息
    """
    slot = models.CharField('插槽位', max_length=8)
    model = models.CharField('磁盘型号', max_length=32)
    capacity = models.FloatField('磁盘容量GB')
    pd_type = models.CharField('磁盘类型', max_length=32)
    server_obj = models.ForeignKey('Server', related_name='disk', null=True, blank=True, on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = "硬盘表"
        db_table = 'disk'

    def __str__(self):
        return self.slot


class NIC(models.Model):
    """
    网卡信息
    """
    name = models.CharField('网卡名称', max_length=128)
    hwaddr = models.CharField('网卡mac地址', max_length=64)
    netmask = models.CharField(max_length=64)
    ipaddrs = models.CharField('ip地址', max_length=256)
    up = models.BooleanField(default=False)
    server_obj = models.ForeignKey('Server', related_name='nic', null=True, blank=True, on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = "网卡表"
        db_table = 'nic'

    def __str__(self):
        return self.name


class Memory(models.Model):
    """
    内存信息
    """
    slot = models.CharField('插槽位', max_length=32)
    manufacturer = models.CharField('制造商', max_length=32, null=True, blank=True)
    model = models.CharField('型号', max_length=64)
    capacity = models.FloatField('容量', null=True, blank=True)
    sn = models.CharField('内存SN号', max_length=64, null=True, blank=True)
    speed = models.CharField('速度', max_length=16, null=True, blank=True)

    server_obj = models.ForeignKey('Server', related_name='memory', null=True, blank=True, on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = "内存表"
        db_table = 'memory'

    def __str__(self):
        return self.slot


class OperationCondition(models.Model):
    """
    操作条件
    """
    type_choices = (
        (0, '触发器被触发'),
        (1, '在计划时间段内'),
    )
    id = models.AutoField(primary_key=True)
    operation = models.ForeignKey('Operation', on_delete=models.CASCADE)
    type = models.IntegerField(choices=type_choices, default=0)
    # 对于type=0，0是触发器没有触发，1是触发了，以此类推
    operator = models.IntegerField('操作符', default=1)
    value = models.CharField('值', max_length=255)

    class Meta:
        verbose_name_plural = "操作条件表"
        db_table = 'operation_condition'


class Operation(models.Model):
    """
    操作
    """
    status_choices = (
        (0, '禁用'),
        (1, '启用'),
    )
    id = models.AutoField(primary_key=True)
    name = models.CharField('操作名称', max_length=128)
    status = models.IntegerField(choices=status_choices, default=1)
    period = models.IntegerField('第一阶段结束后到第二阶段的秒数', null=False, default=3600)
    title = models.CharField('简短消息', max_length=128)
    content = models.CharField('简短消息', max_length=512)

    class Meta:
        verbose_name_plural = "操作表"
        db_table = 'operation'

    def __str__(self):
        return self.name


class OperationStep(models.Model):
    """
    操作步骤，第一轮是立刻开始的，
    """
    operation_status_choices = (
        (1, '发送通知'),
        (2, '执行命令'),
    )
    id = models.BigAutoField(primary_key=True)
    operation = models.ForeignKey('Operation', on_delete=models.CASCADE, default=1, null=False)
    start_step = models.IntegerField('开始的阶段', null=False, default=1)
    end_step = models.IntegerField('结束的阶段', null=False, default=1)
    inteval = models.IntegerField('除了第一阶段的每阶段的间隔秒数', null=False, default=3600)
    run_type = models.IntegerField('操作类型', choices=operation_status_choices, null=False, default=1)

    class Meta:
        verbose_name_plural = "操作步骤表"
        db_table = 'operation_step'


class OperationCommand(models.Model):
    """
    操作步骤中的命令
    """
    exec_at_type_choices = (
        (1, '在被监控主机上'),
        (2, '在监控服务端上'),
    )
    id = models.BigAutoField(primary_key=True)
    operation_step = models.ForeignKey('OperationStep', on_delete=models.CASCADE, default=1, null=True)
    exec_at = models.IntegerField('在什么地方执行', choices=exec_at_type_choices, null=False, default=1)
    port = models.IntegerField('运行的端口', null=True)
    ssh_username = models.CharField('用户名', max_length=128)
    ssh_password = models.CharField('密码', max_length=128)
    command = models.TextField(null=False)
    notification_mode = models.ForeignKey('NotificationMode', null=True, on_delete=models.SET_NULL)

    class Meta:
        verbose_name_plural = "执行命令的操作"
        db_table = 'operation_command'


class OperationMessage(models.Model):
    """
    操作步骤中的消息
    """
    id = models.BigAutoField(primary_key=True)
    operation_step = models.ForeignKey('OperationStep', on_delete=models.CASCADE, default=1, null=True)
    subject = models.CharField(default='', max_length=80, verbose_name='告警主题')
    message = models.CharField(default='', max_length=1024, verbose_name='告警正文')
    send_type = models.IntegerField('发送信息的方式')

    class Meta:
        verbose_name_plural = "发送消息的操作"
        db_table = 'operation_message'


class RelationOperationMessageUser(models.Model):
    """
    发送消息的操作对用户关系表
    """
    id = models.BigAutoField(primary_key=True)
    operation_message = models.ForeignKey('OperationMessage', on_delete=models.CASCADE)
    user = models.ForeignKey('Profile', on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = "发送消息的操作对用户关系表"
        db_table = 'r_operation_message_user'


class RelationOperationCommandUser(models.Model):
    """
    执行命令的操作对服务器关系表
    """
    id = models.BigAutoField(primary_key=True)
    operation_command = models.ForeignKey('OperationCommand', on_delete=models.CASCADE)
    server = models.ForeignKey('Server', on_delete=models.CASCADE)

    class Meta:
        verbose_name_plural = '执行命令的操作对服务器关系表'
        db_table = 'r_operation_command_server'


class AssetRecord(models.Model):
    """
    资产变更记录,creator为空时，表示是资产汇报的数据。
    """
    asset_obj = models.ForeignKey('Asset', related_name='ar', null=True, blank=True, on_delete=models.CASCADE)
    content = models.TextField(null=True)
    creator = models.ForeignKey('Profile', null=True, blank=True, on_delete=models.CASCADE)
    create_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name_plural = "资产记录表"
        db_table = 'addset_record'

    def __str__(self):
        return "%s-%s-%s" % (self.asset_obj.idc.name, self.asset_obj.cabinet_num, self.asset_obj.cabinet_order)


class AssetErrorLog(models.Model):
    """
    错误日志,如：agent采集数据错误 或 运行错误
    """
    asset_obj = models.ForeignKey('Asset', null=True, blank=True, on_delete=models.CASCADE)
    title = models.CharField(max_length=16)
    content = models.TextField()
    create_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name_plural = '错误日志表'
        db_table = 'assert_error_log'

    def __str__(self):
        return self.title


class GeneralConfig(models.Model):
    """
    常规设置
    """
    grafana_api_key = models.CharField(u'grafana的api key', max_length=255, blank=True, null=True)
    send_warn = models.BooleanField(default=False)
    stop_command = models.CharField(u'批量命令执行中的命令过滤规则', max_length=4096, blank=True, null=True)
    ssh_private_key_dir = models.CharField(u'ssh私钥路径', max_length=512, blank=True, null=True)

    class Meta:
        verbose_name_plural = '常规设置表'
        db_table = 'general_config'

    def __str__(self):
        return "常规设置"


class HistoryStringData(models.Model):
    """
    历史的字符串数据，历史的数字数据存在opentsdb中
    """
    monitor_item = models.BigIntegerField(u'监控项', null=False)
    ts = models.IntegerField(u'时间戳（秒）', null=False)
    value = models.CharField(u'值', max_length=255, null=False)

    class Meta:
        verbose_name_plural = '历史字符串数据表'
        db_table = 'history_string_data'
        # 不使用唯一索引，有参考zabbix，查了文档是因为普通索引+change buffer适合写多读少的场景，充分使用change buffer，比较适合账单和日志业务场景
        indexes = [
            models.Index(fields=['monitor_item', 'ts'])
        ]
