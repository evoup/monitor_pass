<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_report.php
  +----------------------------------------------------------------------+
  | Comment:检查report监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-01-10 10:25:06
  +----------------------------------------------------------------------+
*/
$module_name = "scan_report";

// 把report类中需要存记录的成员变量换成标准名字 
$dict = array(
    'log_process_stat'     => 'm_log_process_stat',
    'process_speed'        => 'm_process_speed',
    'wait_process_log_num' => 'm_wait_process_log_num'
    );

/*{{{ 对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key = sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_REPORT, $serv_node);
    SaveSysLog("[$module_name][row_key:]".$row_key,3);
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
        $res = $res[0]->columns;
        $mon_report_info = $res[__MDB_COL_EVENT]->value; // 取得指定类型指定服务器的监控信息 
    } catch (Exception $e) {
    }
    $row_key = sprintf(__KEY_LASTTIME, $serv_node );
    $res = "";
    $tried = 0;
    while (empty($res) && $tried<=1) {
        // 重连机制
        try {
            $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res = $res[0]->columns;
            $last_alive_time = $res[__MDB_COL_EVENT]->value; // 取得上次获取到客户端信息的时间
        } catch (Exception $e) {
        }
        $tried++;
    }
    if (!empty($last_alive_time) && time()-$last_alive_time<=$down_over_time ) { 
        $report_info = new clsReport($mon_report_info); // 获取一个report对象
        if (false===$report_info->getAllInfo()) { // 包含了全部监控指标的当前值  
            if (empty($mon_report_info)) { // 客户端没有传此类监控信息 
                SaveSysLog("[$module_name][getAllInfo param empty,no data ]",0);
            } else { // 上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb
                SaveSysLog("[$module_name][Exception][getAllInfo error,get data error]",0);
            }
            continue;
        }
    } else {
        SaveSysLog("[$module_name][failed to get info,server down]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        // down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node, __MONITOR_TYPE_REPORT);
        continue;
    }

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($report_info->m_server);

    /* {{{ 检查待处理log数量
     */
    $scan_item = "wait process log num";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE022C), (array)$detailSetting)) {
        if ($report_info->m_wait_process_log_num > $report_wait_process_log_num['caution_start']) {
            eventAdd(0,__EVCODE022C,$report_info->m_server,sprintf($report_wait_process_log_num['caution_word'],$report_info->m_server,$report_info->m_wait_process_log_num));
            saveEventDetail($report_info->m_server,__MONITOR_TYPE_REPORT,__EVCODE022C); // 存事件代码
        }
        SaveSysLog("[$module_name][checking item][$scan_item][check done][current wait logs:{$report_info->m_wait_process_log_num}]",4);
    } else {
        SaveSysLog("[$module_name][checking item][$scan_item][check set off]",4);
    }
    /* }}} */

    mdbSaveLastCheckTime($report_info->m_server); // 保存检查时间 
}
/*}}}*/
?>
