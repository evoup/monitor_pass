#!/bin/sh
. ../include/cookie.sh
curl -v 'http://localhost:8004/mmsapi1.0/create/host/@self' -H "Cookie: __CO_MMSUNAME=${__CO_MMSUNAME}; __CO_MMSUID=${__CO_MMSUID}" -H 'Origin: http://localhost:8004' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Accept: */*' -H 'Referer: http://localhost:8004/monitor_ui/client/configure/add_edit_usergroup.html?version=1.0' -H 'X-Requested-With: XMLHttpRequest' -H 'Connection: keep-alive' \
    --data 'hostname=test&agent_interface=&snmp_interface=&jmx_interface=&data_collector=&template=&monitored=' --compressed
