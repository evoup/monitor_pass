<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_hadoop.php
  +----------------------------------------------------------------------+
  | Comment:检查hadoop类型服务器监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年11月 3日 星期六 22时38分44秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-05 17:54:39
  +----------------------------------------------------------------------+
*/
$module_name="scan_hadoop";

/*{{{对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key=sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_HADOOP, $serv_node);
    SaveSysLog("[$module_name][row_key:]".$row_key,3);
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $mon_hadoop_info=$res[__MDB_COL_EVENT]->value; //取得指定类型指定服务器的监控信息 
        } catch (Exception $e) {
        }
        $try++;
    }
    $row_key=sprintf(__KEY_LASTTIME, $serv_node);
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
        $hadoop_info=new clsHadoop($mon_hadoop_info); //获取一个mysql对象
        if (false===$hadoop_info->getAllInfo()) { //包含了全部监控指标的当前值  
            if (empty($mon_hadoop_info)) { //客户端没有传此类监控信息 
                SaveSysLog("[$module_name][getAllInfo param empty,no data ]",0);
            } else { //上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb //TODO hadoop日志
               //收集不及时时也返回false,要区别
                SaveSysLog("[$module_name][Exception][getAllInfo error,get data error]",0);
            }
            continue;
        }
    } else { //没有获取到和超过当机判断时间的算down 
        SaveSysLog("[$module_name][failed to get info,server down]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        //down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node,__MONITOR_TYPE_HADOOP);
        continue;
    }

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($hadoop_info->m_server);
    SaveSysLog("[$module_name][$check_item][$scan_item][monitoritem:".json_encode($detailSetting)."]",5);

    /* {{{ 检查datanode块复制时间
     */
    $scan_item="dfs.datanode.copyBlockOp_avg_time";
    SaveSysLog("[$module_name][$check_item][$scan_item][threshold:".
        "{$dfs_datanode_copyBlockOp_avg_time['caution_start']}][val:{$hadoop_info->m_hdfsMetric['copyBlockOp_avg_time']}]",4);
    if (false===$detailSetting || in_array(getEventNum(__EVCODE026C), (array)$detailSetting)) {
        if ($hadoop_info->m_hdfsMetric['copyBlockOp_avg_time']>$dfs_datanode_copyBlockOp_avg_time['caution_start']) {
            if (determineEvent($hadoop_info->m_server, __EVCODE026C)) {
                eventAdd(0,__EVCODE026C,$hadoop_info->m_server,sprintf("%s:dfs.datanode.copyBlockOp_avg_time too long.current:{$hadoop_info->m_hdfsMetric['copyBlockOp_avg_time']}ms",$hadoop_info->m_server));
                saveEventDetail($hadoop_info->m_server,__MONITOR_TYPE_HADOOP,__EVCODE026C); //存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */
    /* {{{ 检查datanode向dnamenode汇报时间
     */
    $scan_item="dfs.datanode.heartBeats_avg_time";
    SaveSysLog("[$module_name][$check_item][$scan_item][threshold:".
        "{$dfs_datanode_heartBeats_avg_time['caution_start']}][val:{$hadoop_info->m_hdfsMetric['heartBeats_avg_time']}]",4);
    if (false===$detailSetting || in_array(getEventNum(__EVCODE027C), (array)$detailSetting)) {
        if ($hadoop_info->m_hdfsMetric['heartBeats_avg_time']>$dfs_datanode_heartBeats_avg_time['caution_start']) {
            if (determineEvent($hadoop_info->m_server, __EVCODE027C)) {
                eventAdd(0,__EVCODE027C,$hadoop_info->m_server,sprintf("%s:dfs.datanode.heartBeats_avg_time too long.current:{$hadoop_info->m_hdfsMetric['heartBeats_avg_time']}ms",$hadoop_info->m_server));
                saveEventDetail($hadoop_info->m_server,__MONITOR_TYPE_HADOOP,__EVCODE027C); //存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */
    mdbSaveLastCheckTime($hadoop_info->m_server); //保存检查时间 
}
/*}}}*/
?>
