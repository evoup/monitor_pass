<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/initApi.m                                         
  +----------------------------------------------------------------------+
  | Comment:初始化api,确定配置                                      
  +----------------------------------------------------------------------+
  | Author:Odin                                                      
  +----------------------------------------------------------------------+
  | Created:2011-02-23 10:44:39                                       
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-31 11:35:43
  +----------------------------------------------------------------------+
 */
$moduleName=basename(__FILE__);

//默认配置
$_uriHasVersion=true;  //uri中是否包含version信息
$_uriHasOperation=true;//uri中是否包含

//debug级别默认为1,可在各自模块单独修改
$_debugLevel=3;
$GLOBALS['debugLevel']=empty($_REQUEST['debug'])?3:(int)$_REQUEST['debug'];  //支持参数指定debug级别
$GLOBALS['debugOutput']=(isset($_REQUEST['debug_output']) && $_REQUEST['debug_output']==='1')?true:false;

//输出内容,是个数组
$GLOBALS['outputContent']=array();

//载入mdn
openMdb();

//设置一些常数
$GLOBALS['timeNow']=time();
//$GLOBALS['timeDesc']=2000000000-$GLOBALS['timeNow']; //时间逆序(越大的时间戳排在越前面)  

// redis
$single_redis_server = array(
    'host'     => $conf['redis_host'],
    'port'     => $conf['redis_port'],
    'database' => 15
);

$GLOBALS['redis_client'] = new Predis_Client($single_redis_server);

?>
