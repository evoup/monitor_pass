from __future__ import absolute_import, unicode_literals

from celery import shared_task
from paramiko import SSHClient, AutoAddPolicy, RSAKey

from monitor_web import models


@shared_task
def exec_command(name, host, port, username, command):
    """
    远程执行命令
    :param name: 执行命令的主机名
    :param host: 主机名或ip
    :param port: ssh端口
    :param username:  系统用户名
    :param command: 命令
    :return:
    """
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
    return {'out': bytes.decode(), 'name': name}


@shared_task
def file_dispatch(name, host, port, username, src_file, dest_dir):
    config = models.GeneralConfig.objects.filter(id=1).get()
    pkey = config.ssh_private_key_dir
    key = RSAKey.from_private_key_file(pkey)
    ssh = SSHClient()
    ssh.load_system_host_keys()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    ssh.connect(hostname=host, port=port, username=username, pkey=key, compress=True)
    sftp = ssh.open_sftp()
    sftp.put(src_file, '/tmp/new/')
    return {'out': '分发成功', 'name': name}


@shared_task
def mul(x, y):
    return x * y
