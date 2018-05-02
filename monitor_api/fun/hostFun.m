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
    case(__OPERATION_READ): //查询操作 
    if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
        if (!canAccess('read_usergroupList')) {
            $GLOBALS['httpStatus'] = __HTTPSTATUS_FORBIDDEN;
            return;
        }
        switch ($GLOBALS['selector']) {
        case(__SELECTOR_SINGLE):
            $GLOBALS['rowKey'] = urldecode($GLOBALS['rowKey']); 
                list($table_name,$row_key) = array(__MDB_TAB_HOSTS, $GLOBALS['rowKey']); //以用户组名为rowkey 
                try {
                    $res = $GLOBALS['mdb_client']->getRow($table_name, $row_key);
                } catch (Exception $e) {
                    $err = true;
                }
                $res = (array)$res[0]; //得到二维数组下标为row和columns 
                if (empty($res)) {
                    $err = true;
                } else {
                    $str = array( //组织用户组信息数据 
                        "hostname" => $GLOBALS['rowKey'],
                        "agent_interface"      => $res['columns']['info:agent_interface']->value,
                        "snmp_interface" => $res['columns']['info:snmp_interface']->value,
                        "jmx_interface" => $res['columns']['info:jmx_interface']->value,
                        "data_collector" => $res['columns']['info:data_collector']->value,
                        "template" => $res['columns']['info:template']->value,
                        "monitored" => $res['columns']['info:monitored']->value
                    );
                    echo json_encode($str);
                }
                $GLOBALS['httpStatus'] = __HTTPSTATUS_OK; //查询成功返回200 
                break;
        }
    }
    break;
}

?>
