<?php
/*
  +----------------------------------------------------------------------+
  | Name:inc/const.m              
  +----------------------------------------------------------------------+
  | Comment:测速 RESTful API const 
  +----------------------------------------------------------------------+
  | Author:yinjia evoex@126.com
  +----------------------------------------------------------------------+
  | Created:2011-02-22 10:30:48  
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-19 11:28:49
  +----------------------------------------------------------------------+
 */

/* 版本号 */
define('__VERSION','0.9');        //主版本号',代表主要功能分支
define('__SUBVERSION','r102');      //小版本号',即subversion版本号

/* {{{ mdn setting
 */
define('__MDB_HOST',        $conf['mdb_host']);
define('__MDB_PORT',        $conf['mdb_port']);
define('__MDB_SENDTIMEOUT', '20000');  //20000 seconds
define('__MDB_RECVTIMEOUT', '20000');  //20000 seconds

/* }}} */

/* {{{ services
 */
define('__SERVICE_SPEED', 'speed');
define('__SERVICE_TESTSPEED_SITE', 'testspeed_site');
/* }}} */

/* {{{ services
 */
define('__PREFIX_SPEED', 'speed');
define('__PREFIX_TESTSPEED_SITE', 'testspeed_site');
/* }}} */

/* {{{ operations
 */
define('__OPERATION_CREATE', 'create');
define('__OPERATION_READ',   'get');
define('__OPERATION_UPDATE', 'update');
define('__OPERATION_DELETE', 'delete');
/* }}} */

/* {{{ http status define
 */
define('__HTTPSTATUS_OK',                    200);
define('__HTTPSTATUS_CREATED',               201);
define('__HTTPSTATUS_NO_CONTENT',            204);
define('__HTTPSTATUS_RESET_CONTENT',         205);
define('__HTTPSTATUS_BAD_REQUEST',           400);
define('__HTTPSTATUS_UNAUTHORIZED',          401);
define('__HTTPSTATUS_FORBIDDEN',             403);
define('__HTTPSTATUS_NOT_FOUND',             404);
define('__HTTPSTATUS_METHOD_NOT_ALLOWED',    405);
define('__HTTPSTATUS_METHOD_CONFILICT',      409);
define('__HTTPSTATUS_INTERNAL_SERVER_ERROR', 500);
/* }}} */

/* {{{ selector
 */
define('__SELECTOR_SINGLE',   '@self');
define('__SELECTOR_MASS',     '@all');
define('__SELECTOR_GROUP',    '@group');
define('__SELECTOR_BATCH',    '@batch');
/* }}} */

/* {{{ syslog(fancility&level)
 */
if ( empty($conf['syslog_str']) ) {
    define('__SYSLOG_FACILITY_API', 'LOG_LOCAL2');
    define('__SYSLOG_LV_APILOG',    'LOG_DEBUG');
    define('__SYSLOG_LV_DEBUG',     'LOG_DEBUG');
} else {
    list($f,$l)=array_map('strtoupper',explode('.',$conf['syslog_str'],2));
    define('__SYSLOG_FACILITY_API', 'LOG_' . $f);
    define('__SYSLOG_LV_APILOG',    'LOG_' . $f);
    define('__SYSLOG_LV_DEBUG',     'LOG_' . $l);
}
/* }}} */

/* {{{ (mmsapi)MDB表 
 */
if (!$conf['debug']) {
    define('__MDB_TAB_TESTSPEED', 'monitor_testspeed'); //MDB的测速统计信息表 
    define('__MDB_TAB_TESTSPEED_HISTORY', 'monitor_testspeed_history'); //MDB的测速统计历史信息表
} else {
    define('__MDB_TAB_TESTSPEED', 'monitor_testspeed_beta'); // MDB的测速统计信息表 
    define('__MDB_TAB_TESTSPEED_HISTORY', 'monitor_testspeed_history_beta'); // MDB的测速统计历史信息表 
}
/* }}} */

/* {{{ (mmsapi)MDB的column 
 */
define('__MDB_COL_LOWESTSPEED',     'info:lspeed'); //配置文件的列 
define('__MDB_COL_HIGHESTSPEED',    'info:hspeed'); //配置文件的列 
define('__MDB_COL_URL',             'info:url'); //配置文件的列 
/* }}} */

define('__REDIS_TABLE_SPEED', 'monitorTestSpeed'); // redis表，保存测速数据
?>
