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
        //print_r(array_keys($itemArr));
        //监控点名    key  采集间隔 保存天数    类型    监控集
        $arr=[];
        foreach (array_keys($itemArr) as $itm) {
            $itemInfo = $GLOBALS['mdb_client']->getRow(__MDB_TAB_ITEMS, $itm);
            $columns=$itemInfo[0]->columns;
            $name=$columns['info:name']->value;
            $arr[]=array($name,"2","3","4","5","6","7");
        }

        echo json_encode($arr);
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;

}
