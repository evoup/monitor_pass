<?php
/*
  +----------------------------------------------------------------------+
  | Name:madnSaveData.php
  +----------------------------------------------------------------------+
  | Comment:保存客户端RAWPOST上传的监控数据
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 7月19日 星期四 10时27分24秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-04 10:51:14
  +----------------------------------------------------------------------+
 */
$sub_module_name="madnSaveData";
foreach($run_madn_info as $madn_info){
    mdbUpdateSrvList($madn_info->m_server, __MONITOR_TYPE_MADN); //更新全局服务器列表 
    SaveSysLog("[$sub_module_name][mdb_set][montype:".__MONITOR_TYPE_MADN."][server:".$madn_info->m_server."][message:".($madn_info->m_client_message)."]",3);
    // 保存测速数据
    $access_speed_arr=$madn_info->m_url_access_speed;
    $dateTs=strtotime(date('Y-m-d'));
    foreach ( $access_speed_arr as $speedTestItem=>$speedTestInfo ) {
        // 检查是否为已经删除或者已经禁用,是则跳过
        try {
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED,$speedTestItem,'info:delete');
            $deleted=$arr[0]->value;
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED,$speedTestItem,'info:enable');
            $enable=$arr[0]->value;
            if ( $deleted || !$enable ) {
                continue;
            }
        } catch ( Exception $e ) {
            SaveSysLog("[$module_name][$sub_module_name][get site delete status or enable status err,cause".$e->getMessage()."]",3);
        }
        if ( in_array(substr(str_pad($speedTestInfo['statusCode'],3,"0",STR_PAD_LEFT),0,1),array(2,3)) ) {
            $speedCurrent=$speedTestInfo['time_total'] ? ( $speedTestInfo['size_download']/$speedTestInfo['time_total']/1024) : 0; // 精确到Kb数 
            try {
                // 取出最慢速度，最快速度,平均速度
                $column="info:testspeed_url-{$speedTestItem}-lspeed{$dateTs}";
                $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER_HISTORY, $madn_info->m_server, $column);
                if (!empty($arr[0]->value)) {
                    $lspeed=$arr[0]->value;
                }
                $column="info:testspeed_url-{$speedTestItem}-hspeed{$dateTs}";
                $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER_HISTORY, $madn_info->m_server, $column);
                if (!empty($arr[0]->value)) {
                    $hspeed=$arr[0]->value;
                }
                $column="info:testspeed_url-{$speedTestItem}-speed{$dateTs}";
                $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER_HISTORY, $madn_info->m_server, $column);
                if (!empty($arr[0]->value)) {
                    $speed=$arr[0]->value;
                }
                SaveSysLog("[$module_name][$sub_module_name][write speed][get l&h speed][lspeed:$lspeed][hspeed:$hspeed][speedCurrent:$speedCurrent]",3);
                if ( empty($lspeed) && empty($hspeed) ) {
                    $lspeed=$hspeed=$speedCurrent;
                } elseif (empty($lspeed)) {
                    $lspeed=$speedCurrent;
                }
                SaveSysLog("[$module_name][$sub_module_name][write speed][adjust empty speed][lspeed:$lspeed][hspeed:$hspeed]",3);
                // 保存矫正前的数据
                $prevSpeed=$speed;
                $prevLspeed=$lspeed;
                $prevHspeed=$hspeed;
                // 有新数据进行矫正
                if ( !empty($lspeed) ) {
                    $lspeed=$lspeed>$speedCurrent ? ($speedCurrent>0 ? $speedCurrent : $lspeed) : $lspeed;
                }
                $hspeed=$hspeed-$speedCurrent>0 ? $hspeed : $speedCurrent;
                // 计算平均速
                if ( !empty($speedCurrent) && !empty($speed) ) {
                    $speed=($speed+$speedCurrent)/2; // 与上次保存的进行平均
                } else {
                    $speed=($lspeed+$hspeed)/2;
                }
                if ($speed!=$prevSpeed || $lspeed!=$prevLspeed || $hspeed!=$prevHspeed) { // 监测数据发生变化，监测次数+1 
                    $column="info:testspeed_url-{$speedTestItem}-times{$dateTs}";
                    $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER_HISTORY, $madn_info->m_server, $column);
                    if (!empty($arr[0]->value)) {
                        $times=$arr[0]->value+1;
                    } else {
                        $times=1;
                    }
                }
                SaveSysLog("[$module_name][$sub_module_name][write speed][host:{$madn_info->m_server}][site:$speedTestItem][lspeed:$lspeed][hspeed:$hspeed][speed:$speed][times:$times]",3);
                $column="info:testspeed_url-{$speedTestItem}-lspeed{$dateTs}";
                $mutations[]=new Mutation( array('column'=>$column,'value'=>floatval($lspeed)) );
                $column="info:testspeed_url-{$speedTestItem}-hspeed{$dateTs}";
                $mutations[]=new Mutation( array('column'=>$column,'value'=>floatval($hspeed)) );
                $column="info:testspeed_url-{$speedTestItem}-speed{$dateTs}";
                $mutations[]=new Mutation( array('column'=>$column,'value'=>floatval($speed)) );
                if ( !empty($times) ) {
                    $column="info:testspeed_url-{$speedTestItem}-times{$dateTs}";
                    $mutations[]=new Mutation( array('column'=>$column,'value'=>intval($times)) );
                }
                $GLOBALS['mdb_client']->mutateRow( __MDB_TAB_SERVER_HISTORY, $madn_info->m_server, $mutations ); 
            } catch (Exception $e) {
                SaveSysLog("[$module_name][$sub_module_name][write speed info error!]",3);
            }
            unset($lspeed,$hspeed,$speed,$mutations,$times);
        }
    }
    
    /*{{{ 存服务器上传时间戳 for scan
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_LASTTIME, $madn_info->m_server), time());
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp ok!]",4);
    }
    /*}}}*/
    /*{{{存客户端监控信息 for scan 
     */
    $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_MADN, $madn_info->m_server),
        $madn_info->m_client_message);
    if(false===$res){
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg failed!]",3);
    } else {
        SaveSysLog("[$module_name][$sub_module_name][mdb set client msg ok!]",4);
    }
    /*}}}*/
    /* {{{ 存主机状态(在线状态、上次上传时间) for display 
     */
    mdbSaveHostStatus($madn_info->m_server);
    /* }}} */

    /* {{{ 存即时信息表
     */
    saveMonitorInfo($madn_info, __MONITOR_TYPE_MADN);
    /* }}} */
    /* {{{ 存历史信息表
     */
    mdbSaveMonitorHistoryInfo($madn_info, __MONITOR_TYPE_MADN);
    /* }}} */
}
?>
