#!/usr/bin/python
# -*- coding:utf-8 -*-
from hashlib import sha1
import hmac
import base64
import time


def gen_hmac(data):
    key = "MONITOR2.0"
    token = hmac.new(key=key, msg=data, digestmod=sha1).digest()
    token = base64.b64encode(token)
    return token


def gen_server_token(method, now_time, path):
    separator = "-"
    data = method + separator + str(now_time) + separator + path
    token = gen_hmac(data)
    return token


def get_auth_header(method, path):
    """
    生成请求头（服务端用不到）
    :param method:
    :param path:
    :return:
    """
    separator = "-"
    now_time = int(round(time.time() * 1000))
    data = method + separator + str(now_time) + separator + path
    token = gen_hmac(data)
    return {'Date': str(now_time), 'Authorization': token}


if __name__ == '__main__':
    gen_hmac('GET-1535959328990-/api/material/crawl/app/list')
    header = get_auth_header('POST', '/api/performad/creative/crawler/upload')
    print("Date:%s" % header['Date'])
    print("Authorization:%s" % header['Authorization'])
