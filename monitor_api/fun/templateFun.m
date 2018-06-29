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
        $setItemArr=getSetItemMap();
        $templateSetArr=getTemplateSetMap();
        $templateTriggerArr=getTriggerMap();
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
                                   $templateArr[]=array($template,sizeof($setsArr[$hostid]),$itemsUnderTemplate,sizeof($templateTriggerArr[$templateid]),$templateid);
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


function getTemplateSetMap() {
    $c = fsockopen(__REDIS_HOST, __REDIS_PORT, $errCode, $errStr, 5);
    $rawCommand = "get key2\r\n";
    fwrite($c, $rawCommand);
    $rawResponse = fgets($c);
    $rawResponse = fgets($c);
        $arr=json_decode($rawResponse);
        foreach ($arr as $templateid=>$val) {
            if ($templateid>=10001 && $templateid<=10104) {
                $newArr[$templateid]=$val; //val是setIds
            }
        }
        return $newArr;
}


function getSetItemMap() {
    $c = fsockopen(__REDIS_HOST, __REDIS_PORT, $errCode, $errStr, 5);
    $rawCommand = "get key3\r\n";
    fwrite($c, $rawCommand);
    $rawResponse = fgets($c);
    $rawResponse = fgets($c);
        $arr=json_decode($rawResponse);
        $setItemArr=[];
        foreach ($arr as $itemid=>$setIdsInfo) {
            foreach ($setIdsInfo as $setId) {
                $setItemArr[$setId][]=$itemid;
            }
        }
        return $setItemArr;
}


function getTriggerMap() {
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
    return $templateTriggers;
}
