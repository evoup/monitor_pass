<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_madn.php
  +----------------------------------------------------------------------+
  | Comment:检查madn监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 7月18日 星期三 19时02分02秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-07-18 19:02:18
  +----------------------------------------------------------------------+
 */
$module_name='scan_madn';

/*{{{对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key=sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_MADN, $serv_node);
    SaveSysLog("[$module_name][row_key:$row_key]",3);
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $mon_madn_info=$res[__MDB_COL_EVENT]->value; //取得指定类型指定服务器的监控信息 
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
        $madn_info=new clsMadn($mon_madn_info); //获取一个madn对象
        if (false===$madn_info->getAllInfo()) { //包含了全部监控指标的当前值  
            if (empty($mon_madn_info)) { //客户端没有传此类监控信息 
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
        mdbRemoveFromAliveServerList($serv_node,__MONITOR_TYPE_MADN);
        continue;
    }

    $check_item="checking item";

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($madn_info->m_server);

    /* {{{ 检查madn url状态
     */
    $scan_item="madnurl status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE025C), (array)$detailSetting)) {
        foreach ((array)$madn_info->m_url_status as $urlName => $urlInfo) {
            if (in_array(substr(str_pad($urlInfo['statusCode'],3,"0",STR_PAD_LEFT),0,1),array(2,3))) {
                // ok, our status code is 2XX or 3XX
                SaveSysLog("[$module_name][$check_item][$scan_item][check urlName:$urlName ok]",4);
            } else {
                SaveSysLog("[$module_name][$check_item][$scan_item][check urlName:$urlName abmornal detected]",4);
                if (determineEvent($madn_info->m_server, __EVCODE025C)) {
                    if (empty($urlInfo['statusCode'])) {
                        eventAdd(1,__EVCODE025C,$madn_info->m_server,sprintf($madn_availability['warn_word'],$madn_info->m_server,$urlName," access timeout."));
                    } else {
                        eventAdd(1,__EVCODE025C,$madn_info->m_server,sprintf($madn_availability['warn_word'],$madn_info->m_server,$urlName," got status code:{$urlInfo['statusCode']}."));
                    }
                    saveEventDetail($madn_info->m_server,__MONITOR_TYPE_MADN,__EVCODE025C); //存事件代码 
                }
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    mdbSaveLastCheckTime($madn_info->m_server); //保存检查时间 
}
/*}}}*/
?>
