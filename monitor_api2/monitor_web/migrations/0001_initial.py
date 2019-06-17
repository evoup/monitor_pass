# Generated by Django 2.1.3 on 2019-06-17 13:38

from django.conf import settings
from django.db import migrations, models
import django.db.models.deletion
import web.common.db_fields


class Migration(migrations.Migration):

    initial = True

    dependencies = [
        ('auth', '0009_alter_user_last_name_max_length'),
        migrations.swappable_dependency(settings.AUTH_USER_MODEL),
    ]

    operations = [
        migrations.CreateModel(
            name='Alert',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('time', models.DateTimeField(auto_now_add=True)),
                ('subject', models.CharField(default='', max_length=255, verbose_name='告警正文')),
            ],
            options={
                'db_table': 'alert',
                'verbose_name_plural': '告警表',
            },
        ),
        migrations.CreateModel(
            name='Asset',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('device_type_id', models.IntegerField(choices=[(1, '服务器'), (2, '交换机'), (3, '防火墙')], default=1)),
                ('device_status_id', models.IntegerField(choices=[(1, '上架'), (2, '在线'), (3, '离线'), (4, '下架')], default=1)),
                ('cabinet_num', models.CharField(blank=True, max_length=30, null=True, verbose_name='机柜号')),
                ('cabinet_order', models.CharField(blank=True, max_length=30, null=True, verbose_name='机柜中序号')),
                ('latest_date', models.DateField(null=True)),
                ('create_at', models.DateTimeField(auto_now_add=True)),
            ],
            options={
                'db_table': 'asset',
                'verbose_name_plural': '资产表',
            },
        ),
        migrations.CreateModel(
            name='AssetErrorLog',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('title', models.CharField(max_length=16)),
                ('content', models.TextField()),
                ('create_at', models.DateTimeField(auto_now_add=True)),
                ('asset_obj', models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.Asset')),
            ],
            options={
                'db_table': 'assert_error_log',
                'verbose_name_plural': '错误日志表',
            },
        ),
        migrations.CreateModel(
            name='AssetRecord',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('content', models.TextField(null=True)),
                ('create_at', models.DateTimeField(auto_now_add=True)),
                ('asset_obj', models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, related_name='ar', to='monitor_web.Asset')),
            ],
            options={
                'db_table': 'addset_record',
                'verbose_name_plural': '资产记录表',
            },
        ),
        migrations.CreateModel(
            name='BusinessUnit',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=64, unique=True, verbose_name='业务线')),
            ],
            options={
                'db_table': 'business_unit',
                'verbose_name_plural': '业务线表',
            },
        ),
        migrations.CreateModel(
            name='DataCollector',
            fields=[
                ('id', models.AutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=40, verbose_name='数据收集器名')),
                ('ip', models.CharField(max_length=20, verbose_name='IP地址')),
                ('port', models.IntegerField(verbose_name='端口号')),
                ('desc', models.CharField(blank=True, max_length=50, null=True, verbose_name='描述')),
            ],
            options={
                'db_table': 'data_collector',
                'verbose_name_plural': '数据收集器表',
            },
        ),
        migrations.CreateModel(
            name='Disk',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('slot', models.CharField(max_length=8, verbose_name='插槽位')),
                ('model', models.CharField(max_length=32, verbose_name='磁盘型号')),
                ('capacity', models.FloatField(verbose_name='磁盘容量GB')),
                ('pd_type', models.CharField(max_length=32, verbose_name='磁盘类型')),
            ],
            options={
                'db_table': 'disk',
                'verbose_name_plural': '硬盘表',
            },
        ),
        migrations.CreateModel(
            name='Event',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('event', models.CharField(max_length=200, verbose_name='监控事件')),
                ('time', models.DateTimeField(verbose_name='发生时间')),
                ('type', web.common.db_fields.TinyIntegerField(choices=[(0, 'normal'), (1, 'caution'), (2, 'warning')], default=0, verbose_name='事件类型')),
                ('acknowledge', models.CharField(default='', max_length=200, verbose_name='确认文字')),
            ],
            options={
                'db_table': 'event',
                'verbose_name_plural': '监控事件表',
            },
        ),
        migrations.CreateModel(
            name='IDC',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=32, verbose_name='机房')),
                ('floor', models.IntegerField(blank=True, default=1, verbose_name='楼层')),
            ],
            options={
                'db_table': 'idc',
                'verbose_name_plural': '机房表',
            },
        ),
        migrations.CreateModel(
            name='Memory',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('slot', models.CharField(max_length=32, verbose_name='插槽位')),
                ('manufacturer', models.CharField(blank=True, max_length=32, null=True, verbose_name='制造商')),
                ('model', models.CharField(max_length=64, verbose_name='型号')),
                ('capacity', models.FloatField(blank=True, null=True, verbose_name='容量')),
                ('sn', models.CharField(blank=True, max_length=64, null=True, verbose_name='内存SN号')),
                ('speed', models.CharField(blank=True, max_length=16, null=True, verbose_name='速度')),
            ],
            options={
                'db_table': 'memory',
                'verbose_name_plural': '内存表',
            },
        ),
        migrations.CreateModel(
            name='MonitorItem',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=40, verbose_name='监控项名')),
                ('data_type', web.common.db_fields.TinyIntegerField(choices=[(0, 'agent'), (1, 'SNMP'), (2, 'JMX')], default=0, verbose_name='数据类型')),
                ('delay', models.IntegerField(default=1, verbose_name='轮询间隔秒数')),
                ('desc', models.CharField(default='', max_length=50, verbose_name='描述')),
                ('error', models.CharField(default='', max_length=50, verbose_name='错误')),
                ('key', models.CharField(default='', max_length=128, verbose_name='opentsdb指标名')),
                ('multiplier', models.FloatField(default=1.0, verbose_name='自定义乘子')),
                ('unit', models.CharField(default='', max_length=12, verbose_name='单位')),
                ('host_id', models.IntegerField(verbose_name='对应主机id')),
                ('template_id', models.IntegerField(verbose_name='对应模板id')),
            ],
            options={
                'db_table': 'item',
                'verbose_name_plural': '监控项表',
            },
        ),
        migrations.CreateModel(
            name='MonitorSet',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=40, verbose_name='监控集名')),
            ],
            options={
                'db_table': 'set',
                'verbose_name_plural': '监控集表',
            },
        ),
        migrations.CreateModel(
            name='NetworkDevice',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('management_ip', models.CharField(blank=True, max_length=64, null=True, verbose_name='管理IP')),
                ('vlan_ip', models.CharField(blank=True, max_length=64, null=True, verbose_name='VlanIP')),
                ('intranet_ip', models.CharField(blank=True, max_length=128, null=True, verbose_name='内网IP')),
                ('sn', models.CharField(max_length=64, unique=True, verbose_name='SN号')),
                ('manufacture', models.CharField(blank=True, max_length=128, null=True, verbose_name='制造商')),
                ('model', models.CharField(blank=True, max_length=128, null=True, verbose_name='型号')),
                ('port_num', models.SmallIntegerField(blank=True, null=True, verbose_name='端口个数')),
                ('device_detail', models.CharField(blank=True, max_length=255, null=True, verbose_name='设置详细配置')),
                ('asset', models.OneToOneField(default='', editable=False, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.Asset')),
            ],
            options={
                'db_table': 'network_device',
                'verbose_name_plural': '网络设备',
            },
        ),
        migrations.CreateModel(
            name='NIC',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=128, verbose_name='网卡名称')),
                ('hwaddr', models.CharField(max_length=64, verbose_name='网卡mac地址')),
                ('netmask', models.CharField(max_length=64)),
                ('ipaddrs', models.CharField(max_length=256, verbose_name='ip地址')),
                ('up', models.BooleanField(default=False)),
            ],
            options={
                'db_table': 'nic',
                'verbose_name_plural': '网卡表',
            },
        ),
        migrations.CreateModel(
            name='Profile',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('telephone', models.CharField(blank=True, max_length=32, null=True, verbose_name='座机')),
                ('mobile', models.CharField(blank=True, max_length=32, null=True, verbose_name='手机')),
                ('desc', models.CharField(blank=True, max_length=255, null=True, verbose_name='描述')),
                ('user', models.OneToOneField(on_delete=django.db.models.deletion.CASCADE, related_name='profile', to=settings.AUTH_USER_MODEL)),
            ],
            options={
                'db_table': 'user_profile',
                'verbose_name_plural': '用户信息表',
            },
        ),
        migrations.CreateModel(
            name='Server',
            fields=[
                ('id', models.AutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=40, verbose_name='服务器主机名')),
                ('ip', models.CharField(max_length=20, verbose_name='IP地址')),
                ('status', models.IntegerField(choices=[(0, '宕机'), (1, '在线'), (2, '不监控')], default=0)),
                ('hostname', models.CharField(max_length=128, null=True, unique=True)),
                ('sn', models.CharField(db_index=True, default='', max_length=64, verbose_name='SN号')),
                ('manufacturer', models.CharField(blank=True, max_length=64, null=True, verbose_name='制造商')),
                ('model', models.CharField(blank=True, max_length=64, null=True, verbose_name='型号')),
                ('manage_ip', models.GenericIPAddressField(blank=True, null=True, verbose_name='管理IP')),
                ('os_platform', models.CharField(blank=True, max_length=16, null=True, verbose_name='系统')),
                ('os_version', models.CharField(blank=True, max_length=16, null=True, verbose_name='系统版本')),
                ('cpu_count', models.IntegerField(blank=True, null=True, verbose_name='CPU个数')),
                ('cpu_physical_count', models.IntegerField(blank=True, null=True, verbose_name='CPU物理个数')),
                ('cpu_model', models.CharField(blank=True, max_length=128, null=True, verbose_name='CPU型号')),
                ('create_at', models.DateTimeField(auto_now_add=True)),
                ('agent_address', models.CharField(default='', max_length=50, verbose_name='监控代理地址')),
                ('jmx_address', models.CharField(default='', max_length=50, verbose_name='jmx地址')),
                ('snmp_address', models.CharField(default='', max_length=50, verbose_name='snmp地址')),
                ('asset', models.OneToOneField(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.Asset')),
                ('data_collector', models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.DataCollector', verbose_name='数据收集器')),
            ],
            options={
                'db_table': 'server',
                'verbose_name_plural': '服务器表',
            },
        ),
        migrations.CreateModel(
            name='ServerGroup',
            fields=[
                ('id', models.AutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=40, verbose_name='服务器组名')),
                ('desc', models.CharField(blank=True, max_length=512, null=True, verbose_name='描述')),
                ('alarm_type', models.IntegerField(choices=[(0, '不接收'), (1, '普通报警'), (2, '严重报警'), (3, '所有报警')], default=0, verbose_name='告警类型')),
            ],
            options={
                'db_table': 'server_group',
                'verbose_name_plural': '服务器组表',
            },
        ),
        migrations.CreateModel(
            name='Tag',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=32, unique=True, verbose_name='标签')),
            ],
            options={
                'db_table': 'tag',
                'verbose_name_plural': '标签表',
            },
        ),
        migrations.CreateModel(
            name='Template',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(default='', max_length=40, verbose_name='模板名字')),
                ('monitor_set_id', models.ManyToManyField(db_table='r_template_set', to='monitor_web.MonitorSet')),
                ('server_group', models.ManyToManyField(db_table='r_template_server_group', to='monitor_web.ServerGroup')),
                ('server_id', models.ManyToManyField(db_table='r_template_server', to='monitor_web.Server')),
                ('template_id', models.ManyToManyField(db_table='r_template_template', to='monitor_web.Template')),
            ],
            options={
                'db_table': 'template',
                'verbose_name_plural': '模板表',
            },
        ),
        migrations.CreateModel(
            name='Trigger',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('expression', models.CharField(default='', max_length=256, verbose_name='触发器表达式')),
                ('template_id', models.ForeignKey(on_delete=django.db.models.deletion.CASCADE, related_name='t', to='monitor_web.Template', verbose_name='所属模板')),
            ],
            options={
                'db_table': 'trigger',
                'verbose_name_plural': '触发器表',
            },
        ),
        migrations.CreateModel(
            name='UserGroup',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('desc', models.CharField(blank=True, default='', max_length=512)),
                ('group', models.OneToOneField(on_delete=django.db.models.deletion.CASCADE, to='auth.Group')),
                ('profile', models.ManyToManyField(blank=True, db_table='r_usergroup_profile', to='monitor_web.Profile')),
                ('server_group', models.ManyToManyField(blank=True, db_table='r_user_group_server_group', to='monitor_web.ServerGroup')),
            ],
            options={
                'db_table': 'user_group',
                'verbose_name_plural': '用户组表',
            },
        ),
        migrations.AddField(
            model_name='server',
            name='server_group',
            field=models.ManyToManyField(db_table='r_server_server_group', to='monitor_web.ServerGroup'),
        ),
        migrations.AddField(
            model_name='nic',
            name='server_obj',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, related_name='nic', to='monitor_web.Server'),
        ),
        migrations.AddField(
            model_name='memory',
            name='server_obj',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, related_name='memory', to='monitor_web.Server'),
        ),
        migrations.AddField(
            model_name='event',
            name='monitor_item',
            field=models.ForeignKey(default='', editable=False, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.MonitorItem'),
        ),
        migrations.AddField(
            model_name='disk',
            name='server_obj',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, related_name='disk', to='monitor_web.Server'),
        ),
        migrations.AddField(
            model_name='businessunit',
            name='contact',
            field=models.ForeignKey(on_delete=django.db.models.deletion.CASCADE, related_name='c', to='monitor_web.Profile', verbose_name='业务联系人'),
        ),
        migrations.AddField(
            model_name='businessunit',
            name='manager',
            field=models.ForeignKey(on_delete=django.db.models.deletion.CASCADE, related_name='m', to='monitor_web.Profile', verbose_name='系统管理员'),
        ),
        migrations.AddField(
            model_name='assetrecord',
            name='creator',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.Profile'),
        ),
        migrations.AddField(
            model_name='asset',
            name='business_unit',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.BusinessUnit', verbose_name='属于的业务线'),
        ),
        migrations.AddField(
            model_name='asset',
            name='idc',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.IDC', verbose_name='IDC机房'),
        ),
        migrations.AddField(
            model_name='asset',
            name='tag',
            field=models.ManyToManyField(db_table='r_asset_tag', to='monitor_web.Tag'),
        ),
        migrations.AddField(
            model_name='alert',
            name='monitor_item',
            field=models.ForeignKey(default='', editable=False, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.MonitorItem'),
        ),
        migrations.AddField(
            model_name='alert',
            name='send_to',
            field=models.ForeignKey(default='', editable=False, on_delete=django.db.models.deletion.CASCADE, to='monitor_web.Profile'),
        ),
    ]
