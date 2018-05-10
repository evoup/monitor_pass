<?php
/*
  +----------------------------------------------------------------------+
  | Name: itemFun.m
  +----------------------------------------------------------------------+
  | Comment: 处理监控点的函数
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
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_SET)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        //监控点名    key  采集间隔 保存天数    类型    监控集
        $arr = array(
            array("1","2","3","4","5","6","7"),
            array("1","2","3","4","5","6","7")
        );
        echo json_encode($arr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;

}
