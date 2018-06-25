<?php
/*
  +----------------------------------------------------------------------+
  | Name: templateFun.m
  +----------------------------------------------------------------------+
  | Comment: 处理模板的函数
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
// 仅查询template
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_TEMPLATE_ALL)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        list($table_name,$start_row,$family) = array(__MDB_TAB_HOSTS, '', array('info')); //从row的起点开始 
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            $templateArr = [];
            while (true) { //TODO 这里可能会发生超时，需要加时限 
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $template = $TRowResult->row; //以模板名为rowkey 
                    /* {{{ 取出模板名字
                     */
                    $column = $TRowResult->columns;
                    $templateidInfo=$column['info:templateid'];
                    $templateid=$templateidInfo->value;
                    foreach ($column as $family_column=>$Tcell) {
                        if (strstr($family_column, "info:hostid")) {
                           $hostid=substr($family_column, 11);
                           if ($hostid>=10001 && $hostid<=10104) {
                               if ($Tcell->value=="1") {
                                   $itemsUnderTemplate = sizeof($templateItemMap[$hostid]);
                                   $templateArr[]=array($template,sizeof($setsArr[$hostid]),$itemsUnderTemplate,1,$templateid);
                               }
                           }
                        }
                    }
                    /* }}} */
                }
            }
            $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
        } catch (Exception $e) {
            $err = true;
        }
        echo json_encode($templateArr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
// 查询template和旗下的set和item
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_MASS)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        $templateSetArr=getTemplateSetMap();
        $setItemArr=getSetItemMap();
        $templateItemMap=[];
        //模板、集
        foreach($templateSetArr as $template => $sets) {
            $items=[];
            foreach($sets as $set) {
                $itemsOfSet = $setItemArr[$set];
                foreach($itemsOfSet as $item) {
                    if (!in_array($item, $templateItemMap[$template])) {
                        $templateItemMap[$template][]=$item;
                    }
                }
            }
        }
        //查询监控集
        list($table_name,$start_row,$family) = array(__MDB_TAB_SETS, '', array('info'));
        $setsArr=null;
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            while (true) {
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $set = $TRowResult->row; //以集名为rowkey 
                    $setsArr[$set]=$TRowResult->columns;
                }
            }
            $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
        } catch (Exception $e) {
            $err = true;
        }

        list($table_name,$start_row,$family) = array(__MDB_TAB_HOSTS, '', array('info')); //从row的起点开始 
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            $templateArr = [];
            while (true) { //TODO 这里可能会发生超时，需要加时限 
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $template = $TRowResult->row; //以模板名为rowkey 
                    /* {{{ 取出模板名字
                     */
                    $column = $TRowResult->columns;
                    $templateidInfo=$column['info:templateid'];
                    $templateid=$templateidInfo->value;
                    foreach ($column as $family_column=>$Tcell) {
                        if (strstr($family_column, "info:hostid")) {
                           $hostid=substr($family_column, 11);
                           if ($hostid>=10001 && $hostid<=10104) {
                               if ($Tcell->value=="1") {
                                   $itemsUnderTemplate = sizeof($templateItemMap[$hostid]);
                                   $templateArr[]=array($template,sizeof($setsArr[$hostid]),$itemsUnderTemplate,1,$templateid);
                               }
                           }
                        }
                    }
                    /* }}} */
                }
            }
            $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
        } catch (Exception $e) {
            $err = true;
        }
        echo json_encode($templateArr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;
}

/**
 * 返回以templated为key，setid为value的全部set
 */
function getTemplateSetMapOld() {
    list($table_name,$start_row,$family) = array(__MDB_TAB_SETS, '', array('info')); //从row的起点开始 
    $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
    $itemArr=[];
    while (true) {
        $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
        if ($get_arr == null) break;
        foreach ( $get_arr as $TRowResult ) {
            foreach(array_keys($TRowResult->columns) as $value) {
                if (strstr($value,"info:setid")) {
                    $setid=substr($value, 10);
                    $templateid=$TRowResult->row;
                    if ($templateid>=10001 && $templateid<=10104) {
                        //返回setid对应的item
                        $itemArr[$templateid][]=$setid;
                    }
                }
            }
        }
    }
    return $itemArr;
}

/**
 * 返回templateId对多个setId
 */
function getTemplateSetMap() {
    $single_redis_server = array(
        'host'     => __REDIS_HOST,
        'port'     => __REDIS_PORT
    );
    try {
        $GLOBALS['redis_client'] = new Predis_Client($single_redis_server);
        $value = $GLOBALS['redis_client']->get("key2");
        $arr=json_decode($value);
        foreach ($arr as $templateid=>$val) {
            if ($templateid>=10001 && $templateid<=10104) {
                $newArr[$templateid]=$val; //val是setIds
            }
        }
        return $newArr;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * 返回以setid为key，itemid为value的全部item
 */
function getSetItemMapOld() {
    list($table_name,$start_row,$family) = array(__MDB_TAB_ITEMS, '', array('info')); //从row的起点开始 
    $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
    $itemArr=[];
    while (true) {
        $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
        if ($get_arr == null) break;
        foreach ( $get_arr as $TRowResult ) {
            foreach(array_keys($TRowResult->columns) as $value) {
                if (strstr($value,"info:setid")) {
                    $mysetid=substr($value, 10);
                    //返回setid对应的item
                    $itemArr[$mysetid][]=$TRowResult->row;
                }
            }
        }
    }
    return $itemArr;
}

function getSetItemMap() {
    $single_redis_server = array(
        'host'     => __REDIS_HOST,
        'port'     => __REDIS_PORT
    );
    try {
        $GLOBALS['redis_client'] = new Predis_Client($single_redis_server);
        $value = $GLOBALS['redis_client']->get("key3");
        $arr=json_decode($value);
        $setItemArr=[];
        foreach ($arr as $itemid=>$setIdsInfo) {
            foreach ($setIdsInfo as $setId) {
                $setItemArr[$setId][]=$itemid;
            }
        }
        return $setItemArr;
    } catch (Exception $e) {
        return false;
    }
}
