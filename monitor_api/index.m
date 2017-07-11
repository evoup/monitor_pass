<?php
/*
  +----------------------------------------------------------------------+
  | Name:index            
  +----------------------------------------------------------------------+
  | Comment:mms入口PHP脚本      
  +----------------------------------------------------------------------+
  | Author:evoup evoex@126.com   
  +----------------------------------------------------------------------+
  | Created:2011-02-22 10:41:44                                      
  +----------------------------------------------------------------------+
  | Last-Modified: 2013-03-28 18:31:32
  +----------------------------------------------------------------------+
 */
define(__API_ROOT,    dirname(__FILE__).'/');
define(__THRIFT_ROOT, dirname(__FILE__).'/thrift');
//error_reporting(E_ALL | E_STRICT);
error_reporting(0);
$conf = parse_ini_file(dirname(__FILE__).'/conf/api.conf'); //配置文件 
$hadoopConf = parse_ini_file(dirname(__FILE__).'/conf/hadoop.conf',true); //hbase有关信息配置 

/* {{{ 载入常数
 */
include_once(__API_ROOT.'inc/const.m');
/* }}} */

/* {{{ 基础函数
 */
include_once(__API_ROOT.'fun/common.m');
include_once(__API_ROOT.'fun/base.m');
include_once(__API_ROOT.'fun/mdbFun.m');
include_once(__API_ROOT.'lib/Predis.php');
include_once(__API_ROOT.'fun/mq.m');
/* }}} */

/* {{{ 初始化(载入配置等)
 * $GLOBALS['debugLevel'],debug级别
 * $GLOBALS['debugOutput']
 * $GLOBALS['timeNow']
 * $GLOBALS['timeDesc']
 */
include_once(__API_ROOT.'modules/initApi.m');
/* }}} */

/* {{{ 分析请求,生成全局数组,加载需要的函数
 * $GLOBALS['prefix'], 前缀,与services相关
 * $GLOBALS['postData'], post数据
 * $GLOBALS['serviceName'], 服务名称
 * $GLOBALS['operation'], 操作名
 * $GLOBALS['selector']
 * $GLOBALS['protocolVer'],请求协议版本
 * $GLOBALS['filterStart'],结果过滤相关信息
 * $GLOBALS['filterCount'],结果过滤相关信息
 * $GLOBALS['filterFields'],结果过滤相关信息
 */
ob_start();
include_once(__API_ROOT.'modules/parseRequest.m');
/* }}} */

/* {{{ api运行
 * $GLOBALS['httpStatus'], REST return
 * $GLOBALS['outputContent'], REST output
 */
apiRun();
/* }}} */
