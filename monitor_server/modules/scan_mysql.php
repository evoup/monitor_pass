<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_mysql.php
  +----------------------------------------------------------------------+
  | Comment:检查mysql监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-14 18:59:18
  +----------------------------------------------------------------------+
 */
$module_name="scan_mysql";

//把db类中需要存记录的成员变量换成标准名字 
$dict=array(
    'summary'     => 'm_summary', 
    'traffic'     => 'm_traffic',
    'statement'   => 'm_statement',
    'replication' => 'm_replication',
    'dbinfo'      => 'm_dbinfo',
    'tableifo'    => 'm_tableifo'
);

/*{{{对组内每个server取出监控信息并检测 
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key=sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_MYSQL, $serv_node);
    SaveSysLog("[$module_name][row_key:]".$row_key,3);
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $mon_mysql_info=$res[__MDB_COL_EVENT]->value; //取得指定类型指定服务器的监控信息 
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
        $mysql_info=new clsMysql($mon_mysql_info); //获取一个mysql对象
        if (false===$mysql_info->getAllInfo()) { //包含了全部监控指标的当前值  
            if (empty($mon_mysql_info)) { //客户端没有传此类监控信息 
                SaveSysLog("[$module_name][getAllInfo param empty,no data ]",0);
            } else { //上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb
                SaveSysLog("[$module_name][Exception][getAllInfo error,get data error]",0);
            }
            continue;
        }
    } else { //没有获取到和超过当机判断时间的算down 
        SaveSysLog("[$module_name][failed to get info,server down]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        //down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node,__MONITOR_TYPE_MYSQL);
        continue;
    }

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($mysql_info->m_server);

    /* {{{ 检查数据库连接数
     */
    if (false===$detailSetting || in_array(getEventNum(__EVCODE017C), (array)$detailSetting)) {
        $scan_item="db connections";
        $summary=$mysql_info->m_summary;
        $connections=$summary['connections'];
        if (!empty($connections)) { //允许该项空不报警 
            if (intval($connections)>=intval($mysql_db_connections['caution_start']) && intval($connections)<=intval($mysql_db_connections['warn_start'])) {
                eventAdd(0,__EVCODE017C,$mysql_info->m_server,sprintf($mysql_db_connections['caution_word'],$mysql_info->m_server,$connections));
                saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE017C); //存事件代码 
            } elseif (intval($connections)>=intval($mysql_db_connections['warn_start'])) {
                eventAdd(1,__EVCODE017W,$mysql_info->m_server,sprintf($mysql_db_connections['warn_word'],$mysql_info->m_server,$connections));
                saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE017W); //存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查数据库线程数
     */
    $scan_item="db threads";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE019C), (array)$detailSetting)) {
        $threads=$summary['threads_created'];
        if (!empty($threads)) { //允许该项空不报警 
            if (intval($threads)>=intval($mysql_db_threads['caution_start']) && intval($threads)<=intval($mysql_db_threads['warn_start'])) {
                eventAdd(0,__EVCODE019C,$mysql_info->m_server,sprintf($mysql_db_threads['caution_word'],$mysql_info->m_server,$threads));
                saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE019C); //存事件代码 
            } elseif (intval($threads)>=intval($mysql_db_threads['warn_start'])) {
                eventAdd(1,__EVCODE019W,$mysql_info->m_server,sprintf($mysql_db_threads['warn_word'],$mysql_info->m_server,$threads));
                saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE019W); //存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */


    // TODO 检查关键表
    /* {{{ 检查MASTER SLAVE状态
     */
    $scan_item="db master-slave status";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE020C), (array)$detailSetting)) {
        $replication=strtoupper($mysql_info->m_field[__MYSQL_FIELD_REPLICATION]);
        if ($replication!="ON" && $mysql_info->m_field[__MYSQL_FIELD_MASTERSLAVE]==__MYSQL_MASTERSLAVE_S) {
            if (determineEvent($mysql_info->m_server, __EVCODE020C)) {
                eventAdd(0,__EVCODE020C,$mysql_info->m_server,sprintf($mysql_master_slave['warn_word'],$mysql_info->m_server));
                saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE020C); //存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /*{{{TODO 检查MASTER SLAVE的设置,slave_io_running和slave_sql_running*/
    $scan_item="db slave running";
    // 可以不用检查，上面用mysqladmin已经检查到了run slave
    /*}}}*/

    /*{{{检查MASTER SLAVE的同步延迟时间*/
    $scan_item="db slave latency";
    //SaveSysLog("[$module_name][$check_item][$scan_item][{$mysql_info->m_server}][".json_encode($detailSetting)."]",4);
    if (false===$detailSetting || in_array(getEventNum(__EVCODE029C), (array)$detailSetting)) {
        $seconds_behind_master=$mysql_info->m_seconds_behind_master;
        if ( 0!==strlen($seconds_behind_master) ) { //允许该项空不报警 
            if (intval($seconds_behind_master)>=intval($mysql_seconds_behind_master['caution_start']) && 
                intval($seconds_behind_master)<=intval($mysql_seconds_behind_master['warn_start'])) {
                    eventAdd(0,__EVCODE029C,$mysql_info->m_server,sprintf($mysql_seconds_behind_master['caution_word'],
                        $mysql_info->m_server,$seconds_behind_master));
                    saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE029C); //存事件代码 
                } elseif (intval($seconds_behind_master)>=intval($mysql_seconds_behind_master['warn_start'])) {
                    eventAdd(1,__EVCODE029W,$mysql_info->m_server,sprintf($mysql_seconds_behind_master['warn_word'],
                        $mysql_info->m_server,$seconds_behind_master));
                    saveEventDetail($mysql_info->m_server,__MONITOR_TYPE_MYSQL,__EVCODE029W); //存事件代码 
                }
            SaveSysLog("[$module_name][$check_item][$scan_item][{$mysql_info->m_server}][check done]",4);
        } else {
            SaveSysLog("[$module_name][$check_item][$scan_item][{$mysql_info->m_server}][check set off]",4);
        }
    }
    /*}}}*/

    mdbSaveLastCheckTime($mysql_info->m_server); //保存检查时间 
}
/*}}}*/
?>
