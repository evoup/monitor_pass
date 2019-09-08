from django.contrib import admin

from django.contrib.auth.models import Group

from monitor_web import models
from monitor_web.models import UserGroup
from django.contrib.auth.admin import GroupAdmin as BaseGroupAdmin

admin.site.register(models.Profile)
admin.site.register(models.Server)
admin.site.register(models.ServerGroup)
admin.site.register(models.DataCollector)
admin.site.register(models.Event)
admin.site.register(models.UserGroup)
admin.site.register(models.BusinessUnit)
admin.site.register(models.IDC)
admin.site.register(models.Tag)
admin.site.register(models.Asset)
admin.site.register(models.Alert)
admin.site.register(models.Template)
admin.site.register(models.MonitorItem)
admin.site.register(models.MonitorSet)
admin.site.register(models.Memory)
admin.site.register(models.Disk)
admin.site.register(models.NetworkDevice)
admin.site.register(models.NIC)
admin.site.register(models.Diagram)
admin.site.register(models.DiagramItem)
admin.site.register(models.GeneralConfig)
admin.site.register(models.GrafanaDashboard)
admin.site.register(models.DashBoard)
admin.site.register(models.NotificationMode)


# ----------扩展django用户组开始-----------------
class GroupInline(admin.StackedInline):
    model = UserGroup
    can_delete = False
    verbose_name_plural = 'user groups'


class GroupAdmin(BaseGroupAdmin):
    inlines = (GroupInline,)


# Re-register GroupAdmin
admin.site.unregister(Group)
admin.site.register(Group, GroupAdmin)
# ----------扩展django用户组结束-----------------
