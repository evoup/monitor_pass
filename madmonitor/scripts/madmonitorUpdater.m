<?php
/*
  +----------------------------------------------------------------------+
  | Name:
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Compile: pcc -v --static -c /etc/pcc_mysql.conf madmonitorUpdater.m -o madmonitorUpdater
  +----------------------------------------------------------------------+
  | Create:2012年 2月 1日 星期三 18时27分10秒 CST
  +----------------------------------------------------------------------+
  | Last Modified:2012-02-01 18:27:06
  +----------------------------------------------------------------------+
 */
$shell='cd /tmp';
$shell.=" && fetch http://211.136.105.221/madmonitor/madmonitor.tar.gz";
$shell.=" && fetch http://211.136.105.221/madmonitor/madmonitor.tar.gz.md5";
$shell.=" && tar xzf /tmp/madmonitor.tar.gz";
$res=shell_exec($shell);
$monitorPid=file_get_contents('/services/monitor_deal/run/madmonitor.pid');
$shell="kill -9 {$monitorPid}";
$res=shell_exec($shell);
$shell="cp /tmp/madmonitor /services/monitor_deal/madmonitor";
$res=shell_exec($shell);

?>
