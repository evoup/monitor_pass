<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/parseRequest.m                                          |
  +----------------------------------------------------------------------+
  | Comment:处理访问信息                                                 |
  +----------------------------------------------------------------------+
  | Author:Odin                                                          |
  +----------------------------------------------------------------------+
  | Created:2011-02-23 10:19:45                                          |
  +----------------------------------------------------------------------+
  | Last-Modified:2011-04-09 16:31:39                                    |
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
    case __SERVICE_SPEED:
        $GLOBALS['prefix'] = __PREFIX_SPEED;
        break;
    case __SERVICE_TESTSPEED_SITE:
        $GLOBALS['prefix'] = __PREFIX_TESTSPEED_SITE;
        break;
    default:
        break;
    }
    DebugInfo("[globals_prefix:".$GLOBALS['prefix']."]",2);
    if ( !empty($conf['services']) && !in_array($GLOBALS['serviceName'], explode(',',$conf['services'])) ) {
        DebugInfo("[service:{$GLOBALS['serviceName']} not allow in the conf]",2);
        header('HTTP/1.1 405 Method Not Allowed');
        exit();
    }
    $funcFile=__API_ROOT.'fun/'.$GLOBALS['prefix'].'Fun.m';    //这里要求各个service的命名需要规范
    if (file_exists($funcFile)) {
        DebugInfo("[$moduleName] [$funcFile][include]",2);
        include_once($funcFile);
    } else {
        DebugInfo("[$moduleName] [$funcFile][file_not_exists]",2);
    }
} else {
    DebugInfo("[$moduleName] [none_service]",2);
}

DebugInfo("[$moduleName] [protocolVer:{$GLOBALS['protocolVer']}]-[operation:{$GLOBALS['operation']}]-[serviceName:{$GLOBALS['serviceName']}]-[selector:{$GLOBALS['selector']}]-[rowKey:{$GLOBALS['rowKey']}]",2);
?>
