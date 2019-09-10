# Generated by Django 2.1.9 on 2019-09-10 16:32

from django.db import migrations, models
import django_unixdatetimefield.fields


class Migration(migrations.Migration):

    dependencies = [
        ('monitor_web', '0002_remove_event_monitor_item'),
    ]

    operations = [
        migrations.AddField(
            model_name='event',
            name='acknowledged',
            field=models.BooleanField(default=False, null=True),
        ),
        migrations.AlterField(
            model_name='event',
            name='acknowledge',
            field=models.CharField(default='', max_length=200, null=True, verbose_name='确认文字'),
        ),
        migrations.AlterField(
            model_name='event',
            name='time',
            field=django_unixdatetimefield.fields.UnixDateTimeField(verbose_name='发生时间'),
        ),
    ]