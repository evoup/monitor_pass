# -*- coding:utf-8 -*-


import os
from celery import Celery
from django.conf import settings

# os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'web.deploy_settings')
os.environ.setdefault('DJANGO_SETTINGS_MODULE', os.environ.get('DJANGO_SETTINGS_MODULE'))
app = Celery()
app.conf.update(settings.CELERY)
