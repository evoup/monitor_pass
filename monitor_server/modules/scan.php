<?php
/*
  +----------------------------------------------------------------------+
  | Name:scan.php
  +----------------------------------------------------------------------+
  | Comment:扫描各台监控信息并发送警报邮件的模块
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-21 11:35:29
  +----------------------------------------------------------------------+
 */
$module_name="scan";
setEngineStatMaster();

if ( false===infrastructionInit() ) { // 基础设置初始化返回false，暂时不扫描
    SaveSysLog("[$module_name][infrastructionInit][in latency, cancel scan]",4);
    sleep(3);
    return;
}
SaveSysLog("[$module_name][infrastructionInit][ok]",4);
mdbUpdateTableSrv(); //将ini文件配置的服务器存入mdb XXX 这里要改下，不再需要手工维护id了
readNeedFixList(); //读取之前保留的待处理事件列表(for 判断哪些事件发生变化)
readNeedfixServerEvent(); //读取之前保留的服务器事件(读到SERVER['nf']数组里)

/*{{{从storage中取出全部存活服务器名
 */
/* 取出默认组的 */ 
for ($serv_type=1;$serv_type<=__MONITOR_TYPES_NUM;$serv_type++) {
    $row_key=sprintf(__KEY_ALIVESRV,$serv_type);
    $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
    $res=$res[0]->columns;
    $serv_group=$res[__MDB_COL_EVENT]->value; //获取value 
    if (!empty($serv_group)) {
        $tempStrs=explode('|', $serv_group);

        if ( !empty($toDeleteServers) ) {
            $tempStrs=(array)array_diff($tempStrs,$toDeleteServers);
            //写回调整后的存活服务器列表
            mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, join('|',$tempStrs) ); 
        }

        foreach ($tempStrs as $sv) {
            // 即使某台自定义组主机在配置文件中属于默认组，这里为了逻辑也要分开  // TODO 这部分转到mon_init 
            if (false===belongCustomizeGroup($sv)) {
                !in_array($sv, (array)$tempDefaultSrvs) && $tempDefaultSrvs[]=$sv;
            }
        }
        $serv_group=join('|', (array)$tempDefaultSrvs);
        $alive_servers[$serv_type]=$serv_group; //存为对应类型的alive服务器数组 
        unset($tempDefaultSrvs);
    }
}
//print_r($alive_servers);
SaveSysLog("[$module_name][all alived server groups and nodes:]".join(' ',(array)$alive_servers),4);
/* 取出自定义组的 */ 
foreach ((array)$cust_monitored_servers as $cust_group_name => $srvs) {
    $row_key=sprintf(__KEY_ALIVESRV,$cust_group_name);
    foreach ($srvs as $srv) {
        $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
        $res=$res[0]->columns;
        $serv_group=$res[__MDB_COL_EVENT]->value; //获取value 

        if ( !empty($toDeleteServers) ) {
            $temp_serv_group=(array)explode('|',$serv_group);
            $serv_group=join( '|', array_diff($temp_serv_group,$toDeleteServers) );
            //写回调整后的自定义组服务器存活列表
            if ( !is_int($cust_group_name) ) { //确保不要写到默认组的存活列表去 
                mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serv_group ); 
            }
        }

        if (!empty($serv_group)) {
            $alive_cust_servers["$cust_group_name"]=$serv_group; //存为自定义类型的alive服务器数组 
        }
    }
}
//print_r($alive_cust_servers);
//die;
SaveSysLog("[$module_name][all alived customize server groups and nodes:]".join(',',(array)$alive_cust_servers),4);
/*}}}*/

//先处理down机警报 
SaveSysLog("[$module_name][checking down event for default group...]",4);
SaveSysLog("[$module_name][alive_servers:".serialize($alive_servers)."]",3); 
$down_arr=mdbProcessDown($alive_servers,__GROUP_TYPE_DEFAULT);  //如果每个点down，函数发送终极警报,否则移到mail.php去报
SaveSysLog("[$module_name][down_arr".serialize($down_arr)."]",3); 
mdbSaveLastCheckTime($down_arr, true); //保存检查时间 
/*{{{设置默认组的down标记 for mail*/
if (false!=$down_arr) {
    SaveSysLog("[$module_name][down_arr:".serialize($down_arr)."]",3); 
    list($downed_servers,$downed_groups)=$down_arr;
    SaveSysLog("[$module_name][downed_servers:".serialize($downed_servers)."]",3); 
    SaveSysLog("[$module_name][Unmonitored:".join(',', $Unmonitored)."]",3); 
    $downed_servers = array_diff((array)$downed_servers, (array)$Unmonitored); // 排除未监控的服务器 
    SaveSysLog("[$module_name][after filter unmonitored, downed_servers:".serialize($downed_servers)."]",3); 
    $downed_servers = determineDownHosts($downed_servers); // 确认没有误报 
    if (count($downed_servers)) {
        SaveSysLog("[$module_name][at least one server machine of default group downed!]",3);
        $notDownServer=false;
    } 
    if (count(array_filter((array)$downed_groups))) {
        SaveSysLog("[$module_name][at least one group of default group downed!]",3);
        $notDownGroup=false;
    }
    if ($notDownServer && $notDownGroup) {
        SaveSysLog("[$module_name][all server mechines and groups of default connecting with our monitor server!]",4);
    }
} else {
    //exit; //默认组全体点down，已发送终极警报退出 
}
/*}}}*/

SaveSysLog("[$module_name][checking down event for customize group...]",4);
$down_cust_arr=mdbProcessDown($alive_cust_servers,__GROUP_TYPE_CUSTOMIZE); //自定义组的 

mdbSaveLastCheckTime($down_cust_arr[0], true); //保存检查时间 

/*{{{设置自定义组的down标记 for mail
 */
if (false!=$down_cust_arr) {
    SaveSysLog("[$module_name][down_cust_arr:".serialize($down_cust_arr)."]",3); 
    list($downed_cust_servers,$downed_cust_groups)=$down_cust_arr;
    $downed_cust_servers = array_diff((array)$downed_cust_servers, (array)$Unmonitored); // 排除未监控的服务器 
    SaveSysLog("[$module_name][after filter unmonitored, downed_cust_servers:".serialize($downed_cust_servers)."]",3); 
    $downed_cust_servers = determineDownHosts($downed_cust_servers); // 确认没有误报 
    if (count($downed_cust_servers)) {
        SaveSysLog("[$module_name][at least one server machine of customize group downed!]",3);
        $notDownCustServer=false;
    } 
    if (count(array_filter((array)$downed_cust_groups))) {
        SaveSysLog("[$module_name][at least one group of customize group downed!]",3);
        $notDownCustGroup=false;
    }
    if ($notDownCustServer && $notDownCustGroup) {
        SaveSysLog("[$module_name][all server mechines and groups of customize group connecting with our monitor server!]",4);
    }
} else {
    //exit; //自定义组全体点down，已发送终极警报退出 
}
$down_rest=mdbProcessDown(NULL,NULL,true); //除存活列表中判断down，剩余非存活的也要判断
mdbSaveLastCheckTime($down_rest, true); //保存检查时间 
$down_rest = array_diff((array)$down_rest, (array)$Unmonitored); // 排除未监控的服务器 
$down_rest = determineDownHosts($down_rest); // 确认没有误报 
if (!empty($down_rest)) {
    $notDownRest=false;
}
/*}}}*/

/*{{{ 对剩余存活的默认组服务器，根据servtype类别，调用对应的监控事件检查
 */
SaveSysLog("[$module_name][start event checking...]",4);
SaveSysLog("[$module_name][alive servers:".join(',',(array)$alive_servers)."]",4);
if (!empty($alive_servers)) {
    foreach ($alive_servers as $mon_type => $server_group) {
        $serv_group = array_filter((array)explode('|',$server_group)); // 得到该默认组下服务器的一维数组 
        $serv_group = array_diff((array)$serv_group, (array)$Unmonitored); // 排除未监控的服务器 
        SaveSysLog("[$module_name][after filter unmonitored server, scan these servers events:".join(',', $serv_group)."]",4);
        if (empty($serv_group)) { //maybe the defalut group has none member 
            continue;
        }
        switch ($mon_type) { 
        case __MONITOR_TYPE_GENERIC: //generic监控事件 
            SaveSysLog("[$module_name][call server event check]",3);
            include("scan_generic.php");
            break;
        case __MONITOR_TYPE_MYSQL:   //mysql监控事件 
            SaveSysLog("[$module_name][call db event check]",3);
            include("scan_mysql.php");
            break;
        case __MONITOR_TYPE_SERVING: //serving监控事件 
            SaveSysLog("[$module_name][call serving event check]",3);
            include("scan_serving.php");
            break;
        case __MONITOR_TYPE_DAEMON: //daemon监控事件 
            SaveSysLog("[$module_name][call daemon event check]",3);
            include("scan_daemon.php");
            break;
        case __MONITOR_TYPE_REPORT: //report监控事件 
            SaveSysLog("[$module_name][call event check report]",3);
            include("scan_report.php");
            break;
        case  __MONITOR_TYPE_MADN: //madn监控事件
            SaveSysLog("[$module_name][call event check madn]",3);
            include("scan_madn.php");
            break;
        case __MONITOR_TYPE_HADOOP: //hadoop监控事件 
            SaveSysLog("[$module_name][call event check hadoop]",3);
            include("scan_hadoop.php");
            break;
        }
    } 
}
/*}}}*/

$module_name="scan";
/* {{{ 对剩余存活的自定义组服务器，调用getServerMonitorType逐个检查其监控的类型
 */
$alive_cust_servers = array_diff((array)$alive_cust_servers, (array)$Unmonitored);
if (!empty($alive_cust_servers)) {
    foreach ($alive_cust_servers as $cust_group_name => $srv_str) {
        $tmp_serv_group = array_values(array_filter(explode('|', $srv_str))); 
        SaveSysLog("[$module_name][call customize group server event check]", 3);
        $scan_type = __MONITOR_TYPE_CUST;
        foreach ($tmp_serv_group as $serv_group) {
            /* {{{ 自定义可能有多个监控类型，判断监控哪些
             */
            $types=getServerMonitorType($serv_group);
            $serv_group=(array)$serv_group; // 为scan_xxx强制转换 
            foreach ((array)$types as $mon_type) {
                switch ($mon_type) { 
                case __MONITOR_TYPE_GENERIC: //generic监控事件 
                    SaveSysLog("[$module_name][call server event check]",3);
                    include("scan_generic.php");
                    break;
                case __MONITOR_TYPE_MYSQL:   //mysql监控事件 
                    SaveSysLog("[$module_name][call db event check]",3);
                    include("scan_mysql.php");
                    break;
                case __MONITOR_TYPE_SERVING: //serving监控事件 
                    SaveSysLog("[$module_name][call serving event check]",3);
                    include("scan_serving.php");
                    break;
                case __MONITOR_TYPE_DAEMON: //daemon监控事件 
                    SaveSysLog("[$module_name][call daemon event check]",3);
                    include("scan_daemon.php");
                    break;
                case __MONITOR_TYPE_REPORT: //report监控事件 
                    SaveSysLog("[$module_name][call event check report]",3);
                    include("scan_report.php");
                    break;
                case __MONITOR_TYPE_MADN: //madn监控事件
                    SaveSysLog("[$module_name][call event check madn]",3);
                    include("scan_madn.php");
                    break;
                case __MONITOR_TYPE_HADOOP: //hadoop监控事件 
                    SaveSysLog("[$module_name][call event check hadoop]",3);
                    include("scan_hadoop.php");
                    break;
                }
            }
            /* }}} */
        }
    }
}
/* }}} */

$module_name = "scan";
reloadNeedFixList(); //从待处理事件列表里删除没有发生的监控事件 //TODO 这2处都有调用发送恢复邮件的判断，要合并！ 
changeNeedfixServerEvent(); //事件发生变化须修改对应的服务器事件 
include('mail.php'); //发送警报邮件 
$module_name = "scan";
if ( !isset($evCleanerCounter) || $evCleanerCounter<30 ) {
    $evCleanerCounter++;
} else {
    resolvedEventcleaner(); //对没有set的解决事件再set一次，以防没有set的bug
    $evCleanerCounter=0;
}
/* {{{ 保存扫描一次的时间供事件重试判断机制使用
 */
$module_name = "scan";
$scan_duration=time()-$mon_init_time+__SCAN_INTERVAL;
mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_DURATION, __KEY_SCAN_DURATION, $scan_duration);
/* }}} */
/* {{{ 如果子进程由于某些原因出现卡住等问题，退出
 */
//if (!$notFirstScan) {
    //if (time()-($zk_last_alive_time=@file_get_contents(__ADDON_ROOT.'/zkcli.work'))>30) {
        //$pid=file_get_contents($pid_file2);
        //shell_exec("/bin/kill -9 {$pid}");
        //SaveSysLog("[$module_name][child process occur stuck.force kill,then exit!]", 4);
        //exit();
    //}
//} else {
    //$notFirstScan=true;
    //sleep(3); // 等待一会儿，子进程需要设置alive
//}
/* }}} */
if (is_object($GLOBALS['redis_client'])) $GLOBALS['redis_client']->quit();
SaveSysLog("[$module_name][all done.]",4);
?>
