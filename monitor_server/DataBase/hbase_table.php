#!/usr/local/php5_admin/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | Name:hbase_table.php
  +----------------------------------------------------------------------+
  | Comment:Mdb(hbase)建表脚本,建表时自行修改运行
  +----------------------------------------------------------------------+
  | Author:evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-10-13 15:45:18
  +----------------------------------------------------------------------+
 */
define(__THRIFT_ROOT,'../GPL/thrift');
define(__MDB_HOST,'192.168.2.197');
define(__MDB_PORT,'32799');
define(__MDB_SENDTIMEOUT, '20000');  //10 seconds
define(__MDB_RECVTIMEOUT, '20000');  //10 seconds
define(__TABLE1_NAME,     'monitor_servername'); //被监控服务器名表
define(__TABLE2_NAME,     'monitor_server'); //服务器信息表
define(__TABLE3_NAME,     'monitor_user'); //用户表
define(__TABLE4_NAME,     'monitor_usergroup'); //用户组表
define(__TABLE5_NAME,     'monitor_host'); //主机表 // XXX 放到monitor_server即时信息表中去 
define(__TABLE6_NAME,     'monitor_server_history'); //服务器历史信息表 
define(__TABLE7_NAME,     'monitor_engine'); //服务端状态配置表
define(__TABLE8_NAME,     'monitor_testspeed'); //统计测速表
define(__TABLE9_NAME,     'monitor_testspeed_history'); //统计测速表
define(__TABLE10_NAME,    'monitor_hosts'); //新主机表
define(__TABLE11_NAME,    'monitor_items'); //新监控项表
define(__TABLE12_NAME,    'monitor_sets'); //新监控集表


chdir(dirname(__FILE__));
include_once('template.php');
include_once(__THRIFT_ROOT.'/Thrift.php');
include_once(__THRIFT_ROOT.'/transport/TSocket.php');
include_once(__THRIFT_ROOT.'/transport/TBufferedTransport.php');
include_once(__THRIFT_ROOT.'/protocol/TBinaryProtocol.php');
include_once(__THRIFT_ROOT.'/packages/Hbase/Hbase.php');
openMdb(__MDB_HOST,__MDB_PORT,__MDB_SENDTIMEOUT,__MDB_RECVTIMEOUT);

$all_create_tables = array(
    __TABLE1_NAME,
    __TABLE2_NAME,
    __TABLE3_NAME,
    __TABLE4_NAME,
    __TABLE5_NAME,
    __TABLE6_NAME,
    __TABLE7_NAME,
    __TABLE8_NAME,
    __TABLE9_NAME,
    __TABLE10_NAME,
    __TABLE11_NAME,
    __TABLE12_NAME

);
/*{{{ 删除表
 */
foreach ($all_create_tables as $table) {
    try {
        if ($GLOBALS['mdb_client']->isTableEnabled($table)) {
            $GLOBALS['mdb_client']->disableTable($table); //需要先disableTable
        }
        $GLOBALS['mdb_client']->deleteTable($table);
    }catch (TException $tx) {
        print "table ".$table." delete failed!\n";
    }
    print "table ".$table." deleted\n";
}
/*}}}*/

/*{{{创建表
 */
$table_arr = array(
    array( //表1的family 
        array(
            'column_family_name' => "servername:", //存按照id=>服务器名 以及all=>全局服务器列表(如server1|server2|server3) 
            'table_name'         => __TABLE1_NAME,
        ),
    ),
    array( //表2的family 
        array(
            'column_family_name' => "generic:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "mysql:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "serving:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "daemon:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "report:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "madn:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "hadoop:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "event:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "config:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "info:",
            'table_name'         => __TABLE2_NAME,
        ),
        array(
            'column_family_name' => "groupid:",
            'table_name'         => __TABLE2_NAME,
        )
    ),
    array( //表3的family 
        array(
            'column_family_name' => "info:",
            'table_name'         => __TABLE3_NAME
        ),
        array(
            'column_family_name' => "groupid:",
            'table_name'         => __TABLE3_NAME
        )
    ),
    array( //表4的family 
        array(
            'column_family_name' => "info:",
            'table_name'         => __TABLE4_NAME
        ),
        array(
            'column_family_name' => "member:",
            'table_name'         => __TABLE4_NAME
        )
    ),
    array( //表5的family 
        array(
            'column_family_name' => "info:",
            'table_name'         => __TABLE5_NAME
        )
    ),
    array( //表6的family 
        array(
            'column_family_name' => "info:",
            'table_name'         => __TABLE6_NAME
        ),
        array(
            'column_family_name' => "event:",
            'table_name'         => __TABLE6_NAME
        )
    ),
    array( //表7的family 
        array(
            'column_family_name' => "config:", //存服务端处理扫描上传的配置 
            'table_name'         => __TABLE7_NAME,
        ),
        array(
            'column_family_name' => "daily:", //存日常邮件的状态
            'table_name'         => __TABLE7_NAME
        ),
        array(
            'column_family_name' => "scan:", //存扫描有关的(如守望事件)的key
            'table_name'         => __TABLE7_NAME
        )
    ),
    array( //表8的family 
        array(
            'column_family_name' => "info:", //存统计测速数据
            'table_name'         => __TABLE8_NAME
        )
    ),
    array( //表9的family 
        array(
            'column_family_name' => "info:", //存统计测速数据(历史数据，for reporting)
            'table_name'         => __TABLE9_NAME
        )
    ),
    array( //表10的family 
        array(
            'column_family_name' => "info:", //存接口的ip或主机名和端口，支持agent、snmp和jmx
            'table_name'         => __TABLE10_NAME
        )
    ),
    array( //表11的family 
        array(
            'column_family_name' => "info:", //存监控项
            'table_name'         => __TABLE11_NAME
        )
    ),
    array( //表12的family 
        array(
            'column_family_name' => "info:", //存监控集
            'table_name'         => __TABLE12_NAME
        )
    )
);
foreach ($table_arr as $table) {
    foreach ($table as $column_fm) {
        /*{{{ 历史信息表CF的版本数的特殊处理
         */
        if ($column_fm['table_name']==__TABLE6_NAME) {
            switch ($column_fm['column_family_name']) {
            case("info:"):
                $ver_num = 288; 
                break;
            case("event:"):
                $ver_num = 1000; 
                break;
            default:
                $ver_num = 3;  //其他CF按照默认 
                break;
            }
        } else {
            $ver_num = 3;  //其他表按照默认 
        } 
        /* }}} */
        $columns[] = 
            new ColumnDescriptor(
                array(
                    'name' => $column_fm['column_family_name'], 
                    'maxVersions' => $ver_num, 
                    //'timeToLive'=>2147483647,  //TTL 不要设置，历史数据永远保存 
                    'blockCacheEnabled'=>true,
                    'inMemory'=>false
                )
            );
    }
    print_r($columns);
    try {
        $GLOBALS['mdb_client']->createTable($column_fm['table_name'], $columns);
    } catch (AlreadyExists $terr) {
        $Ret=false;
        print "table ".$column_fm['table_name']." already exist!\n";
        $GLOBALS['mdb_client']->disableTable($column_fm['table_name']);
        $GLOBALS['mdb_client']->deleteTable($column_fm['table_name']);
        exit("create db failed, please try again!");
    }
    print "table ".$column_fm['table_name']." created\n";
    unset($columns);
}
/* }}} */
/* {{{ 默认配置文件数据
 */
$file = dirname(__FILE__)."/../conf/monitor_server.ini.sample";
if (file_exists($file)) {
    $sample_conf_str=file_get_contents($file);
    echo $sample_conf_str;
} else {
    echo "sample configure file not exist!";
}
$rowkey="inidata";
$table=__TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => $sample_conf_str 
    ) )
);

try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret =false;
}
/* }}} */
/* {{{ 所有入库服务器数据，加-1防止获取失败和获取到空的fix数据
 */
$rowkey="all";
$table=__TABLE1_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "servername:all",
        'value'  => '-1'  //存入-1代表没有任何服务器，防止获取出空和获取失败的判断错误。 //TODO 其他关键key也用此方法 
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--generialSetting 
 */
$rowkey = "generial_setting";
$setting_items = array(
    'client_sleep_time'=>8, //客户端5秒发一次请求
    'send_daily_mail' => "1", //发送日常工作状态邮件 
    'send_daily_mail_time' => "12:00:00" //发送的时间 
);
$table = __TABLE2_NAME;
$mutations = array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--mailSetting 
 */
$rowkey = "mail_setting";
$setting_items = array('send_mail_type'=>0, 'mail_from'=>'monitoradmin@madhouse-inc.com', 'sender_name'=>'MONITOR_ALARM', 'smtp_server'=>'mail.madhouse-inc.com', 'smtp_domain'=>'madhouse-inc.com', 'smtp_port'=>25, 'smtp_username'=>'someone@madhouse-inc.com', 'smtp_password'=>'password', 'smtp_auth'=>1); 
$table = __TABLE2_NAME;
$mutations = array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--alarmSetting 
 */
$rowkey = "alarm_setting";
$setting_items = array('current_engine'=>'monitor_srv1', 'all_default_gp_down'=>3600, 'all_cust_gp_down'=>3600, 'one_default_gp_down'=>3600, 'one_cust_gp_down'=>3600, 'one_default_server_down'=>3600, 'one_cust_server_down'=>3600, 'general_server_event'=>3600, 'recover_notifiction'=>1); 
$table = __TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据--eventSetting
 */
$rowkey = "event_setting";
$setting_items = array(
    'down_over_time'=>'80', //80秒keepalive超时
    'disk_range_caution_start'=>'97',
    'disk_range_warn_start'=>'100',
    'disk_inode_caution_start'=>'90',
    'disk_inode_warn_start'=>'100',
    'load_average_caution_start'=>'30',
    'load_average_warn_start'=>'100',
    'memory_usage_percent_caution_start'=>'98',
    'memory_usage_percent_warn_start'=>'100',
    'running_process_num_caution_start'=>'250',
    'running_process_num_warn_start'=>'500',
    'tcpip_connections_caution_start'=>'2000',
    'tcpip_connections_warn_start'=>'4000',
    'network_flow_caution_start'=>'52428800', //50M 
    'network_flow_warn_start'=>'104857600', //100M 
    'mysql_db_connections_caution_start'=>'500',
    'mysql_db_connections_warn_start'=>'3000',
    'mysql_db_threads_caution_start'=>'500',
    'mysql_db_threads_warn_start'=>'3000',
    'mysql_seconds_behind_master_caution_start'=>'1000',
    'mysql_seconds_behind_master_warn_start'=>'5000',
    'serving_request_caution_start'=>'500',
    'serving_request_warn_start'=>'3000',
    'serving_fillrate_caution_start'=>'5', // 5% 
    'serving_fillrate_warn_start'=>'0', // 0% 
    'report_wait_process_log_num_caution_start'=>'100',
    'report_wait_process_log_num_warn_start'=>'1000',
    'dfs_datanode_copyBlockOp_avg_time_caution_start'=>'5', //ms 
    'dfs_datanode_copyBlockOp_avg_time_warn_start'=>'500',
    'dfs_datanode_heartBeats_avg_time_caution_start'=>'5',
    'dfs_datanode_heartBeats_avg_time_warn_start'=>'500'
);
$table = __TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--servlistSetting 
 */
$rowkey = "servlist_setting";
$setting_items = array('type_1'=>"", 'type_2'=>"", 'type_3'=>"", 'type_4'=>"", 'type_5'=>"", 'type_6'=>"",
                       'type_7'=>"", 'type_8'=>"", 'tpye_9'=>"", 'type_10'=>"", 'type_11'=>""); 
$table = __TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--servlistCustSetting 
 */
$rowkey = "servlist_cust_setting";
$setting_items = array('type_cust'=>array()); 
$table = __TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 配置文件默认数据项--scan_setting
 */
$rowkey = "scan_setting";
$setting_items = array('generic_disk_range_scanopt'=>'0|0|1', 'generic_disk_inode_scanopt'=>'0|0|1', 'generic_load_average_scanopt'=>'0|0|1', 'generic_memory_usage_percent_scanopt'=>'0|0|1', 'generic_running_process_num_scanopt'=>'0|0|1', 'generic_tcpip_service_scanopt'=>'0|2|3', 'generic_tcpip_connections_scanopt'=>'0|0|1', 'generic_network_flow_scanopt'=>'0|0|1', 'mysql_db_connections_scanopt'=>'0|0|1','mysql_db_threads_scanopt'=>'0|0|1','mysql_master_slave_scanopt'=>'0|10|14','mysql_key_table_scanopt'=>'0|0|1','mysql_seconds_behind_master'=>'0|0|1','serving_request_scanopt'=>'0|0|1','serving_loginfo_scanopt'=>'0|10|3','serving_deliver_scanopt'=>'0|10|2','serving_fillrate_scanopt'=>'0|10|5','daemon_webserver_scanopt'=>'0|0|1','daemon_daemon_scanopt'=>'0|5|3','daemon_login_scanopt'=>'0|0|1','daemon_adserv_scanopt'=>'0|0|1','daemon_errorlog_scanopt'=>'0|0|1','report_wait_process_log_num_scanopt'=>'0|0|1','madn_availability_scanopt'=>'0|10|3','dfs_datanode_copyBlockOp_avg_time'=>'0|0|1','dfs_datanode_heartBeats_avg_time'=>'0|0|1');
$table = __TABLE2_NAME;
$mutations=array(
    new Mutation( array(
        'column' => "config:ini",
        'value'  => json_encode($setting_items)
    ) )
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 默认用户数据
 */
$table = __TABLE3_NAME;
$rowkey = "monitoradmin";
$mutations = array(
    new Mutation( array(
        'column' => "info:realname",
        'value'  => "monitoradmin has no realname"
    )),
    new Mutation( array(
        'column' => "info:email",
        'value'  => "monitoradmin@madhouse-inc.com"
    )),
    new Mutation( array(
        'column' => "info:passwd",
        'value'  => "monitoradmin"
    )),
    new Mutation( array(
        'column' => "info:mailtype",
        'value'  => "1"
    )),
    new Mutation( array(
        'column' => "info:desc",
        'value'  => "监控默认用户,拥有最高权限的管理员"
    )),
    new Mutation( array(
        'column' => "groupid:monitoradmin",
        'value'  => "member"
    ))
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 接口表默认有服务器这台的采用客户端接口的数据
 */
$table = __TABLE10_NAME;
$rowkey = "monitorserver";
$mutations = array(
    new Mutation( array(
        'column' => 'info:agent_interface',
        'value'  => '127.0.0.1:15667'
    )),
    new Mutation( array(
        'column' => 'info:snmp_interface',
        'value'  => ''
    )),
    new Mutation( array(
        'column' => 'info:jmx_interface',
        'value'  => ''
    )),
    new Mutation( array(
        'column' => 'info:data_collector',
        'value'  => ''
    )),
    new Mutation( array(
        'column' => 'info:template',
        'value'  => ''
    )),
    new Mutation( array(
        'column' => 'info:monitored',
        'value'  => '0'
    ))
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* }}} */
/* {{{ 默认用户组数据
 */
$table = __TABLE4_NAME;
$rowkey = "monitoradmin";
$mutations = array(
    new Mutation( array(
        'column' => "info:desc",
        'value'  => "默认用户组"
    )),
    new Mutation( array(
        'column' => "info:privilege",
        'value'  => "enginestatus#2|mdbstatus#2|healthstatus#2|eventsummary#2|topological#2|serversummary#2|eventall#2|servergroup#6|mailsetting#4|alarmsetting#4|eventsetting#4|monitorsetting#4|usersetting#4|usergroupsetting#6"
    )),
    new Mutation( array(
        'column' => "member:monitoradmin",
        'value'  => "member"
    ))
);
try { //thrift出错直接抛出异常需要捕获 
    $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    $ret = true;
}
catch (Exception $e) { //抛出异常返回false 
    echo $e;
    $ret = false;
}
/* {{{ 生成INI需要之user_setting,usergroup_setting
 */
//$table = __TABLE2_NAME;
//$rowkey = 'user_setting';
//$mutations = array(
    //new Mutation( array(
        //'column' => 'config:ini',
        //'value'  => '{"user":{"monitoradmin":{"mail_type":"1""email":"monitoradmin@madhouse-inc.com"}}' 
    //)),
    //new Mutation( array(
        //'column' => 'config:ini',
        //'value'  => '{"user_group":{"monitoradmin":["monitoradmin"]}}'
    //))
//);
//try { //thrift出错直接抛出异常需要捕获 
    //$GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
    //$ret = true;
//}
//catch (Exception $e) { //抛出异常返回false 
    //echo $e;
    //$ret = false;
//}
/* }}} */
if ($ret) {
    echo "Database create successfully, sample data add ok!\r\n";
}

/* {{{ 默认监控模板，也存在hosts表
 */
$table = __TABLE10_NAME;
$templateData=getTemplate();
for ($i=1; $i<sizeof($templateData); $i++) {
    list($host,$status,$disable_until,$error,$available,$errors_from,$lastaccess,$snmp_disable_until,$snmp_available,$snmp_errors_from,$snmp_error,$jmx_disable_until,$jmx_available,$jmx_errors_from,$jmx_error,$name,$flags,$templateid) = $templateData[$i];
    $rowkey=$host;
    echo "add template:${host}\n";
    $mutations = array(
        new Mutation( array(
            'column' => "info:status",
            'value'  => $status 
        )),
        new Mutation( array(
            'column' => "info:disable_until",
            'value'  => $disable_until 
        )),
        new Mutation( array(
            'column' => "info:error",
            'value'  => $error 
        )),
        new Mutation( array(
            'column' => "info:available",
            'value'  => $available 
        )),
        new Mutation( array(
            'column' => "info:errors_from",
            'value'  => $errors_from 
        )),
        new Mutation( array(
            'column' => "info:lastaccess",
            'value'  => $lastaccess 
        )),
        new Mutation( array(
            'column' => "info:snmp_disable_until",
            'value'  => $snmp_disable_until 
        )),
        new Mutation( array(
            'column' => "info:snmp_available",
            'value'  => $snmp_available 
        )),
        new Mutation( array(
            'column' => "info:snmp_errors_from",
            'value'  => $snmp_errors_from 
        )),
        new Mutation( array(
            'column' => "info:snmp_error",
            'value'  => $snmp_error 
        )),
        new Mutation( array(
            'column' => "info:jmx_disable_until",
            'value'  => $jmx_disable_until 
        )),
        new Mutation( array(
            'column' => "info:jmx_available",
            'value'  => $jmx_available 
        )),
        new Mutation( array(
            'column' => "info:jmx_errors_from",
            'value'  => $jmx_errors_from 
        )),
        new Mutation( array(
            'column' => "info:jmx_error",
            'value'  => $jmx_error 
        )),
        new Mutation( array(
            'column' => "info:name",
            'value'  => $name 
        )),
        new Mutation( array(
            'column' => "info:flags",
            'value'  => $flags 
        )),
        new Mutation( array(
            'column' => "info:templateid",
            'value'  => $templateid 
        ))
    );
    try { //thrift出错直接抛出异常需要捕获 
        $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
        $ret = true;
    }
    catch (Exception $e) { //抛出异常返回false 
        echo $e;
        $ret = false;
    }
}
/* }}} */


closeMdb();


/**
 *@brief 打开Mdb连接 
 *@return 
 */
function openMdb($host, $port, $sendtimeout, $recvtimeout) {
    global $module_name;
    try {
        $socket = new TSocket($host, $port);
        $socket->setSendTimeout($sendtimeout); // 2 seconds
        $socket->setRecvTimeout($recvtimeout); // 2 seconds
        $GLOBALS['mdb_transport'] = new TBufferedTransport($socket);
        $protocol = new TBinaryProtocol($GLOBALS['mdb_transport']);
        $GLOBALS['mdb_client'] = new HbaseClient($protocol);
        $GLOBALS['mdb_transport']->open();
    } catch (Exception $e) {
        //SaveSysLog("[$module_name][open mdb error,check mdb server addr and whether mdb table integrity!]",2);
        print_r($e);
        exit(0);
    }
}

/**
 *@brief 关闭Mdb连接 
 *@return 
 */
function closeMdb() {
    if (isset($GLOBALS['mdb_transport'])) {
        $GLOBALS['mdb_transport']->close();
        unset($GLOBALS['mdb_client']);
        unset($GLOBALS['mdb_transport']);
    }
}
?>
