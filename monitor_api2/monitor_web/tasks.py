from __future__ import absolute_import, unicode_literals

from celery import shared_task
from paramiko import SSHClient, AutoAddPolicy, RSAKey


@shared_task
def add(host, port, username, command):
    if str(command).find('rm ') >= 0 or str(command).find('rm -rf') >= 0:
        return {'out': '该命令被禁用!'}
    pkey = '/home/evoup/.ssh/id_rsa'
    key = RSAKey.from_private_key_file(pkey)
    ssh = SSHClient()
    ssh.load_system_host_keys()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    # ssh.connect(hostname=host, port=port, username=username, password='xxx', compress=True)
    ssh.connect(hostname=host, port=port, username=username, pkey=key, compress=True)
    stdin, stdout, stderr = ssh.exec_command(command)
    return {'out': str(stdout.read())}


@shared_task
def mul(x, y):
    return x * y