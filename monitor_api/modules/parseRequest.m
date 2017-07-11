<?php
/*
  +----------------------------------------------------------------------+
  | Name: modules/parseRequest.m                                          |
  +----------------------------------------------------------------------+
  | Comment: 处理访问信息                                                 |
  +----------------------------------------------------------------------+
  | Author: Evoup                                                         |
  +----------------------------------------------------------------------+
  | Created: 2011-02-23 10:19:45                                          |
  +----------------------------------------------------------------------+
  | Last-Modified: 2013-03-29 10:41:11
  +----------------------------------------------------------------------+
*/
$moduleName=basename(__FILE__);

/* 读取uri信息 */
$arrPathInfo=parse_url($_SERVER['REQUEST_URI']);
DebugInfo("[{$arrPathInfo['path']}][start---------]",2);
$MmsQs=explode('/',$arrPathInfo['path']);
array_shift($MmsQs);        //数组第一项为空

/* {{{ 获取版本(如果需要的话)
 */
if ($_uriHasVersion) {
    $GLOBALS['protocolVer']=$MmsQs[0];
    array_shift($MmsQs);
} else {
    $GLOBALS['protocolVer']=__VERSION;
}
/* }}} */

//获取操作符
if ($_uriHasOperation) {
    $GLOBALS['operation']=$MmsQs[0];
    array_shift($MmsQs);
} else {    //为以后转为纯净的REST留点可能性
    //...
}

//获取service名称
$GLOBALS['serviceName']=strtolower($MmsQs[0]);
array_shift($MmsQs);

// selector
$GLOBALS['selector']=strtolower($MmsQs[0]);
array_shift($MmsQs);

//rowkey
$GLOBALS['rowKey']=empty($MmsQs[0])?null:$MmsQs[0];

//filter
$GLOBALS['filterFields']=isset($_GET['fields'])?explode(',',$_GET['fields']):array();    //用户自定义过滤字段
$GLOBALS['filterStart']=isset($_GET['start'])?$_GET['start']:null;  //起始id
$GLOBALS['filterCount']=isset($_GET['count'])?(int)$_GET['count']:null; //最大数字

//获取post数据
$GLOBALS['postData']=file_get_contents("php://input");

//加载相关函数
if (!empty($GLOBALS['serviceName'])) {
    switch($GLOBALS['serviceName']) {
    case __SERVICE_LOGIN:
        $GLOBALS['prefix']=__PREFIX_LOGIN;
        break; 
    case __SERVICE_SERVER:
        $GLOBALS['prefix']=__PREFIX_SERVER;
        break;
    case __SERVICE_SERVER_GROUP:
        $GLOBALS['prefix']=__PREFIX_SERVER_GROUP;
        break;
    case __SERVICE_STATUS:
        $GLOBALS['prefix']=__PREFIX_STATUS;
        break;
    case __SERVICE_EVENT:
        $GLOBALS['prefix']=__PREFIX_EVENT;
        break;
    case __SERVICE_EVENT_CAUTION:
        $GLOBALS['prefix']=__PREFIX_EVENT_CAUTION;
        break;
    case __SERVICE_EVENT_WARNING:
        $GLOBALS['prefix']=__PREFIX_EVENT_WARNING;
        break;
    case __SERVICE_EVENT_OK:
        $GLOBALS['prefix']=__PREFIX_EVENT_OK;
        break;
    case __SERVICE_MAILSETTING:
        $GLOBALS['prefix']=__PREFIX_MAILSETTING;
        break;
    case __SERVICE_ALARMSETTING:
        $GLOBALS['prefix']=__PREFIX_ALARMSETTING;
        break;
    case __SERVICE_USERGROUP:
        $GLOBALS['prefix']=__PREFIX_USERGROUP;
        break;
    case __SERVICE_USER:
        $GLOBALS['prefix']=__PREFIX_USER;
        break;
    case __SERVICE_LOG:
        $GLOBALS['prefix']=__PREFIX_LOG;
        break;
    case __SERVICE_MONITOR:
        $GLOBALS['prefix']=__PREFIX_MONITOR;
        break;
    case __SERVICE_MONITORITEM:
        $GLOBALS['prefix']=__PREFIX_MONITORITEM;
        break;
    case __SERVICE_EVENT_SETTING:
        $GLOBALS['prefix']=__PREFIX_EVENT_SETTING;
        break;
    case __SERVICE_GENERIC_SETTING:
        $GLOBALS['prefix'] =__PREFIX_GENERIC_SETTING;
        break;
    case __SERVICE_CLOUDVIEW:
        $GLOBALS['prefix'] =__PREFIX_CLOUDVIEW;
        break;
    case __SERVICE_MONENGINE:
        $GLOBALS['prefix'] =__PREFIX_MONENGINE;
        break;
    case __SERVICE_SCAN_SETTING:
        $GLOBALS['prefix']=__PREFIX_SCAN_SETTING;
        break;
    case __SERVICE_GRAPH:
        $GLOBALS['prefix']=__PREFIX_GRAPH;
        break;
    case __SERVICE_DETAIL_SETTING:
        $GLOBALS['prefix']=__PREFIX_DETAIL_SETTING;
        break;
    case __SERVICE_IP_SETTING:
        $GLOBALS['prefix']=__PREFIX_IP_SETTING;
        break;
    case __SERVICE_MDNDELIVER_SETTING:
        $GLOBALS['prefix']=__PREFIX_MDNDELIVER_SETTING;
        break;
    case __SERVICE_DISTRICT:
        $GLOBALS['prefix']=__PREFIX_DISTRICT;
        break;
    case __SERVICE_CARRIER:
        $GLOBALS['prefix']=__PREFIX_CARRIER;
        break;
    case __SERVICE_EDGESERVER_STATUS:
        $GLOBALS['prefix']=__PREFIX_EDGESERVER_STATUS;
        break;
    case __SERVICE_TESTSPEED_SITE:
        $GLOBALS['prefix']=__PREFIX_TESTSPEED_SITE;
        break;
    case __SERVICE_TESTSPEED:
        $GLOBALS['prefix']=__PREFIX_TESTSPEED;
        break;
    case __SERVICE_METRIC:
        $GLOBALS['prefix']=__PREFIX_METRIC;
        break;
    case __SERVICE_PROCESS_DELETE_SERVER:
        $GLOBALS['prefix']=__PREFIX_PROCESS_DELETE_SERVER;
        break;
    case __SERVICE_TIME:
        $GLOBALS['prefix']=__PREFIX_TIME;
        break;
    case __SERVICE_DOCS:
        $GLOBALS['prefix']=__PREFIX_DOCS;
        break;
    case __SERVICE_DOWNLOADS:
        $GLOBALS['prefix']=__PREFIX_DOWNLOADS;
        break;
    case __SERVICE_GET_DOWNLOAD_FILE:
        $GLOBALS['prefix']=__PREFIX_GET_DOWNLOAD_FILE;
        break;
    default:
        break;
    }
    DebugInfo("[globals_prefix:".$GLOBALS['prefix']."]",2);
    $funcFile=__API_ROOT.'fun/'.$GLOBALS['prefix'].'Fun.m';    //这里要求各个service的命名需要规范
    if (file_exists($funcFile)) {
        DebugInfo("[$moduleName] [$funcFile][include]",2);
        /*{{{ (mssapi)监控配置的读入
         */
        if (in_array($GLOBALS['prefix'],array(__SERVICE_STATUS)) && $GLOBALS['selector']==__SELECTOR_MDB) {
            DebugInfo("[$moduleName][get mdb status][funcFile:$funcFile]",2);
        } else {
            $mdb_host=explode('|',$conf['mdb_host']); // 获取thrift服务器组
            //载入mdn
            openMdb($mdb_host);
            //设置一些常数
            $GLOBALS['timeNow']=time();
            try{
                $row_key=__KEY_INIDATA; //完整配置文件的rowkey 
                $res=$GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER,$row_key,array(__MDB_COL_CONFIG_INI));
                $res=$res[0]->columns;
                $res=$res[__MDB_COL_CONFIG_INI]->value; //获取value
                if(!$res) {
                    DebugInfo("[$moduleName][getRowWithColumns mdbconf error]",3);
                    $GLOBALS['httpStatus'] = __HTTPSTATUS_INTERNAL_SERVER_ERROR;
                    exit(); //此为异常，建表的时候输入了默认数据，这里采取直接退出 
                } else {
                    DebugInfo("[$moduleName][got mdb configure file]", 3);
                    //配置文件读取
                    $_CONFIG=@parse_ini_string($res,true);
                    unset($res);
                }
            } catch(Exception $e) {
                DebugInfo("[$moduleName][getRowWithColumns mdbconf error][$e]",2);
                $GLOBALS['httpStatus'] = __HTTPSTATUS_INTERNAL_SERVER_ERROR;
                exit(); //此为异常，建表的时候输入了默认数据，这里采取直接退出 
            }
        }
        /* }}} */
        include_once($funcFile);
    } else {
        DebugInfo("[$moduleName] [$funcFile][file_not_exists]",2);
    }
} else {
    DebugInfo("[$moduleName] [none_service]",2);
}

DebugInfo("[$moduleName] [protocolVer:{$GLOBALS['protocolVer']}]-[operation:{$GLOBALS['operation']}]-[serviceName:{$GLOBALS['serviceName']}]-[selector:{$GLOBALS['selector']}]-[rowKey:{$GLOBALS['rowKey']}]",2);
?>
