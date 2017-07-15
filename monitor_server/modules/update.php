<?php
/*
  +----------------------------------------------------------------------+
  | Name: update.php
  +----------------------------------------------------------------------+
  | Comment: 处理客户端的更新
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2012年 1月31日 星期二 17时35分27秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-02-01 14:22:44
  +----------------------------------------------------------------------+
 */
$module_name = 'update';
// 告诉客户端最新客户端版本号和客户端下载地址 
// TODO 改成可以在界面上设置的变量,要注意下载的安全，需要上传自身程序的md5检查合法性
echo "0.10.1.5135#http://211.136.105.221/madmonitor/madmonitor.tar.gz"; 
// 获取客户端上传了版本和配置文件信息
list($host,$clientVer,$confContent)=array($_POST['host'],$_POST['clientVer'],$_POST['confContent']);
SaveSysLog("[$module_name][host:$host]",4);
if (!in_array($host, $monitored_servers[strval(__MONITOR_TYPE_GENERIC)])) {
    SaveSysLog("[$module_name][host:$host is not in monitored list!]",3);
    return;
} else {
    SaveSysLog("[$module_name][host:$host is in monitored list.]",4);
}
$confContent=base64_decode($confContent);
$confContent=serialize($confContent);
SaveSysLog("[$module_name][clientVer:$clientVer]",4);
SaveSysLog("[$module_name][confContent:$confContent]",4);
// 存版本号以及配置文件
$clientVerConfString = $clientVer."|".$confContent;
$res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_CONFIG_CLIENT, $host, $clientVerConfString);
if ($res) {
    SaveSysLog("[$module_name][$host:saved ver & conf.]",4);
} else {
    SaveSysLog("[$module_name][$host:failed to save ver & conf!]",4);
}
?>
