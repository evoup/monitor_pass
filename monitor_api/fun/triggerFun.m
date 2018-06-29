<?php
/*
  +----------------------------------------------------------------------+
  | Name: triggerFun.m
  +----------------------------------------------------------------------+
  | Comment: 处理触发器的函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
$GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; //默认返回400 
header("Content-type: application/json; charset=utf-8");


switch ($GLOBALS['operation']) {
case(__OPERATION_READ): //查询操作 
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_TEMPLATE)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        $templateId=$GLOBALS['rowKey'];
        // 根据templateId查下面的触发器
        $arr=getTriggers($templateId);
        // 只要几个项目，名字，表达式，是否启用，等级
        $newArr=[];
        foreach ($arr as $triggerInfo) {
            $newArr[]=array(
            $triggerInfo->triggerid,
            $triggerInfo->expression,
            $triggerInfo->description,
            $triggerInfo->url,
            $triggerInfo->status,
            $triggerInfo->value,
            $triggerInfo->priority,
            $triggerInfo->lastchange,
            $triggerInfo->comments,
            $triggerInfo->error,
            $triggerInfo->templateid,
            $triggerInfo->type,
            $triggerInfo->state,
            $triggerInfo->flags,
            $triggerInfo->hostid);
        }
        echo json_encode($newArr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;

}

function getTriggers($templateId) {
    $c = fsockopen(__REDIS_HOST, __REDIS_PORT, $errCode, $errStr, 5);
    $rawCommand = "get key6\r\n";
    fwrite($c, $rawCommand);
    $rawResponse = fgets($c);
    $rawResponse = fgets($c);
    $arr=json_decode($rawResponse);
    // hostid才是模板id
    $templateTriggers=[];
    foreach($arr as $triggerid => $triggerinfo) {
        $templateTriggers[$triggerinfo->hostid][]= $triggerinfo;
    }
    return $templateTriggers[$templateId];
}

