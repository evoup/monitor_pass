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
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_SINGLE)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        $row=$GLOBALS['rowKey'];
        list($table_name,$start_row,$family) = array(__MDB_TAB_ITEMS, '', array('info'));
        $itemInfo = $GLOBALS['mdb_client']->getRow($table_name, $row);
        //print_r($itemInfo);
        $colInfo=$itemInfo[0]->columns;
        $type=$colInfo['info:type']->value;
        $data_type=$colInfo['info:data_type']->value;
        $name=$colInfo['info:name']->value;
        $interval=$colInfo['info:delay']->value;
        $desc=$colInfo["info:description"]->value;
        $key=$colInfo["info:key_"]->value;
        $unit=$colInfo["info:units"]->value;
        $multiply=$colInfo["info:multiplier"]->value;
        $history=$colInfo["info:history"]->value;
        $value_type=$colInfo["info:value_type"]->value;
        $delta=$colInfo["info:delta"]->value;
        //print_r($colInfo);
        $arr=array(
            'name'=>$name,
            'type'=>$type,
            'data_type'=>$data_type,
            'key'=>htmlspecialchars($key),
            'unit'=>$unit,
            'multiply'=>$multiply,
            'interval'=>$interval,
            'desc'=>$desc,
            'history'=>$history,
            'value_type'=>$value_type,
            'delta'=>$delta
        );
        echo json_encode($arr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
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
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_TEMPLATE)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        $templateId=$GLOBALS['rowKey'];
        // 根据templateId查下面的监控项
        list($table_name,$start_row,$family) = array(__MDB_TAB_ITEMS, '', array('info'));
        $setsArr=null;
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            $itemArr=[];
            while (true) {
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $item = $TRowResult->row;
                    $column = $TRowResult->columns;
                    foreach ($column as $family_column=>$Tcell) {
                        if (strstr($family_column, "info:hostid")) {
                            if ($Tcell->value==$templateId) {
                                $itemArr[$item]=1;
                            }
                        }
                    }
                }
            }
            $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
        } catch (Exception $e) {
            $err = true;
        }
        //监控点名    key  采集间隔 保存天数    类型    监控集
        $arr=[];
        foreach (array_keys($itemArr) as $itm) {
            $itemInfo = $GLOBALS['mdb_client']->getRow(__MDB_TAB_ITEMS, $itm);
            $row=$itemInfo[0]->row;
            $columns=$itemInfo[0]->columns;
            $name=$columns['info:name']->value;
            $key=$columns['info:key_']->value;
            $history=$columns['info:history']->value;
            $trends=$columns['info:trends']->value;
            $type=$columns['info:type']->value;
            $delay=$columns['info:delay']->value;
            if ($type==0) {
                $type="监控客户端检查";
            } else if($type==1) {
                $type="snmp代理检查";
            }
            foreach(array_keys($columns) as $col) {
                if (strstr($col,"info:setid")) {
                    $arr[]=array($name,$key,$delay,$history,$type,getSetName($col),$row);
                    break;
                }
            }
        }

        echo json_encode($arr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;

}

/**
 *获取监控集的名字
 */
function getSetName($colName) {
    list($table_name,$start_row,$family) = array(__MDB_TAB_SETS, '', array('info'));
    try {
        $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
        $itemArr=[];
        $setName="";
        while (true) {
            $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
            if ($get_arr == null) break;
            foreach ( $get_arr as $TRowResult ) {
                $item = $TRowResult->row;
                $column = $TRowResult->columns;
                foreach ($column as $family_column=>$Tcell) {
                    if ($family_column==$colName) {
                        $setName=$Tcell->value;
                        break 2;
                    }
                }
            }
        }
        $GLOBALS['mdb_client']->scannerClose($scanner); //关闭scanner 
    } catch (Exception $e) {
        $err = true;
    }
    return $setName;
}
