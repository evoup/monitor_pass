<?php
/*
  +----------------------------------------------------------------------+
  | Name: datacollectorFun.m
  +----------------------------------------------------------------------+
  | Comment: 处理数据收集器的函数
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

case(__OPERATION_CREATE):
    if ($GLOBALS['selector'] == __SELECTOR_SINGLE && $_SERVER['REQUEST_METHOD'] == 'POST') { 
        $valid_key = array('interface', 'memberServers'); //合法的POST的key
        $dataCollector = $GLOBALS['rowKey'];
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;

case(__OPERATION_READ): //查询操作 
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_MASS)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部数据收集器
        list($table_name,$start_row,$family) = array(__MDB_TAB_DATACOLLECTORS, '', array('info')); //从row的起点开始 
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            $datacollectorArr = [];
            while (true) { //TODO 这里可能会发生超时，需要加时限 
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $datacollector = $TRowResult->row; //以模板名为rowkey 
                    $column = $TRowResult->columns;
                    $datacollectorArr[]=array($datacollector, $column['info:hostNum']->value, $column['info:itemNum']->value, 1);
                }
            }
            $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
        } catch (Exception $e) {
            $err = true;
        }
        echo json_encode($datacollectorArr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_MASSMEMBER)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部可添加成员服务器，因为如果添加到其他数据收集器了，这里不能显示
        $hosts=array(
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host2"=>array("blabla","blabla","host1","blabla"),
            "host3"=>array("blabla","blabla","host1","blabla"),
            "host4"=>array("blabla","blabla","host1","blabla"),
            "host5"=>array("blabla","blabla","host1","blabla"),
            "host6"=>array("blabla","blabla","host1","blabla"),
            "host7"=>array("blabla","blabla","host1","blabla"),
            "host8"=>array("blabla","blabla","host1","blabla"),
            "host9"=>array("blabla","blabla","host1","blabla"),
            "host10"=>array("blabla","blabla","host1","blabla"),
            "host11"=>array("blabla","blabla","host1","blabla"),
            "host12"=>array("blabla","blabla","host1","blabla"),
            "host13"=>array("blabla","blabla","host1","blabla"),
            "host14"=>array("blabla","blabla","host1","blabla"),
            "host15"=>array("blabla","blabla","host1","blabla"),
            "host16"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host1"=>array("blabla","blabla","host1","blabla"),
            "host2"=>array("blabla","blabla","host2","blabla")
        );
        echo json_encode($hosts);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;
}
