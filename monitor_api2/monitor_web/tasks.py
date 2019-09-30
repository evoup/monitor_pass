from __future__ import absolute_import, unicode_literals

from celery import shared_task
from paramiko import SSHClient, AutoAddPolicy


@shared_task
def add(host, port, username, command):
    if str(command).find('rm ') >= 0 or str(command).find('rm -rf') >= 0:
        return '该命令被禁用!'
    ssh = SSHClient()
    ssh.load_system_host_keys()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    ssh.connect(hostname=host, port=port, username=username, password='hellomadhouse', compress=True)
    stdin, stdout, stderr = ssh.exec_command(command)
    return {'out': str(stdout.read())}


@shared_task
def mul(x, y):
    return x * y
