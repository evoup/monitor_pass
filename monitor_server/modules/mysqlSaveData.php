<?php
/*
  +----------------------------------------------------------------------+
  | Name:mysqlSaveData.php
  +----------------------------------------------------------------------+
  | Comment:保存客户端RAWPOST上传的监控数据
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
$sub_module_name="mysqlSaveData";
foreach($run_mysql_info as $mysql_info){
    mdbUpdateSrvList($mysql_info->m_server, __MONITOR_TYPE_MYSQL); //更新全局服务器列表 
    SaveSysLog("[$sub_module_name][mdb_set][montype:".$mysql_info->m_server."][server:".($mysql_info->m_client_message)."]",3);
    /*{{{ 存服务器上传时间戳 for scan
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_LASTTIME, $mysql_info->m_server), time());
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp ok!]",4);
    }
    /* }}} */
    /*{{{ 存客户端监控信息 for scan 
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_MYSQL, $mysql_info->m_server),
        $mysql_info->m_client_message);
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg ok!]",4);
    }
    /* }}} */
    /* {{{ 存主机状态(在线状态、上次上传时间) for display 
     */
    mdbSaveHostStatus($mysql_info->m_server);
    /* }}} */

    /* {{{ 存即时信息表 
     */
    saveMonitorInfo($mysql_info, __MONITOR_TYPE_MYSQL);
    /* }}} */
    /* {{{ 存历史信息表
     */
    mdbSaveMonitorHistoryInfo($mysql_info, __MONITOR_TYPE_MYSQL);
    /* }}} */
}
?>
