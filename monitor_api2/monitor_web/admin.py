from django.contrib import admin

# Register your models here.
from django.contrib.auth.models import Group

from monitor_web import models
from monitor_web.models import UserGroup
from django.contrib.auth.admin import GroupAdmin as BaseGroupAdmin

admin.site.register(models.UserProfile)
admin.site.register(models.Server)
admin.site.register(models.DataCollector)
admin.site.register(models.Event)
admin.site.register(models.UserGroup)
admin.site.register(models.BusinessUnit)
admin.site.register(models.IDC)
admin.site.register(models.Tag)
admin.site.register(models.Asset)

# ----------扩展django用户组开始-----------------
class GroupInline(admin.StackedInline):
    model = UserGroup
    can_delete = False
    verbose_name_plural = 'user groups'


class GroupAdmin(BaseGroupAdmin):
    inlines = (GroupInline, )

# Re-register GroupAdmin
admin.site.unregister(Group)
admin.site.register(Group, GroupAdmin)
# ----------扩展django用户组结束-----------------


