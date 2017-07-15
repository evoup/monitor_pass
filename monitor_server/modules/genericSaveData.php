<?php
/*
  +----------------------------------------------------------------------+
  | Name:genericSaveData.php
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
$sub_module_name="genericSaveData";
foreach($run_server_info as $generic_info){
    mdbUpdateSrvList($generic_info->m_server, __MONITOR_TYPE_GENERIC); //更新全局服务器列表 
    SaveSysLog("[$sub_module_name][mdb_set][montype:".__MONITOR_TYPE_GENERIC."][server:".$generic_info->m_server."][message:".($generic_info->m_client_message)."]",3);
    /*{{{ 存服务器上传时间戳 for scan 
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_LASTTIME, $generic_info->m_server), time());
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp ok!]",4);
    }
    /* }}} */
    /*{{{ 存客户端监控信息 for scan TODO 这里直接扫描报警 
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_GENERIC, $generic_info->m_server),
        $generic_info->m_client_message);
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg ok!]",4);
    }
    /* }}} */
    /* {{{ 存主机状态(在线状态、上次上传时间) for display 
     */
    mdbSaveHostStatus($generic_info->m_server);
    /* }}} */

    /* {{{ 存即时信息表 
     */
    saveMonitorInfo($generic_info, __MONITOR_TYPE_GENERIC);
    /* }}} */
    /* {{{ 存历史信息表
     */
    mdbSaveMonitorHistoryInfo($generic_info, __MONITOR_TYPE_GENERIC);
    /* }}} */
}
?>
