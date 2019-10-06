# -*- coding:utf-8 -*-
# Author:wd
from __future__ import absolute_import, unicode_literals
import os
from celery import Celery

# 设置django环境
os.environ.setdefault('DJANGO_SETTINGS_MODULE', os.environ.get('DJANGO_SETTINGS_MODULE'))

app = Celery('web')

#  使用CELERY_ 作为前缀，在settings中写配置
app.config_from_object('django.conf:settings', namespace='CELERY')

app.autodiscover_tasks()  # 发现任务文件每个app下的task.py

app.conf.result_expires = 300

app.conf.update(
    # 允许重试
    CELERY_ACKS_LATE=True,
    CELERY_ACCEPT_CONTENT=['pickle', 'json'],
    # 有些情况可以防止死锁
    CELERYD_FORCE_EXECV=True,
    # 设置并发worker数量
    CELERYD_CONCURRENCY=4,
    # 每个worker最多执行500个任务被销毁，可以防止内存泄漏
    CELERYD_MAX_TASKS_PER_CHILD=500,
    # 心跳
    BROKER_HEARTBEAT=0,
    # 超时时间
    CELERYD_TASK_TIME_LIMIT=12 * 30
)
