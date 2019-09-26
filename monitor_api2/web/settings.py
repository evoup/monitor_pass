"""
Django settings for web project.

Generated by 'django-admin startproject' using Django 2.1.2.

For more information on this file, see
https://docs.djangoproject.com/en/2.1/topics/settings/

For the full list of settings and their values, see
https://docs.djangoproject.com/en/2.1/ref/settings/
"""
import datetime
import os

# Build paths inside the project like this: os.path.join(BASE_DIR, ...)
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/2.1/howto/deployment/checklist/

# SECURITY WARNING: keep the secret key used in production secret!
SECRET_KEY = 'l110wk$h&cq!qk-p7abu1xmoyk9f4azou5)74!vdp(ht9%c(14'

# SECURITY WARNING: don't run with debug turned on in production!
DEBUG = True

ALLOWED_HOSTS = ['*']

# Application definition

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'monitor_web.apps.MonitorWebConfig',
    'rest_framework',
    'corsheaders',
    'django_extensions'
]

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'corsheaders.middleware.CorsMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'web.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [os.path.join(BASE_DIR, 'templates')]
        ,
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]

WSGI_APPLICATION = 'web.wsgi.application'

# Database
# https://docs.djangoproject.com/en/2.1/ref/settings/#databases

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'monitor',
        'USER': 'dba',
        'PASSWORD': '123456',
        'HOST': 'monitor-db',
        'PORT': '3307'
    }
}

# Password validation
# https://docs.djangoproject.com/en/2.1/ref/settings/#auth-password-validators

AUTH_PASSWORD_VALIDATORS = [
    {
        'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator',
    },
]

# Internationalization
# https://docs.djangoproject.com/en/2.1/topics/i18n/

LANGUAGE_CODE = 'en-us'

TIME_ZONE = 'UTC'

USE_I18N = True

USE_L10N = True

USE_TZ = False

# Static files (CSS, JavaScript, Images)
# https://docs.djangoproject.com/en/2.1/howto/static-files/

STATIC_URL = '/static/'

REST_FRAMEWORK = {
    'DEFAULT_PERMISSION_CLASSES': (
        'rest_framework.permissions.IsAuthenticated',
    ),
    'DEFAULT_AUTHENTICATION_CLASSES': (
        'rest_framework_jwt.authentication.JSONWebTokenAuthentication',
        # 'rest_framework.authentication.SessionAuthentication',
        # 'rest_framework.authentication.BasicAuthentication',
    ),
    'DEFAULT_PAGINATION_CLASS': 'rest_framework.pagination.PageNumberPagination',
    'PAGE_SIZE': 5
}

JWT_AUTH = {
    'JWT_ENCODE_HANDLER':
        'rest_framework_jwt.utils.jwt_encode_handler',

    'JWT_DECODE_HANDLER':
        'rest_framework_jwt.utils.jwt_decode_handler',

    'JWT_PAYLOAD_HANDLER':
        'rest_framework_jwt.utils.jwt_payload_handler',

    'JWT_PAYLOAD_GET_USER_ID_HANDLER':
        'rest_framework_jwt.utils.jwt_get_user_id_from_payload_handler',

    'JWT_RESPONSE_PAYLOAD_HANDLER':
        'rest_framework_jwt.utils.jwt_response_payload_handler',

    'JWT_SECRET_KEY': SECRET_KEY,
    'JWT_GET_USER_SECRET_KEY': None,
    'JWT_PUBLIC_KEY': None,
    'JWT_PRIVATE_KEY': None,
    'JWT_ALGORITHM': 'HS256',
    'JWT_VERIFY': True,
    'JWT_VERIFY_EXPIRATION': True,
    'JWT_LEEWAY': 0,
    'JWT_EXPIRATION_DELTA': datetime.timedelta(seconds=15552000),
    'JWT_AUDIENCE': None,
    'JWT_ISSUER': None,

    'JWT_ALLOW_REFRESH': True,
    'JWT_REFRESH_EXPIRATION_DELTA': datetime.timedelta(days=7),

    'JWT_AUTH_HEADER_PREFIX': 'JWT',
    'JWT_AUTH_COOKIE': None,

}

CORS_ORIGIN_ALLOW_ALL = True
# CORS_ALLOW_HEADERS = default_headers + (
#     'x-token',
#     'authorization'
# )

STATIC_ROOT = '/services'
LOGGING = {
    'version': 1,
    'disable_existing_loggers': True,
    'formatters': {
        'standard': {
            'format': '%(asctime)s [%(threadName)s:%(thread)d] [%(name)s:%(lineno)d] [%(levelname)s]- %(message)s'
        },
    },
    'filters': {
    },
    'handlers': {
        'mail_admins': {
            'level': 'ERROR',
            'class': 'django.utils.log.AdminEmailHandler',
            'include_html': True,
        },
        'default': {
            'level': 'DEBUG',
            'class': 'logging.handlers.RotatingFileHandler',
            'filename': os.path.join(STATIC_ROOT + '/logs/', 'all.log'),
            'maxBytes': 1024 * 1024 * 5,  # 5 MB
            'backupCount': 5,
            'formatter': 'standard',
        },
        'console': {
            'level': 'DEBUG',
            'class': 'logging.StreamHandler',
            'formatter': 'standard'
        },
        'request_handler': {
            'level': 'DEBUG',
            'class': 'logging.handlers.RotatingFileHandler',
            'filename': os.path.join(STATIC_ROOT + '/logs/', 'script.log'),
            'maxBytes': 1024 * 1024 * 5,  # 5 MB
            'backupCount': 5,
            'formatter': 'standard',
        },
        'scprits_handler': {
            'level': 'DEBUG',
            'class': 'logging.handlers.RotatingFileHandler',
            'filename': os.path.join(STATIC_ROOT + '/logs/', 'script.log'),
            'maxBytes': 1024 * 1024 * 5,  # 5 MB
            'backupCount': 5,
            'formatter': 'standard',
        },
    },
    'loggers': {
        'django': {
            'handlers': ['default', 'console'],
            'level': 'DEBUG',
            'propagate': False
        },
        'monitor_web.app': {
            'handlers': ['default', 'console'],
            'level': 'DEBUG',
            'propagate': True
        },
        'django.request': {
            'handlers': ['request_handler'],
            'level': 'DEBUG',
            'propagate': False
        },
        'scripts': {  # 脚本专用日志
            'handlers': ['scprits_handler'],
            'level': 'INFO',
            'propagate': False
        },
    }
}

# 这些权限不需要，其中包含用户和用户组的，用默认的
ABANDONED_PERMISSIONS = ['add_logentry', 'change_logentry', 'delete_logentry', 'view_logentry', 'add_permission',
                         'change_permission', 'delete_permission', 'view_permission', 'add_contenttype',
                         'change_contenttype', 'delete_contenttype', 'view_contenttype', 'add_session',
                         'change_session', 'delete_session', 'view_session', 'add_profile', 'change_profile',
                         'delete_profile', 'view_profile', 'add_usergroup', 'change_usergroup', 'delete_usergroup',
                         'view_usergroup', 'add_relationuseritem', 'change_relationuseritem', 'delete_relationuseritem',
                         'view_relationuseritem', 'add_function', 'change_function', 'delete_function', 'view_function',
                         'add_grafanadashboard', 'change_grafanadashboard', 'delete_grafanadashboard',
                         'view_grafanadashboard', 'add_dashboard', 'change_dashboard', 'delete_dashboard',
                         'view_dashboard', 'add_operationcommand', 'change_operationcommand', 'delete_operationcommand',
                         'view_operationcommand', 'add_operationmessage', 'change_operationmessage',
                         'delete_operationmessage', 'view_operationmessage', 'add_operationstep',
                         'change_operationstep', 'delete_operationstep', 'view_operationstep', 'add_operationcondition',
                         'change_operationcondition', 'view_operationcondition', 'delete_operationcondition',
                         'add_relationoperationcommanduser', 'change_relationoperationcommanduser',
                         'delete_relationoperationcommanduser', 'view_relationoperationcommanduser',
                         'add_relationoperationmessageuser', 'change_relationoperationmessageuser',
                         'delete_relationoperationmessageuser', 'view_relationoperationmessageuser']

# 权限管理(两个app的view函数不能重复)
PERMISSIONS = {
    # 用户组
    "add_group": "用户组添加",
    "change_group": "用户组更新",
    "delete_group": "用户组删除",
    "view_group": "用户组查看",

    # 用户组
    "add_user": "用户添加",
    "change_user": "用户更新",
    "delete_user": "用户删除",
    "view_user": "用户查看",

    # 监控项
    "add_monitoritem": "监控项添加",
    "change_monitoritem": "监控项更新",
    "delete_monitoritem": "监控项删除",
    "view_monitoritem": "监控项查看",

    # 机房
    "add_idc": "机房添加",
    "change_idc": "机房更新",
    "delete_idc": "机房删除",
    "view_idc": "机房查看",

    # 服务器
    "add_server": "服务器添加",
    "change_server": "服务器修改",
    "delete_server": "服务器删除",
    "view_server": "服务器查看",

    # 标签
    "add_tag": "标签添加",
    "change_tag": "标签修改",
    "delete_tag": "标签删除",
    "view_tag": "标签查看",

    # 监控事件
    "add_event": "监控事件添加",
    "change_event": "监控事件修改",
    "delete_event": "监控事件删除",
    "view_event": "监控事件查看",

    # 业务线
    "add_businessunit": "业务线添加",
    "change_businessunit": "业务线修改",
    "delete_businessunit": "业务线删除",
    "view_businessunit": "业务线查看",

    # 资产错误记录
    "add_asseterrorlog": "资产错误记录添加",
    "change_asseterrorlog": "资产错误记录修改",
    "delete_asseterrorlog": "资产错误记录删除",
    "view_asseterrorlog": "资产错误记录查看",

    # 内存
    "add_memory": "内存添加",
    "change_memory": "内存修改",
    "delete_memory": "内存删除",
    "view_memory": "内存查看",

    # 网络接口
    "add_nic": "网络接口添加",
    "change_nic": "网络接口修改",
    "delete_nic": "网络接口删除",
    "view_nic": "网络接口查看",

    # 数据收集器
    "add_datacollector": "数据收集器添加",
    "change_datacollector": "数据收集器修改",
    "delete_datacollector": "数据收集器删除",
    "view_datacollector": "数据收集器查看",

    # 网络设备
    "add_networkdevice": "网络设备添加",
    "change_networkdevice": "网络设备修改",
    "delete_networkdevice": "网路设备删除",
    "view_networkdevice": "网络设备查看",

    # 资产记录
    "add_assetrecord": "资产记录添加",
    "change_assetrecord": "资产记录修改",
    "delete_assetrecord": "资产记录删除",
    "view_assetrecord": "资产记录查看",

    # 资产
    "add_asset": "资产添加",
    "change_asset": "资产修改",
    "delete_asset": "资产删除",
    "view_asset": "资产查看",

    # 监控集
    "add_monitorset": "监控集添加",
    "change_monitorset": "监控集修改",
    "delete_monitorset": "监控集删除",
    "view_monitorset": "监控集查看",

    # 磁盘
    "add_disk": "磁盘添加",
    "change_disk": "磁盘修改",
    "delete_disk": "磁盘删除",
    "view_disk": "磁盘查看",

    # 模板
    "add_template": "模板添加",
    "change_template": "模板修改",
    "delete_template": "模板删除",
    "view_template": "模板查看",

    # 告警
    "add_alert": "告警添加",
    "change_alert": "告警修改",
    "delete_alert": "告警删除",
    "view_alert": "告警查看",

    # 触发器
    "add_trigger": "触发器添加",
    "change_trigger": "触发器修改",
    "delete_trigger": "触发器删除",
    "view_trigger": "触发器查看",

    # 服务器组
    "add_servergroup": "服务器组添加",
    "change_servergroup": "服务器组修改",
    "delete_servergroup": "服务器组删除",
    "view_servergroup": "服务器组查看",

    # 图表
    "add_diagram": "图表添加",
    "change_diagram": "图表修改",
    "delete_diagram": "图表删除",
    "view_diagram": "图表查看",

    # 图表项
    "add_diagramitem": "图表项添加",
    "change_diagramitem": "图表项修改",
    "delete_diagramitem": "图表项删除",
    "view_diagramitem": "图表项查看",

    # 常规设置
    "add_generalconfig": "常规设置添加",
    "change_generalconfig": "常规设置修改",
    "delete_generalconfig": "常规设置删除",
    "view_generalconfig": "常规设置查看",

    # 告警通知方式设置
    "add_notificationmode": "告警通知方式设置添加",
    "change_notificationmode": "告警通知方式设置修改",
    "delete_notificationmode": "告警通知方式设置删除",
    "view_notificationmode": "告警通知方式设置查看",

    # 操作
    "add_operation": "操作添加",
    "change_operation": "操作修改",
    "delete_operation": "操作删除",
    "view_operation": "操作查看",
}

# grafana单位映射(数据库中的单位<=>grafana的单位)
GRAFANA_UNIT_MAP = {
    None: "short",
    "": "short",
    "B": "decbytes",
    "%": "percent"
}
