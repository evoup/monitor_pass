from django.contrib import admin

# Register your models here.
from monitor_web import models

admin.site.register(models.UserProfile)
admin.site.register(models.Server)
