<?php
/*
  +----------------------------------------------------------------------+
  | Name: fun/hostFun.m
  +----------------------------------------------------------------------+
  | Comment: 处理主机的项目
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
    // http://localhost:8004/mmsapi1.0/create/host/@self/usergp
    if ($GLOBALS['selector'] == __SELECTOR_SINGLE && $_SERVER['REQUEST_METHOD'] == 'POST') { 
        $valid_key = array('hostname', 'agent_interface', 'snmp_interface', 'jmx_interface', 'data_collector', 
        'template', 'monitored'); //合法的POST的key
        /* {{{ 上传数据检查 
         */
        /* 检查是否符合数据格式 */
        foreach ($valid_key as $host_key) {
            if (!in_array($host_key, array_keys($_POST))) { //检查参数是否传满，对少传判断为非法 
                $err = true;
            } else {
                $hosts[$host_key] = $_POST[$host_key];
            }
        }
        if (empty($hosts['hostname'])) {
            $err = true;
        }
        if ($err) {
            return;
        }
        $table_name = __MDB_TAB_HOSTS;
        $row_key = $hosts['hostname'];
        $mutations = array(
            new Mutation( array(
                'column' => "info:agent_interface", 
                'value'  => $host_setting['agent_interface'] 
            ) ),
            new mutation( array(
                'column' => "info:snmp_interface", 
                'value'  => $host_setting['snmp_interface'] 
            ) ),
            new mutation( array(
                'column' => "info:jmx_interface", 
                'value'  => $host_setting['jmx_interface'] 
            ) ),
            new mutation( array(
                'column' => "info:data_collector", 
                'value'  => $host_setting['data_collector'] 
            ) ),
            new mutation( array(
                'column' => "info:template", 
                'value'  => $host_setting['template'] 
            ) ),
            new mutation( array(
                'column' => "info:monitored", 
                'value'  => $host_setting['monitored'] 
            ) )
        );
        try { //thrift出错直接抛出异常需要捕获 
            $GLOBALS['mdb_client']->mutateRow( $table_name, $row_key, $mutations );
        } catch(Exception $e) { //抛出异常返回400 
            echo $e;
            $err = true;
        }
        if (!$err) { //没错则返回200 
            $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
        }
    }
    break;
}

?>
