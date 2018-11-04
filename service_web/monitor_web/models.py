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

    class Meta:
        verbose_name_plural = '用户信息表'

    def __str__(self):
        return self.name


class Server(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=40, null=False)
    ip = models.CharField(max_length=20, null=False)

    def __str__(self):
        return self.name
