<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_serving.php
  +----------------------------------------------------------------------+
  | Comment:检查serving监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-02-20 15:25:29
  +----------------------------------------------------------------------+
 */
$module_name = "scan_serving";

/* 把clsServing类中需要存记录的成员变量换成标准名字 */
$dict = array(
    'request'      => 'm_request', 
    'adimage'      => 'm_adimage',
    'loginfo'      => 'm_loginfo',
    'traffic'      => 'm_traffic',
    'enginestatus' => 'm_enginestatus',
);

/*{{{ 对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    // 是否为自定义组，是key用自定义组名|服务器名，否key用3|服务器
    if (false!==belongCustomizeGroup($serv_node)) {
        $tmpArr = belongCustomizeGroup($serv_node);
        $key_flag = $tmpArr[0]; // XXX 临时用其中的一个解决
    } else {
        $key_flag = __MONITOR_TYPE_SERVING;
    }
    $row_key = $scan_type==__MONITOR_TYPE_CUST ?sprintf(__KEY_CLIENT_MSG, $key_flag, $serv_node) :sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_SERVING, $serv_node);
    SaveSysLog("[$module_name][get mon info][row_key:$row_key]",3);
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
        $res = $res[0]->columns;
        $mon_serving_info = $res[__MDB_COL_EVENT]->value; // 取得指定类型指定服务器的监控信息 
    } catch (Exception $e) {
    }
    $row_key = sprintf(__KEY_LASTTIME, $serv_node);
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
    $now = time();
    SaveSysLog("[$module_name][get lastime][row_key:$row_key][scan_type:$scan_type][last_alive_time:$last_alive_time][now:$now][down_over_time:$down_over_time]",3);
    if (!empty($last_alive_time) && time()-$last_alive_time<=$down_over_time ) { 
        $serving_info = new clsServing($mon_serving_info); // 获取一个serving对象
        if (false===$serving_info->getAllInfo()) { // 包含了全部监控指标的当前值  
            // 上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb
            SaveSysLog("[$module_name][Exception][getAllInfo error,get data error]",0);
            continue;
        }
    } else {
        SaveSysLog("[$module_name][failed to get info,server down][row_key:$row_key][scan_type:$scan_type]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        // down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node, __MONITOR_TYPE_SERVING);
        continue;
    }

    $check_item = "checking item";

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($serving_info->m_server);

    /* {{{ 检查单台负荷是否正常
     */
    $scan_item = "request";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE009C), (array)$detailSetting)) {
        $request_num = $serving_info->m_request;
        if (!empty($request_num)) { // 允许该项空不报警 
            if (intval($request_num)>=intval($serving_request['caution_start'])) {
                SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
                if (determineEvent($serving_info->m_server, __EVCODE009C)) {
                    eventAdd(0,__EVCODE009C,$serving_info->m_server,sprintf($serving_request['caution_word'],$serving_info->m_server,$request_num));
                    saveEventDetail($serving_info->m_server,__MONITOR_TYPE_SERVING,__EVCODE009C); // 存事件代码 
                }
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    // 检查工作节点数量 TODO

    /* {{{ 检查广告发布(状态) TODO 多台的红色警报
     */
    $scan_item = "advt publish status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE011C), (array)$detailSetting)) {
        if ($serving_deliver['caution_status']===$serving_info->m_enginestatus) {
            SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
            if (determineEvent($serving_info->m_server, __EVCODE011C)) {
                eventAdd(0,__EVCODE011C,$serving_info->m_server,sprintf($serving_deliver['caution_word'],$serving_info->m_server,$serving_info->m_enginestatus));
                saveEventDetail($serving_info->m_server,__MONITOR_TYPE_SERVING,__EVCODE011C); // 存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查日志生成状态
     */
    $scan_item = "log creation status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE023C), (array)$detailSetting)) {
        // 取得loginfo的最近保存md5和时间戳
        try {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, sprintf(__KEY_SERVING_LOGINFO,$serving_info->m_server) , __MDB_COL_EVENT);
            list($total_log_num,$upload_log_num,$empty_counter)=explode('#',$arr[0]->value);
        } catch (Exception $e) {
            unset($total_log_num,$upload_log_num,$empty_counter);
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][host:{$serving_info->m_server}][total_log_num:$total_log_num][upload_log_num:$upload_log_num][empty_counter:$empty_counter]",4);
        if ($empty_counter>__LOGININFO_RETRY_EMPTY_TIME) { // 连续上传n次没有数据认为创建日志失败 
            SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
            if (determineEvent($serving_info->m_server, __EVCODE023C)) {
                eventAdd(0,__EVCODE023C,$serving_info->m_server,sprintf($serving_loginfo['caution_word'],$serving_info->m_server));
                saveEventDetail($serving_info->m_server,__MONITOR_TYPE_SERVING,__EVCODE023C); // 存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查单台广告填充率
     */
    $scan_item = "single fillrate";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE024C), (array)$detailSetting)) {
        SaveSysLog("[$module_name][$check_item][$scan_item][total_log:{$serving_info->m_loginfo['total_log_num']}][upload_log_num:{$serving_info->m_loginfo['upload_log_num']}]",4);
        if (!empty($serving_info->m_loginfo['total_log_num'])) {
            $fillrate=$serving_info->m_loginfo['upload_log_num']/$serving_info->m_loginfo['total_log_num'];
            if ($fillrate<$serving_fillrate['caution_start']/100 && $serving_info->m_loginfo['total_log_num']>=100) { // 客户端上传采样不足100的不理会 
                if (determineEvent($serving_info->m_server, __EVCODE024C)) {
                    SaveSysLog("[$module_name][$check_item][$scan_item][fillrate:$fillrate][catch a low fillrate!]",3);
                    eventAdd(0,__EVCODE024C,$serving_info->m_server,sprintf($serving_fillrate['caution_word'],$serving_info->m_server, $fillrate*100, $serving_fillrate['caution_start']));
                    saveEventDetail($serving_info->m_server,__MONITOR_TYPE_SERVING,__EVCODE024C); // 存事件代码 
                }
            } else {
                SaveSysLog("[$module_name][$check_item][$scan_item][fillrate:$fillrate][ok]",4);
            }
        } else {
            // 客户端会在没有收集数据的时候上传2个空，此时需要判断上次是否存在问题事件，存在则再次添加一个事件
            SaveSysLog("[$module_name][$check_item][$scan_item][client upload zero, need analysis]",4);
            $hasEvent=in_array(str_pad(__EVCODE024C, 4, "0", STR_PAD_LEFT), (array)$_SERVER['needfix_orig']) ?true :false;
            if ($hasEvent) { // 如何未解决事件列表存在填充率事件，找是否有这台服务器 
                SaveSysLog("[$module_name][$check_item][$scan_item][got fillrate event!]",4);
                try {
                    $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, 'nf'.str_pad(__EVCODE024C, 4, "0", STR_PAD_LEFT), "event:item");
                    $val = $arr[0]->value;
                    $srvTimeArr=(array)explode('|', $val);
                    SaveSysLog("[$module_name][$check_item][$scan_item][".serialize($srvTimeArr)."]",3);
                    foreach ($srvTimeArr as $srvTime) {
                        if (strstr($srvTime, $serving_info->m_server) && $fillrate<$serving_fillrate['caution_start']/100) {
                            if (determineEvent($serving_info->m_server, __EVCODE024C)) {
                                SaveSysLog("[$module_name][$check_item][$scan_item][fillrate:$fillrate][catch a previous low fillrate, set event again!]",3);
                                eventAdd(0,__EVCODE024C,$serving_info->m_server,sprintf($serving_fillrate['caution_word'],$serving_info->m_server, $fillrate*100, $serving_fillrate['caution_start']));
                                saveEventDetail($serving_info->m_server,__MONITOR_TYPE_SERVING,__EVCODE024C); // 存事件代码 
                            }
                        }
                    }
                } catch (Exception $e) {
                    SaveSysLog("[$module_name][$check_item][$scan_item][get fillevent err]",3);
                }
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    // 检查日志传送状态 TODO
    $scan_item = "log transport status";

    // 检查获取信息情况 TODO
    $scan_item = "get info statement";

    mdbSaveLastCheckTime($serving_info->m_server); // 保存检查时间 
}
/*}}}*/
?>
