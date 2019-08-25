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

from monitor_web.views import view, template_view, item_view, trigger_view, idc_view, data_collector_view, asset_view, \
    diagram_view, general_config_view
from monitor_web.views import user_view
from monitor_web.views import server_view

router = routers.DefaultRouter()
router.register(r'users', user_view.UserViewSet)

url_prefix = 'mmsapi2.0'
urlpatterns = [
    path('admin/', admin.site.urls),
    url(r'^$', view.index),
    url(r'^%s/user/logout$' % url_prefix, user_view.logout),  # fbv不能写前缀，目前直接这么写
    # 这个加好后drf的api管理后台会出现登录按钮，但是现在写在jwt里没用了
    # url('api-auth/', include('rest_framework.urls')),
    url(r'^', include(router.urls)),
    # 有前缀和没有兼容
    url(r'(^%s/)|(^)' % url_prefix, include([
        url(r'^servers/?$', server_view.ServerList.as_view()),
        # 登录时post获取token，delete注销
        url(r'^login/$', user_view.Login.as_view()),
        # 查询是否过期，过期
        url(r'^login/status/$', user_view.LoginStatus.as_view()),
        # 读取/创建/修改用户
        url(r'^user/info$', user_view.UserInfo.as_view()),
        # 返回单个用户角色数据
        url(r'^user_role/info$', user_view.UserRoleInfo.as_view()),
        # 用户列表
        url(r'^user/list$', user_view.UserList.as_view()),
        # 添加/删除用户组
        url(r'^user_group/info$', user_view.UserGroupInfo.as_view()),
        # 用户组列表
        url(r'^user_group/list$', user_view.UserGroupList.as_view()),
        # 所有权限列表
        url(r'^user_perm/list$', user_view.UserPerm.as_view()),
        # 读取/创建服务器
        url(r'^server/info$', server_view.ServerInfo.as_view()),
        # 服务器列表
        url(r'^server/list$', server_view.ServerList.as_view()),
        # 添加数据收集器
        url(r'^data_collector/info$', data_collector_view.DataCollectorInfo.as_view()),
        # 数据收集器列表
        url(r'^data_collector/list$', data_collector_view.DataCollectorList.as_view()),
        # 添加机房
        url(r'^idc/info$', idc_view.IdcInfo.as_view()),
        # 机房列表
        url(r'^idc/list$', idc_view.IdcList.as_view()),
        # 服务组列表
        url(r'^server_group/list$', server_view.ServerGroupList.as_view()),
        # 服务器组删除
        url(r'^server_group/info$', server_view.ServerGroupInfo.as_view()),
        # 模板列表
        url(r'^template/list$', template_view.TemplateList.as_view()),
        # 返回单个模板/修改/添加/删除模板
        url(r'^template/info', template_view.TemplateInfo.as_view()),
        # 监控项列表
        url(r'^item/list$', item_view.ItemList.as_view()),
        # 修改监控项状态
        url(r'^item/status$', item_view.ItemStatus.as_view()),
        # 读取监控项
        url(r'^item/info$', item_view.ItemInfo.as_view()),
        # 触发器列表
        url(r'^trigger/list$', trigger_view.TriggerList.as_view()),
        # 读取触发器
        url(r'^trigger/info$', trigger_view.TriggerInfo.as_view()),
        # 资产列表
        url(r'^asset/list$', asset_view.AssetList.as_view()),
        # 资产变更记录列表
        url(r'^asset_record/list$', asset_view.AssetRecordList.as_view()),
        # 图表列表
        url(r'^diagram/list$', diagram_view.DiagramList.as_view()),
        # 读取图表
        url(r'^diagram/info', diagram_view.DiagramInfo.as_view()),
        # 读取常规设置
        url(r'^general_config/info', general_config_view.GeneralConfig.as_view()),
    ]))
]
