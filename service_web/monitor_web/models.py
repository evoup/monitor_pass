from django.db import models

# Create your models here.


class ServerModels(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=40, null=False)
    ip = models.CharField(max_length=20, null=False)