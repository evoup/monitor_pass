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
    if ( in_array($GLOBALS['selector'], array(__SELECTOR_MASS,__SELECTOR_MASSMEMBER)) && 
        $_SERVER['REQUEST_METHOD'] == 'GET') {  //查询全部 
        list($table_name,$start_row,$family) = array(__MDB_TAB_HOSTS, '', array('info')); //从row的起点开始 
        try {
            $scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row , $family);
            while (true) { //TODO 这里可能会发生超时，需要加时限 
                $get_arr = $GLOBALS['mdb_client']->scannerGet($scanner);
                if ($get_arr == null) break;
                foreach ( $get_arr as $TRowResult ) {
                    $template = $TRowResult->row; //以用户名为rowkey 
                    /* {{{ 取出实际用户名和电子邮件
                     */
                    $column = $TRowResult->columns;
                    foreach ($column as $family_column=>$Tcell) {
                        if (strstr("info:hostid")) {
                           $hostid=substr('info:hostid10011', 11);
                           if ($hostid>=10001 && $hostid<10104) {
                               if ($Tcell->value=="1") {
                                   echo $template;
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
        $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        return;
    }
    break;
}
