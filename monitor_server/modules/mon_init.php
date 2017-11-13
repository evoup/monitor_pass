<?php
/*
  +----------------------------------------------------------------------+
  | Name: mon_init.php
  +----------------------------------------------------------------------+
  | Comment: 监控服务端初始化
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-22 16:38:37
  +----------------------------------------------------------------------+
 */
error_reporting(E_ALL & ~E_NOTICE);
$mon_init_time=time(); // 初始化时间 
$controller_type = strtolower($_GET[__CNAME]); // 控制器类型，调用对应syslog 
$GLOBALS['controller_type']=$controller_type;
$module_name="mon_init";

/**
 *       【读取配置流程】
 *   ___________________________________________________________
 *  |  读取本地INI配置文件,如果选择工作模式work_mode为分布式，  |
 *  |  则仅仅读取MDB部分的设置.                                 |
 *  |  如果为本地模式，读取全部配置,则结束读取流程。            | 
 *  |  如果一开始就连本地配置文件也没有读取，程序会自动生成一个 |
 *  |  本基配置文件                                             | 
 *  '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 *                            . | .
 *   __________________________ .____________________________________
 *  |  如果选择了work_mode为分布式,则从远程MDB获取配置文件，         |
 *  |  如果没有获取到，则须修改本地配置文件MDB配置的部分,如果确认MDB |
 *  |  部分配置没有问题，请检查MDB服务器，再次尝试从MDB获取。        |
 *  ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
makeDir(__CONF_FILE,"0755",0,'f'); // 创建配置文件目录 
$conf_sample = file_get_contents(dirname(__FILE__)."/../conf/monitor_server.ini.sample");
if (true === buildConf(__CONF_FILE,$conf_sample)) { // 创建默认SAMPLE配置文件 
    echo "build configuration file,done. run again\n";
    doExit("build conf");
} 

/* 配置文件读取 */ 
SaveSysLog("[$module_name][will read configure file!]");
if (file_exists(__CONF_FILE)) {
    $try=0;
    $getConf=false;
    while (!$getConf && $try<2) {
        $ini_res=file_get_contents(__CONF_FILE);
        if ($_CONFIG=parse_ini_string($ini_res,true)) {
            $getConf=true;
        }
        $try++;
    }
} else {
    SaveSysLog("[$module_name][configure file exception,check script privilege!]");
    doExit("read conf");
}

$general_conf = $_CONFIG['general'];
if (empty($general_conf)) { // 可能因为网络问题没有获取到配置文件的状况时 
    file_put_contents(__CONF_FILE, $conf_sample); // 重载默认配置文件 
    doExit("reload default conf");
}
foreach ($general_conf as $conf_key => $conf_val) {
    $$conf_key = $conf_val; // general段的配置直接以配置文件中的名字命令变量 
}


$stag = GenerateStag(); // 为本次扫描设置一个唯一id 
$GLOBALS['stag']=$stag;
SaveSysLog("[$module_name][start -----------------------]",1);
closeMdb();
$mdb_host=explode('|',$mdb_host); // 获取thrift服务器组 

// 根据配置文件打开MDB 
if (true!==openMdb($mdb_host, $mdb_sendtimeout, $mdb_recvtimeout))
{
    if (substr(php_sapi_name(), 0, 3) == 'cli')
    {
        infrastructionMail2admin('Open mdb(hbase) error!Platform monitor function invalid!');
    }
}

/*{{{ 扫描时只允许运行一个进程
 */
if (empty($controller_type) && $argv[1]!='daily') {
    $pid_file = __PRGM_PATH . '/' . __RUN_SUBPATH . '/' . __PROCESS_NAME.'.pid';
    $pid_file2 = __PRGM_PATH . '/' . __RUN_SUBPATH . '/' . __PROCESS2_NAME.'.pid';
    if (!file_exists($pid_file)) {
        makeDir($pid_file,"0755",0,'f');
    }
    $last_pid = file_get_contents($pid_file);
    if (!flock($master_pid_fp=fopen($pid_file,'w'), LOCK_NB | LOCK_EX)) {
        SaveSysLog("[$module_name][last scan process exists,last pid:$last_pid]",1); 
        if (!empty($last_pid)) {
            file_put_contents($pid_file, $last_pid); // w模式须写回last_pid
        } else {
            SaveSysLog("[$module_name][pid not lt 0 exit]",1); 
        }
        SaveSysLog("[$module_name][last pid:$last_pid]",2); 
        doExit();
    } else {
        if (!$GLOBALS['daemoned']) {
            //daemonize();
            $GLOBALS['daemoned'] = true; // 扫描时以守护进程形式出现
        }
    }
    $pid = posix_getpid();
    if (!empty($pid)) {
        file_put_contents($pid_file, $pid);
        SaveSysLog("[$module_name][write pid:$pid]",2); 
    }
}
/*}}}*/


/*{{{ 根据ini工作模式,读取设置
 */
switch ($work_mode) {
case(__INI_WORK_MODE_LOCAL): // 使用本地配置文件 
    SaveSysLog("[$module_name][use local configure setting]", 4);
    break;
case(__INI_WORK_MODE_MDB): // 使用远程配置文件 
    /*{{{ 读取界面上设置的待删除的服务器列表 */
    if ($runCli) {
        try {
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, __KEY_TODELETE_SERVERS, __MDB_COL_CONFIG_INI);
            $toDeleteServers=(array)json_decode($arr[0]->value);
            SaveSysLog("[$module_name][toDeleteServers:".json_encode($toDeleteServers)."]", 4);
            if (!empty($toDeleteServers)) {
                //关闭客户端的上传
                exec($client_fpm_stop_shell);
                usleep(10000);
                //自动配置有关数据中删出
                $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVERNAME, __KEY_ALLSRV, __MDB_COL_SERVERNAME_ALL);
                $allAutoConfServers=(array)( explode('|',$arr[0]->value) );
                if (!empty($allAutoConfServers)) {
                    $remainAuto=(array)array_diff($allAutoConfServers,$toDeleteServers);
                    mdb_set( __MDB_TAB_SERVERNAME, __MDB_COL_SERVERNAME_ALL, __KEY_ALLSRV, 
                        join('|',$remainAuto) );
                }
                for ($serv_type=1;$serv_type<=__MONITOR_TYPES_NUM;$serv_type++) {
                    $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVERNAME, sprintf(__KEY_SERVGROUP,
                        $serv_type), __MDB_COL_SERVERNAME_ALL); 
                    $servGroupAuto=(array)( explode('|',$arr[0]->value) );
                    if (!empty($servGroupAuto)) {
                        $remainServGroupAuto=(array)array_diff($servGroupAuto,$toDeleteServers);
                        mdb_set( __MDB_TAB_SERVERNAME, __MDB_COL_SERVERNAME_ALL, sprintf(__KEY_SERVGROUP,
                            $serv_type), join('|',$remainServGroupAuto) );
                    }
                }

                //调整默认组服务器存活列表
                for ($serv_type=1;$serv_type<=__MONITOR_TYPES_NUM;$serv_type++) {
                    $row_key=sprintf(__KEY_ALIVESRV,$serv_type);
                    $res=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $row_key, __MDB_COL_EVENT);
                    $serv_group=$res[0]->value;
                    if (!empty($serv_group)) {
                        $tempStrs=explode('|', $serv_group);
                        if ( !empty($toDeleteServers) ) {
                            $tempStrs=(array)array_diff($tempStrs,$toDeleteServers);
                            //写回调整后的存活服务器列表
                            mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, join('|',$tempStrs) ); 
                        }
                    }
                }
                //调整自定义服务器存活列表
                $tmpConf=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER,__KEY_INIDATA,__MDB_COL_CONFIG_INI);
                $tmpConf=$tmpConf[0]->value;
                $tmpConf = @parse_ini_string($tmpConf,true);
                $tmp_serv_list=$tmpConf['server_list'];
                foreach ($tmp_serv_list as $serv_type => $srv) {
                    $temp_type_name = explode("type_",$server_type);
                    $type_name = $temp_type_name[1];
                    if (!in_array($type_name,range(1,__MONITOR_TYPES_NUM))) {
                        $row_key=sprintf(__KEY_ALIVESRV,$type_name);
                        $res=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $row_key, __MDB_COL_EVENT);
                        $serv_group=$res[0]->value;
                        $tempStrs=explode('|', $serv_group);
                        if ( !empty($toDeleteServers) ) {
                            $tempStrs=(array)array_diff($tempStrs,$toDeleteServers);
                            //写回调整后的存活服务器列表
                            mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, join('|',$tempStrs) ); 
                        }
                    }
                }

                // 调用界面删除服务器并生成deleteServerGenerateIni的api(会重建INI)
                $html=noBlockFileGetContents($api_url_prefix.'/delete/process_delete_server/@all', 60); // TODO这个api暂时权限开放 
                SaveSysLog("[$module_name][toDeleteServersApi:".$html."]", 4);
                if (empty($html)) {
                    SaveSysLog("[$module_name][access delete api_url get none data!]", 4);
                } elseif ($html=='ok:deleted') {
                    //配置文件中删除完成，必须清空待处理事件列表中已删除项，然后清空待删除服务器列表
                    if (!empty($toDeleteServers)) {
                        //删除待处理事件中各有关事件
                        $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER,__KEY_NEEDFIX,__MDB_COL_EVENT);
                        $events=$arr[0]->value;
                        $events=(array)explode('|',$events);
                        foreach ($events as $event) {
                            $arr2=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER,'nf'.$event,__MDB_COL_EVENT);
                            $eventServers=(array)explode('|',$arr2[0]->value);
                            foreach ($eventServers as $serverTime) {
                                list($srv,$tm)=explode('#',$serverTime);
                                if ( !in_array($srv,$toDeleteServers) ) { //排除delete的 
                                    $newSrvTime[]=$srv.'#'.$tm;
                                }
                            }
                            mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, 'nf'.$event, join('|',$newSrvTime) );
                            if (!empty($newSrvTime)) {
                                $tmpNewNeedfix[]=$event;
                            }
                            unset($newSrvTime);
                        }
                        mdb_set( __MDB_TAB_SERVER, __MDB_COL_EVENT, __KEY_NEEDFIX, join('|',$tmpNewNeedfix) );
                        unset($tmpNewNeedfix);
                        mdb_set( __MDB_TAB_SERVER, __MDB_COL_CONFIG_INI, __KEY_TODELETE_SERVERS, '' );
                        restoreUpload();
                        killZkCliProc();
                        doExit('server list changed restart monitor server.');
                    }
                } else {
                    SaveSysLog("[$module_name][access delete api_url got other statement!]", 4);
                }
                restoreUpload();
            }
        } catch (Exception $e) {
            restoreUpload();
            doExit("[$module_name][read toDeleteServers err]");
        } 
    }
    /* }}} */
    /*{{{ 从MDB获取INI
     */
    $try=0;
    $ConfLoaded=false;
    while (!$ConfLoaded && $try<10) {
        try { 
            $row_key = __KEY_INIDATA;
            $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER,$row_key,array(__MDB_COL_CONFIG_INI));
            SaveSysLog("[$module_name][getRowWithColumns mdbconf ok]", 4);
            $res = $res[0]->columns;
            $ini_res = $res[__MDB_COL_CONFIG_INI]->value; // 获取value 
            if (!$ini_res) { // 此为异常，建表的时候输入了默认数据
                SaveSysLog("[$module_name][getRowWithColumns mdbconf error][ini_res err][$e]", 2);
            } else {
                SaveSysLog("[$module_name][got mdb configure file]", 4);
                // 配置文件读取
                $_CONFIG = @parse_ini_string($ini_res,true);
                SaveSysLog("[$module_name][parse_ini_string done]", 4);
                $general_conf = $_CONFIG['general'];
                SaveSysLog("[$module_name][general_conf done]", 4);
                foreach ($general_conf as $conf_key => $conf_val) {
                    $$conf_key = $conf_val; // general段的配置直接以配置文件中的名字命令变量 
                }
                $GLOBALS['debug_level']=$debug_level;
                $GLOBALS['save_upload_log']=$save_upload_log;
                $GLOBALS['save_update_log']=$save_update_log;
                $GLOBALS['upload_log_facility']=$upload_log_facility;
                $GLOBALS['upload_log_level']=$upload_log_level;
                $GLOBALS['scan_log_facility']=$scan_log_facility;
                $GLOBALS['scan_log_level']=$scan_log_level;
                $GLOBALS['update_log_facility']=$update_log_facility;
                $GLOBALS['update_log_level']=$update_log_level;
                $ConfLoaded=true;
            }
        } catch (Exception $e) {
            SaveSysLog("[$module_name][getRowWithColumns mdbconf error][$e]", 2);
            if (!empty($controller_type) || substr(php_sapi_name(), 0, 3) !== 'cli') {
                doExit("client get conf failed"); // 客户端上传数据获取配置文件失败直接退出 TODO 从缓存读 
            }
            if ($try==9) {
                if (substr(php_sapi_name(), 0, 3) == 'cli')
                {
                    infrastructionMail2admin('Get info from mdb(hbase) error!Platform monitor function invalid!');
                }
                doExit("hbase tsocket time out,tryed $try times");
            }
            SaveSysLog("[$module_name][timeout recover,go on process]", 2);
        }
        $try++;
        SaveSysLog("[$module_name][try:$try]", 4);
    }
    // 从mdb同步本地的配置文件,仅在扫描时
    if (substr(php_sapi_name(), 0, 3) == 'cli' && $work_mode==__INI_WORK_MODE_MDB) {
        file_put_contents(__CONF_FILE, $ini_res);
    }
    /*}}}*/
    break;
}
/*}}}*/


/*{{{ 载入配置文件中的服务器列表(和全局服务器列表__KEY_ALLSRV的区别是:此为多维数组，后者为服务器名一维数组)
 */
$server_list = $_CONFIG['server_list']; // 被监控服务器列表 
foreach ($server_list as $server_type => $srv) {
    $temp_type_name = explode("type_",$server_type);
    $type_name = $temp_type_name[1];
    if (in_array($type_name,range(1,__MONITOR_TYPES_NUM))) { // 对应全部服务器type 
        $monitored_servers["$type_name"] = explode(',',$server_list["type_$type_name"]); // 存入被监控服务器数组 
    } else { //自定义组 
        $cust_monitored_servers["$type_name"] = explode(',',$server_list["type_$type_name"]); // 存入被监控服务器数组(自定义组)
    }
}
/*}}}*/

/*{{{ 配置变量
 */
$disk_range           = $_CONFIG['disk_range']; // 磁盘容量 
$disk_inode           = $_CONFIG['disk_inode']; // Inode占用率
$mail                 = $_CONFIG['mail']; // Mail 
$load_average         = $_CONFIG['load_average']; // Load average(1 min) 
$memory_usage_percent = $_CONFIG['memory_usage_percent']; // 内存使用率 
$running_process_num  = $_CONFIG['running_process_num']; // 运行进程数 
$tcpip_service        = $_CONFIG['tcpip_service']; // tcpip端口监控 
$tcpip_connections    = $_CONFIG['tcpip_connections']; // tcpip连接数 
$network_flow         = $_CONFIG['network_flow']; // 网卡流量 

$serving_request      = $_CONFIG['serving_request']; // serving单台负荷 每秒request数量 
$serving_loginfo      = $_CONFIG['serving_loginfo']; // serving单台日志生成状态(日志有无更新)
$serving_fillrate     = $_CONFIG['serving_fillrate']; // serving单台填充率
$serving_deliver      = $_CONFIG['serving_deliver']; // serving广告发布

$daemon_webserver= $_CONFIG['daemon_webserver'];// webserver状态
$daemon_daemon   = $_CONFIG['daemon_daemon']; // daemon状态 
$daemon_login    = $_CONFIG['daemon_login']; // login状态 
$daemon_adserv   = $_CONFIG['daemon_adserv']; // adserv状态
$daemon_errorlog = $_CONFIG['daemon_errorlog']; // errorlog状态 

$mysql_db_connections       = $_CONFIG['mysql_db_connections']; // db连接数量
$mysql_db_threads           = $_CONFIG['mysql_db_threads']; // db线程数量
$mysql_master_slave         = $_CONFIG['mysql_master_slave']; // db的master和slave工作状态 
$mysql_seconds_behind_master = $_CONFIG['mysql_seconds_behind_master']; // db的slave延迟时间 
$report_wait_process_log_num = $_CONFIG['report_wait_process_log_num']; // report待处理log数量

$madn_availability = $_CONFIG['madn_availability']; // madn可用性 

$dfs_datanode_copyBlockOp_avg_time = $_CONFIG['dfs_datanode_copyBlockOp_avg_time']; //datanode块复制时间 
$dfs_datanode_heartBeats_avg_time = $_CONFIG['dfs_datanode_heartBeats_avg_time']; //datanode向namenode汇报时间 

$alarm_interval = $_CONFIG['alarm_interval']; // 报警间隔

$mail_from       = $mail['mail_from'];
$sender_name     = $mail['sender_name'];
$mail_to_caution = (array)$mail['mail_to_caution'];
$mail_cc_caution = (array)$mail['mail_cc_caution'];
$mail_to_warning = (array)$mail['mail_to_warning'];
$mail_cc_warning = (array)$mail['mail_cc_warning'];

$host_monitor_detail = $_CONFIG['host_monitor_detail']; // 监控明细项 
/*}}}*/

$notDownServer = true;
$notDownGroup = true;
$notDownCustServer = true;
$notDownCustGroup = true;
$notDownRest = true;

$Unmonitored = (array)explode(',', $_CONFIG['not_monitored']['not_monitored']); // 所有未监控的服务器名单 
SaveSysLog("[$module_name][Unmonitored:".join(',', $Unmonitored)."]",3); 
?>
