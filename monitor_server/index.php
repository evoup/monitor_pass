<?php
/*
  +----------------------------------------------------------------------+
  | Name:index.php
  +----------------------------------------------------------------------+
  | Comment:监控服务端的入口
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-11 16:36:23
  +----------------------------------------------------------------------+
 */
$module_name = 'index';
date_default_timezone_set('PRC');
define(__PRGM_PATH, dirname(__FILE__));
chdir(dirname(__FILE__)); // for crontab设置路径 
include_once("inc/inc.server.php");
include_once("fun/fun.valid.php");
include_once("fun/fun.event.php");
include_once("fun/fun.common.php");
include_once("fun/fun.host.php");
include_once("fun/fun.group.php");
include_once("fun/fun.fs.php");
include_once("fun/fun.mdb.php");
include_once("fun/fun.save.php");
include_once("fun/fun.scan.php");
include_once(__THRIFT_ROOT.'/Thrift.php');
include_once(__THRIFT_ROOT.'/transport/TSocket.php');
include_once(__THRIFT_ROOT.'/transport/TSocketPool.php');
include_once(__THRIFT_ROOT.'/transport/TBufferedTransport.php');
include_once(__THRIFT_ROOT.'/protocol/TBinaryProtocol.php');
include_once(__THRIFT_ROOT.'/packages/Hbase/Hbase.php');
include_once("modules/server_class.php"); // 主要是一些监控的class 

SaveSysLog("[$module_name][debug_level:$debug_level]",4);

$module_name = 'index';

$runCli=substr(php_sapi_name(), 0, 3) == 'cli'?true:false;

if (!$runCli) 
{
    include_once("modules/mon_init.php"); // 每次作业前必须的初始化,含数据库的连接检查 
    switch($controller_type)
    {
    case('upload'): // 处理监控信息上传 
        SaveSysLog("[$module_name][branch upload]",4); 
        include_once("modules/upload.php");
        break;
    case('update'): // 提供客户端更新
        SaveSysLog("[$module_name][branch update]",4); 
        include_once("modules/update.php");
        break;
    case('testspeed_update'):
        SaveSysLog("[$module_name][branch testspeed conf]",4);
        include_once("modules/testspeed_update.php");
        break;
    }
} else {
    switch($argv[1]) 
    {
    case('daily'): // 发送每日事务信息邮件 
        include("modules/local_init.php");
        include_once("modules/mon_init.php");
        SaveSysLog("[$module_name][branch daily]",4); 
        include_once('modules/daily.php');
        break;
    case('scan'): // 处理收集到的监控信息 
        while (true)
        {
            include_once("fun/fun.engine.php");
            include_once('GPL/predis/Predis.php');
            include("modules/local_init.php");
            include("modules/mon_init.php");
            SaveSysLog("[$module_name][branch scan]",4); 
            /*{{{调用机房外的一个连通性测试脚本判断客户端能够达到
             */
            //SaveSysLog("[$module_name][check remote watchdog][url:$watchdog_url]",4);
            //$confirmStr=@file_get_contents($watchdog_url);
            //if (strstr("status:access monitor server ok",$confirmStr))
            //{
                //SaveSysLog("[$module_name][check remote watchdog][ok]",4);
            //} else {
                //SaveSysLog("[$module_name][check remote watchdog][fail]",4);
                // TODO 加警报
                //sleep(__SCAN_INTERVAL);
                //continue;
            //}
            /*}}}*/
            include('modules/scan.php');
            if ( !isset($testSpeedCounter) || $testSpeedCounter<40 ) { //每40回合进行一次测速统计
                $testSpeedCounter++;
            } else {
                include('modules/speed_count.php');
                $testSpeedCounter=0;
            }
            /* 释放扫描用到的全局/超级变量 */
            unset($_SERVER['nf'], $_SERVER['nf2'], $_SERVER['needfix'], $_SERVER['needfix_orig'], $_SERVER['runed_servers'], $_SERVER['downed'], $GLOBALS['downed_srv'], $GLOBALS['fix_event_desc'],$alive_servers,$GLOBALS['redis_client']);  
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            sleep(__SCAN_INTERVAL);
        }
        break;
    }
}

closeMdb(); // 作业完毕关闭MDB连接 
