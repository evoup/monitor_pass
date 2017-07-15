<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_daemon.php
  +----------------------------------------------------------------------+
  | Comment:检查daemon监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-01-10 10:25:15
  +----------------------------------------------------------------------+
 */
$module_name="scan_daemon";

//把daemon类中需要存记录的成员变量换成标准名字 
$dict=array(
    'webserver_status' => 'm_webserver_status', 
    'daemon_status'    => 'm_daemon_status',
    'login_status'     => 'm_login_status',
    'adserv_status'    => 'm_adserv_status',
    'error_log_status' => 'm_error_log_status'
);

/*{{{对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key=sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_DAEMON, $serv_node);
    SaveSysLog("[$module_name][row_key:$row_key]",3);
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $mon_daemon_info=$res[__MDB_COL_EVENT]->value; //取得指定类型指定服务器的监控信息 
        } catch (Exception $e) {
        }
        $try++;
    }
    $row_key=sprintf(__KEY_LASTTIME, $serv_node );
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $last_alive_time=$res[__MDB_COL_EVENT]->value; //取得上次获取到客户端信息的时间
        } catch (Exception $e) {
        }
        $try++;
    }
    if (!empty($last_alive_time) && time()-$last_alive_time<=$down_over_time ) { 
        $daemon_info=new clsDaemon($mon_daemon_info); //获取一个daemon对象
        if (false===$daemon_info->getAllInfo()) { //包含了全部监控指标的当前值  
            if (empty($mon_daemon_info)) { //客户端没有传此类监控信息 
                SaveSysLog("[$module_name][getAllInfo param empty,no data ]",0);
            } else { //上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb
                SaveSysLog("[$module_name][Exception][getAllInfo error,get data error]",0);
            }
            continue;
        }
    } else {
        SaveSysLog("[$module_name][failed to get info,server down]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        //down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node,__MONITOR_TYPE_DAEMON);
        continue;
    }

    $check_item="checking item";

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($daemon_info->m_server);

    /* {{{ 检查webserver状态
     */
    $scan_item="webserver status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE012C), (array)$detailSetting)) {
        if ($daemon_webserver['warn_status']==$daemon_info->m_webserver_status) {
            eventAdd(1,__EVCODE012W,$daemon_info->m_server,sprintf($daemon_webserver['warn_word'],$daemon_info->m_server,$daemon_info->m_webserver_status));
            saveEventDetail($daemon_info->m_server,__MONITOR_TYPE_DAEMON,__EVCODE012W); //存事件代码 
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查daemon状态
     */
    $scan_item="daemon status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE013C), (array)$detailSetting)) {
        if ($daemon_daemon['warn_status']==$daemon_info->m_daemon_status) {
            eventAdd(1,__EVCODE013W,$daemon_info->m_server,sprintf($daemon_daemon['warn_word'],$daemon_info->m_server,$daemon_info->m_daemon_status));
            saveEventDetail($daemon_info->m_server,__MONITOR_TYPE_DAEMON,__EVCODE013W); //存事件代码 
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查login状态
     */
    $scan_item="login status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE014C), (array)$detailSetting)) {
        if ($daemon_login['warn_status']==$daemon_info->m_login_status) {
            eventAdd(1,__EVCODE014W,$daemon_info->m_server,sprintf($daemon_login['warn_word'],$daemon_info->m_server,$daemon_info->m_login_status));
            saveEventDetail($daemon_info->m_server,__MONITOR_TYPE_DAEMON,__EVCODE014W); //存事件代码 
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查adserv状态
     */
    $scan_item="adserv status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE015C), (array)$detailSetting)) {
        if ($daemon_adserv['warn_status']==$daemon_info->m_adserv_status) {
            eventAdd(1,__EVCODE015W,$daemon_info->m_server,sprintf($daemon_adserv['warn_word'],$daemon_info->m_server,$daemon_info->m_adserv_status));
            saveEventDetail($daemon_info->m_server,__MONITOR_TYPE_DAEMON,__EVCODE015W); //存事件代码 
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查errorlog状态 
     */
    $scan_item="errorlog status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE016C), (array)$detailSetting)) {
        if ($daemon_errorlog['warn_status']==$daemon_info->m_error_log_status) {
            eventAdd(1,__EVCODE016W,$daemon_info->m_server,sprintf($daemon_errorlog['warn_word'],$daemon_info->m_server,$daemon_info->m_error_log_status));
            saveEventDetail($daemon_info->m_server,__MONITOR_TYPE_DAEMON,__EVCODE016W); //存事件代码 
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    mdbSaveLastCheckTime($daemon_info->m_server); //保存检查时间 
}
/*}}}*/

?>
