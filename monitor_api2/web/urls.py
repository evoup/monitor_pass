"""web URL Configuration

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/2.1/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  path('', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  path('', Home.as_view(), name='home')
Including another URLconf
    1. Import the include() function: from django.urls import include, path
    2. Add a URL to urlpatterns:  path('blog/', include('blog.urls'))
"""
from django.conf.urls import url
from django.contrib import admin
from django.urls import path, include
from rest_framework import routers
from rest_framework_jwt.views import obtain_jwt_token

from monitor_web import views

router = routers.DefaultRouter()
router.register(r'users', views.UserViewSet)

url_prefix = 'mmsapi2.0'
urlpatterns = [
    path('admin/', admin.site.urls),
    url(r'^$', views.index),
    url(r'^%s/user/logout$' % url_prefix, views.logout), # fbv不能写前缀，目前直接这么写
    url(r'^', include(router.urls)),
    # 有前缀和没有兼容
    url(r'(^%s/)|(^)' % url_prefix, include([
        url(r'^servers/?$', views.ServerList.as_view()),
        # 登录时post获取token，delete注销
        url(r'^login/$', views.Login.as_view()),
        # 查询是否过期，过期
        url(r'^login/status/$', views.LoginStatus.as_view()),
        url(r'^user/info$', views.UserInfo.as_view()),
        # 添加服务器
        url(r'^server/info$', views.ServerInfo.as_view()),
        # 服务器列表
        url(r'^server/list$', views.ServerList.as_view())
    ]))
]
