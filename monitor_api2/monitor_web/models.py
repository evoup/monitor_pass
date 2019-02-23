from django.db import models


# Create your models here.


class UserProfile(models.Model):
    """
    用户信息
    """
    name = models.CharField(u'姓名', max_length=32)
    email = models.EmailField(u'邮箱')
    telephone = models.CharField(u'座机', max_length=32)
    mobile = models.IntegerField(u'手机')
    user_group = models.ManyToManyField('UserGroup', db_table='r_user_user_group')

    class Meta:
        verbose_name_plural = '用户信息表'
        db_table = 'user'

    def __str__(self):
        return self.name


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
    server_group = models.ManyToManyField('ServerGroup', db_table='r_server_server_group')

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
    alarm_type = models.IntegerField(choices=alarm_type_choices, default=0)

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
    监控事件
    """
    id = models.AutoField(primary_key=True)
    event = models.CharField(max_length=200, null=False)
    date = models.DateTimeField()
    host_id = models.ForeignKey('Server', on_delete=models.CASCADE)
    class Meta:
        # ordering = ('id',)
        verbose_name_plural = '监控事件表'
        db_table = 'event'

    def __str__(self):
        return self.event


class UserGroup(models.Model):
    """
    用户组
    """
    name = models.CharField(max_length=32, unique=True)


    class Meta:
        verbose_name_plural = "用户组表"
        db_table = 'user_group'

    def __str__(self):
        return self.name


class BusinessUnit(models.Model):
    """
    业务线
    """
    name = models.CharField('业务线', max_length=64, unique=True)
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
