from django.contrib import admin

# Register your models here.
from monitor_web import models

admin.site.register(models.UserProfile)
admin.site.register(models.Server)
admin.site.register(models.DataCollector)
admin.site.register(models.Event)
admin.site.register(models.UserGroup)
admin.site.register(models.BusinessUnit)
admin.site.register(models.IDC)
admin.site.register(models.Tag)
admin.site.register(models.Asset)


