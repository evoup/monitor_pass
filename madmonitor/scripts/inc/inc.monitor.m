<?php
define(__SVNVER, '5135'); // SVN的版本号 
define(__VERSION,'0.10.1.'.__SVNVER);

//process setting
define(__PROC_ROOT,     '/services/monitor_deal');
define(__RUN_SUBPATH,   'run');
define(__CONF_SUBPATH,  'conf');
define(__STATUS_SUBPATH,'status');
define(__WORK_SUBPATH,  'work');
define(__BAK_SUBPATH,   'source_bak');
define(__PROC_LIFE,     3600);
define(__SLEEP,         10);

define(__SOURCE_DEF_FILETYPE,'J');//J:bzip2, Z:gzip
define(__SOURCE_SPLIT_TAG1,  ':');
define(__SOURCE_SPLIT_TAG2,  '#');
define(__SOURCE_SPLIT_TAG3,  '|');
define(__SOURCE_SPLIT_TAG4,  ',');

define(__FLAG_SERVER, 1);
define(__FLAG_MYSQL,  2);
define(__FLAG_SERV,   3);
define(__FLAG_MANAGE, 4);
define(__FLAG_REPORT, 5);
define(__FLAG_MADN,   6);
define(__FLAG_HADOOP, 7);
define(__FLAG_SECURITY, 11);

define(__LOGTAG_READ,    'MadRead');
define(__LOGTAG_DELIVER, 'MadDeliver');
define(__LOGTAG_PF,      'pf_monitor');
define(__LOGTAG_LOG,     'access_monitor');

$hostname=php_uname('n');
$array_conf=Array(
    'server_name'   => $hostname, 
    'proc_life'     => __PROC_LIFE,
    'sleep'         => __SLEEP,
    'upload_url'    => '',
    'upload_host'   => '172.18.0.30',
    'upload_port'   => '80',
    'upload_version'=> 'monitor_server2r1_1',
    'upload_suffix' => 'm1',
    'send_host'     => '172.18.0.1',
    'send_port'     => '8090'
);

/* {{{ 安全监控
 */
define('__SSH_AUTH_STATUS_LOG_NOT_EXIST', -1);
define('__SSH_AUTH_STATUS_OK', 1);
define('__SSH_AUTH_STATUS_ABNORMAL', 0);
define('__INTERFACE_SAFE_STATUS_OK', 1);
define('__INTERFACE_SAFE_STATUS_ABNORMAL', 0);
/* }}} */
?>
