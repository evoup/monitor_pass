from __future__ import absolute_import, unicode_literals

from celery import shared_task
from paramiko import SSHClient, AutoAddPolicy, RSAKey

from monitor_web import models


@shared_task
def exec_command(host, port, username, command):
    if str(command).find('rm ') >= 0 or str(command).find('rm -rf') >= 0:
        return {'out': '该命令被禁用!'}
    config = models.GeneralConfig.objects.filter(id=1).get()
    pkey = config.ssh_private_key_dir
    # pkey = '/home/evoup/.ssh/id_rsa'
    key = RSAKey.from_private_key_file(pkey)
    ssh = SSHClient()
    ssh.load_system_host_keys()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    # ssh.connect(hostname=host, port=port, username=username, password='xxx', compress=True)
    ssh.connect(hostname=host, port=port, username=username, pkey=key, compress=True)
    stdin, stdout, stderr = ssh.exec_command(command)
    bytes = stdout.read()
    return {'out': bytes.decode()}


@shared_task
def mul(x, y):
    return x * y
