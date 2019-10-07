from __future__ import absolute_import, unicode_literals

import json

import requests
from celery import shared_task
from paramiko import SSHClient, AutoAddPolicy, RSAKey, SFTPClient, Transport

from monitor_web import models
from web.celery import app
from django.core.cache import cache


@app.task(bind=True, name='exec_command')
def exec_command(self, name, host, port, username, command):
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
    key = RSAKey.from_private_key_file(pkey)
    ssh = SSHClient()
    ssh.load_system_host_keys()
    ssh.set_missing_host_key_policy(AutoAddPolicy())
    ssh.connect(hostname=host, port=port, username=username, pkey=key, compress=True)
    stdin, stdout, stderr = ssh.exec_command(command)
    bytes = stdout.read()
    result_command = bytes.decode()
    cache.set("task_id:%s" % exec_command.request.id, json.dumps({'out': result_command, 'name': name}), timeout=300)
    return {'out': result_command, 'name': name}


@shared_task
def file_dispatch(name, host, port, username, src_file, dest_dir):
    """
    文件传输
    """
    config = models.GeneralConfig.objects.filter(id=1).get()
    pkey = config.ssh_private_key_dir
    key = RSAKey.from_private_key_file(pkey)
    t = Transport(host, port)
    t.connect(username=username, pkey=key)
    sftp = SFTPClient.from_transport(t)
    sftp.put(src_file, dest_dir)
    cache.set("task_id:%s" % file_dispatch.request.id, json.dumps({'out': '分发成功', 'name': name}), timeout=300)
    return {'out': '分发成功', 'name': name}


@shared_task
def add(x, y):
    return x + y


@app.task(bind=True, name='tasks.send_wechat_message')
def send_wechat_message(self, server_name):
    # 部门id
    Toparty = "2"

    Touser = 'YinJia'

    # 应用id
    AgentID = 1000002

    # 修改为企业CropID和Secret

    CropID = 'ww0adfed0b986e2142'

    Secret = 'oIithrVguY_ax6tywSjiqS7L8P6piHEc_AjoUggmKvM'

    # 获取Token

    Gtoken = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" + CropID + "&corpsecret=" + Secret

    headers = {'Content-Type': 'application/json'}

    json_data = json.loads(requests.get(Gtoken).content.decode())

    token = json_data["access_token"]

    # 消息发送接口

    Purl = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=" + token
    title = '告警'
    message = '%s发生问题事件' % server_name
    weixin_msg = {

        "touser": Touser,

        "msgtype": "textcard",

        "agentid": AgentID,

        "textcard": {

            "title": title,

            "description": message,

            "url": "www.evoupsight.com",

            "btntxt": "更多"

        }

    }
    res = requests.post(Purl, json.dumps(weixin_msg), headers=headers)
    return {'out': res.status_code, 'name': server_name}
