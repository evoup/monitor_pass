<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan_generic.php
  +----------------------------------------------------------------------+
  | Comment:检查generic类型服务器监控事件以获取警报信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-02-08 13:31:29
  +----------------------------------------------------------------------+
 */
$module_name="scan_generic";

//把Server类中需要存记录的成员变量换成标准名字以便存Db 
$dict=array(
    'summary' => 'm_summary', 
    'cpu'     => 'm_cpu',
    'mem'     => 'm_mem',
    'swap'    => 'm_swap',
    'disk'    => 'm_disk',
    'process' => 'm_process',
    'network' => 'm_network',
    'link'    => 'm_link',
    'service' => 'm_service'
);

/*{{{对组内每个server取出监控信息并检测
 */
foreach ($serv_group as $serv_node) {
    if (in_array($serv_node, (array)$Unmonitored)) { // 跳过不监控的服务器 
        continue;
    }
    $row_key=sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_GENERIC, $serv_node); //指定类型指定服务器名为监控信息的key
    SaveSysLog("[$module_name][row_key:$row_key]",3);
    $res="";
    $try=0;
    while (empty($res) && $try<=1) {
        //重连机制
        try {
            $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res=$res[0]->columns;
            $mon_generic_info=$res[__MDB_COL_EVENT]->value; //取得指定类型指定服务器的监控信息 
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
            SaveSysLog("[$module_name][$e]",3);
        }
        $try++;
    }
    if (!empty($last_alive_time) && time()-$last_alive_time<=$down_over_time ) { 
        $generic_info=new clsGeneric($mon_generic_info); //获取一个server对象
        if (false===$generic_info->getAllInfo()) { //包含了全部监控指标的当前值  
            if (empty($mon_generic_info)) { //客户端没有传此类监控信息 
                SaveSysLog("[$module_name][getAllInfo param empty,no data ]",0);
            } else { //上传的数据无效，此状况为异常,因为upload时已做判断，无效数据不存mdb
                SaveSysLog("[$module_name][Exception][[server:$serv_node]getAllInfo error,get data error]",0);
            }
            continue;
        }
    } else { //没有获取到和超过当机判断时间的算down 
        SaveSysLog("[$module_name][failed to get info,server down][row_key:$row_key]",3);
        $GLOBALS['downed_srv'][] = $serv_node;
        //down机，从servtype内删除,再次scan就能报down
        mdbRemoveFromAliveServerList($serv_node,__MONITOR_TYPE_GENERIC); //XXX 这里比较容易出现问题，就是一定要求客户端写最近上传时间 last_alive_time, 需要注意,现在已知hbase对频繁操作的row_key有低微概览的读写问题，需要做错误处理！
        continue;
    }

    /* 获取该服务器的明细监控选项 */
    $detailSetting=mdbGetHostMonDetailSetting($generic_info->m_server);

    /* {{{ 检查该服务器disk的capacity是否正常 
     */
    $check_item="checking item";
    $scan_item="disk_capacity";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE000C), (array)$detailSetting)) {
        foreach ($generic_info->m_disk as $disk_info) {
            if (!empty($disk_info['capacity'])) { //允许该项空不报警 
                if (floatval($disk_info['capacity'])>=floatval($disk_range['caution_start']) &&
                    floatval($disk_info['capacity'])<=floatval($disk_range['caution_end'])) {
                        SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
                        if (determineEvent($generic_info->m_server, __EVCODE000C)) {
                            eventAdd(0,__EVCODE000C,$generic_info->m_server,sprintf($disk_range['caution_word'],$generic_info->m_server,$disk_info['mounted'],$disk_info['capacity']));
                            saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE000C); //存事件代码 
                        }
                    } elseif (floatval($disk_info['capacity'])>=floatval($disk_range['warn_start'])) {
                        SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
                        if (determineEvent($generic_info->m_server, __EVCODE000W)) {
                            eventAdd(1,__EVCODE000W,$generic_info->m_server,sprintf($disk_range['warn_word'],$generic_info->m_server,$disk_info['mounted'],$disk_info['capacity']));
                            saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE000W); //存事件代码 
                        }
                    }
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查该服务器的inode是否正常
     */
    $scan_item="inode_capacity";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE001C), (array)$detailSetting)) {
        foreach ($generic_info->m_disk as $disk_info) {
            if (!empty($disk_info['iused'])) {  // 允许该项空不报警 
                if (floatval($disk_info['iused'])>=floatval($disk_inode['caution_start']) &&
                    floatval($disk_info['iused'])<=floatval($disk_inode['caution_end'])) {
                        eventAdd(0,__EVCODE001C,$generic_info->m_server,sprintf($disk_inode['caution_word'],$generic_info->m_server,$disk_info['mounted'],$disk_info['iused']));
                        saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE001C); // 存事件代码 
                    } elseif (floatval($disk_info['iused'])>=floatval($disk_inode['warn_start'])) {
                        eventAdd(1,__EVCODE001W,$generic_info->m_server,sprintf($disk_inode['warn_word'],$generic_info->m_server,$disk_info['mounted'],$disk_info['iused']));
                        saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE001W); // 存事件代码 
                    }
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    foreach ($generic_info->m_summary as $summary_key => $summary_val) {
        switch ($summary_key) {
        case "load":
            /* {{{ 检查该服务器Load Average (1min)是否正常
             */
            $scan_item="load average(1min)";
            if (false===$detailSetting || in_array(getEventNum(__EVCODE002C), (array)$detailSetting)) {
                if (!empty($summary_val)) { //允许该项空不报警 
                    if (floatval($summary_val)>=floatval($load_average['caution_start']) && floatval($summary_val)<=floatval($load_average['caution_end'])) {
                        SaveSysLog("[$module_name][$check_item][$scan_item][a potential problem event]",4);
                        if (determineEvent($generic_info->m_server, __EVCODE002C)) {
                            eventAdd(0,__EVCODE002C,$generic_info->m_server,sprintf($load_average['caution_word'],$generic_info->m_server,$summary_val));
                            saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE002C); //存事件代码 
                        }
                    } elseif (floatval($summary_val)>=floatval($load_average['warn_start'])) {
                        eventAdd(1,__EVCODE002W,$generic_info->m_server,sprintf($load_average['warn_word'],$generic_info->m_server,$summary_val));
                        saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE002W); //存事件代码 
                    }
                }
                SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
            } else {
                SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
            }
            /* }}} */
            break;
        case "tcp_connections":
            /* {{{ 检查该服务TCP/IP连接数量是否正常 
             */
            $scan_item="tcp/ip connnections";
            if (false===$detailSetting || in_array(getEventNum(__EVCODE007C), (array)$detailSetting)) {
                if (!empty($summary_val)) { //允许该项空不报警 
                    if (intval($summary_val)>=intval($tcpip_connections['caution_start']) &&
                        intval($summary_val)<=intval($tcpip_connections['caution_end'])) {
                            eventAdd(0,__EVCODE007C,$generic_info->m_server,sprintf($tcpip_connections['caution_word'],$generic_info->m_server,$summary_val));
                            saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE007C); //存事件代码 
                        } elseif (intval($summary_val)>=intval($tcpip_connections['warn_start'])) {
                            eventAdd(1,__EVCODE007W,$generic_info->m_server,sprintf($tcpip_connections['warn_word'],$generic_info->m_server,$summary_val));
                            saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE007W); //存事件代码 
                        }
                }
                SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
            } else {
                SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
            }
            /* }}} */
            break;
        }
    }

    /* {{{ 检查内存使用率是否正常
     */
    $scan_item = "memory usage percent";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE003C), (array)$detailSetting)) {
        list($mem_active, $mem_inact, $mem_wired, $mem_cache, $mem_buf, $mem_free)= array_values($generic_info->m_mem);
        if (!empty($mem_inact) && !empty($mem_free)) { // 允许该项空不报警 
            $mem_usg_pct = sprintf('%01.2f',$mem_active*100/($mem_inact+$mem_free+$mem_active));
            if ($mem_usg_pct>$memory_usage_percent['caution_start']) { 
                eventAdd(0,__EVCODE003C,$generic_info->m_server,sprintf($memory_usage_percent['caution_word'],$generic_info->m_server,$mem_usg_pct));
                saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE003C); // 存事件代码 
            }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][mem_active:$mem_active mem_inact:$mem_inact mem_free:$mem_free]",5);
        SaveSysLog("[$module_name][$check_item][$scan_item][memory usage percent:$mem_usg_pct]",4);
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查进程数是否正常
     */
    $scan_item = "process running number";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE004C), (array)$detailSetting)) {
        list($process_sum, $process_starting, $process_running, $process_sleeping, $process_stopped, $process_zombie, $process_waiting,
            $process_lock) = array_values($generic_info->m_process);
        if (!empty($process_running)) { //允许该项空不报警  // TODO 该项空要追溯到上次事件的状态 
            if (intval($process_running)>=intval($running_process_num['caution_start']) &&
                intval($process_running)<=intval($running_process_num['caution_end'])) {
                    eventAdd(0,__EVCODE004C,$generic_info->m_server,sprintf($running_process_num['caution_word'],$generic_info->m_server,$process_running));
                    saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE004C); //存事件代码 
                } elseif (intval($process_running)>=intval($running_process_num['warn_start']) &&
                    intval($process_running)<=intval($running_process_num['warn_end'])) {
                        eventAdd(0,__EVCODE004W,$generic_info->m_server,sprintf($running_process_num['warn_word'],$generic_info->m_server,$process_running));
                        saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE004W); //存事件代码 
                    }
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查各TCP/IP服务端口
     */
    $scan_item="tcp/ip service port";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE006C), (array)$detailSetting)) {
        foreach ($generic_info->m_service as $service) {
            list($tcpip_service_name, $tcpip_service_port, $tcpip_service_status)=array_values($service);
            if ($tcpip_service_status===$tcpip_service['caution_status']) { //允许该项空不报警 
                $problem_services_word.=sprintf($tcpip_service['caution_word'],$generic_info->m_server,$tcpip_service_name,$tcpip_service_port)."    ";
            }
        }
        if (!empty($problem_services_word)) {
            if (determineEvent($generic_info->m_server, __EVCODE006C)) {
                eventAdd(0,__EVCODE006C,$generic_info->m_server,$problem_services_word);
                saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE006C); //存事件代码 
            }
            unset($problem_services_word);
        }
        SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    /* {{{ 检查网卡流量是否正常
     */
    $scan_item="net work flow";
    if (false===$detailSetting || in_array(getEventNum(__EVCODE008C), (array)$detailSetting)) {
    foreach ($generic_info->m_network as $network) {
        if (!empty($network)) {
            list($nw_iface, $nw_in, $nw_out)=array_values($network);
            $current_nw_flow=$nw_in+$nw_out; //In+Out总量 
            if ((!empty($nw_in) || !empty($nw_out)) && floatval($current_nw_flow)>=floatval($network_flow['caution_start'])) { //允许该项空不报警 
                $problem_network_flow_word.=sprintf($network_flow['caution_word'],$generic_info->m_server, $nw_iface, $current_nw_flow, $nw_in ,$nw_out)."    ";
                //SaveSysLog("[current_nw_flow:$current_nw_flow nw_in:$nw_in nw_out:$nw_out]",4);
            }
        }
    }
    if (!empty($problem_network_flow_word)) {
        eventAdd(0,__EVCODE008C, $generic_info->m_server, $problem_network_flow_word);
        saveEventDetail($generic_info->m_server,__MONITOR_TYPE_GENERIC,__EVCODE008C); //存事件代码 
        unset($problem_network_flow_word);
    }
    SaveSysLog("[$module_name][$check_item][$scan_item][check done]",4);
    } else {
        SaveSysLog("[$module_name][$check_item][$scan_item][check set off]",4);
    }
    /* }}} */

    mdbSaveLastCheckTime($generic_info->m_server); //保存检查时间 
}
/*}}}*/
?>
