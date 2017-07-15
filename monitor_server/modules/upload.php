<?php
/*
  +----------------------------------------------------------------------+
  | Name:upload.php
  +----------------------------------------------------------------------+
  | Comment:处理被监控客户端的post操作的模块
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
$module_name = "upload";
$log = file_get_contents("php://input"); // 针对客户端使用curl对应获取POST的方式 
SaveSysLog("[$module_name][start get post data]",4);
$lines = explode("\n", $log); // 将原始post数据的每行赋值到lines数组 
if (sizeof($lines)===1 && empty($lines[0])) { // 空数据退出 
    SaveSysLog("[$module_name][get 0 byte of posted data,exit]",4);
    doExit();
}
$server_info_array = array();

foreach ($lines as $line_val) {
    if (empty($line_val)){ // 空行跳过 
        continue;
    }
    SaveSysLog("[line_val:$line_val]",4);
    $mon_t = explode(":", $line_val);
    $mon_t = array_shift($mon_t);
    switch ($mon_t) { // 获取每行数据，一行对应一类监控数据 
    case __MONITOR_TYPE_GENERIC:
        SaveSysLog("[$module_name][get server info]",5);
        $server_info = new clsGeneric($line_val);
        if (false!=$server_info->getAllInfo()) { // 获取全部上传信息 
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($server_info->m_server, __MONITOR_TYPE_GENERIC); // 刷新监控服务器存活名单for mdb
            $run_server_info[] = $server_info;
            include_once("genericSaveData.php");
        }
        continue;
    case __MONITOR_TYPE_MYSQL:
        SaveSysLog("[$module_name][get db info]",5);
        $mysql_info = new clsMysql($line_val);
        if (false!=$mysql_info->getAllInfo()) { // 获取全部上传信息 
            mdbSetAliveServerList($mysql_info->m_server, __MONITOR_TYPE_MYSQL); // 刷新监控服务器存活名单 for mdb
            $run_mysql_info[] = $mysql_info;
            include_once('mysqlSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_SERVING:
        SaveSysLog("[$module_name][get serving info]",5);
        $serving_info = new clsDelivering($line_val);
        if (false!=$serving_info->getAllInfo()) { // 获取全部上传信息 
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($serving_info->m_server, __MONITOR_TYPE_SERVING); // 刷新监控服务器存活名单 for mdb
            $run_serving_info[] = $serving_info;
            include_once('servingSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_DAEMON:
        SaveSysLog("[$module_name][get daemon info]",5);
        $daemon_info = new clsDaemon($line_val);
        if (false!=$daemon_info->getAllInfo()) { // 获取全部上传信息 
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($daemon_info->m_server, __MONITOR_TYPE_DAEMON); // 刷新监控服务器存活名单 for mdb
            $run_daemon_info[] = $daemon_info;
            include_once('daemonSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_REPORT:
        SaveSysLog("[$module_name][get report info]",5);
        $report_info = new clsReport($line_val);
        if (false!=$report_info->getAllInfo()) { // 获取全部信息 
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($report_info->m_server, __MONITOR_TYPE_REPORT); // 刷新监控服务器存活名单 for mdb
            $run_report_info[] = $report_info;
            include_once('reportSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_MADN:
        SaveSysLog("[$module_name][get madn info]",5);
        $madn_info = new clsMadn($line_val);
        if (false!=$madn_info->getAllInfo()) {
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($madn_info->m_server, __MONITOR_TYPE_MADN); // 刷新监控服务器存活名单 for mdb
            $run_madn_info[] = $madn_info;
            include_once('madnSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_HADOOP:
        SaveSysLog("[$module_name][get hadoop info]",5);
        $hadoop_info = new clsHadoop($line_val);
        if (false!=$hadoop_info->getAllInfo()) {
            SaveSysLog("[$module_name][getAllInfo done]",4);
            mdbSetAliveServerList($hadoop_info->m_server, __MONITOR_TYPE_HADOOP); // 刷新监控服务器存活名单 for mdb
            $run_hadoop_info[] = $hadoop_info;
            include_once('hadoopSaveData.php');
        }
        continue;
    case __MONITOR_TYPE_SECURITY:
        SaveSysLog("[$module_name][get security info]",5);
        $security_info = new clsSecurity($line_val);
        if (false!=$security_info->getAllInfo()) { // 获取全部信息 
        }
        continue;
    default:
        SaveSysLog("[$module_name][get type dismatch exit upload]",5);
        continue;
    }
}
?>
