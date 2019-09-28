#!/usr/bin/python
# -*- coding:utf8 -*-

# zabbix notification confirmation script

# python2.7 or above


import requests

import json

import os

import sys

# 部门id
Toparty = "2"

Touser = 'evoup'

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
Murl = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%s&fetch_child=true" % (token, 2)
x = requests.get(Murl, headers=headers)

# 消息发送函数

def msg(title, message):
    weixin_msg = {

        "touser" : "evoup",

        "msgtype": "textcard",

        "agentid": AgentID,

        "textcard": {

            "title": title,

            "description": message,

            "url": "www.evoupsight.com",

            "btntxt": "更多"

        }

    }

    print
    x = requests.post(Purl, json.dumps(weixin_msg), headers=headers)
    pass

if __name__ == '__main__':
    title = "告警"

    message = "12"

    msg(title, message)
