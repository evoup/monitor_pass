<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.host.php
  +----------------------------------------------------------------------+
  | Comment:主机状态信息的函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-16 13:03:18
  +----------------------------------------------------------------------+
 */

/**
 *@brief 获取主机在线状态
 */
function hostIsOnline($host) {
    global $module_name,$down_over_time;
    try {
        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, "lt|$host", 'event:item');
        $val = $arr[0]->value;
        $current_time=time();
        if ($current_time-$val>$down_over_time) {
            SaveSysLog("[$module_name][determineDownHosts:$host hit][current:$current_time][lastonline:$val][has over down_over_time:$down_over_time]",4);
            return false;
        }
    } catch (Exception $e) {
    }
    return true;
}

/**
 *@brief 再次检查以及过滤n个主机是否在线,以防hbase读写问题造成的异常误报
 *@return 返回宕机的数组
 */
function determineDownHosts($dw_arr) {
    $ret = array();
    $dw_arr = array_unique((array)$dw_arr);
    $dw_arr = array_filter((array)$dw_arr);
    foreach ((array)$dw_arr as $down_srv) {
        if (hostIsOnline($down_srv)) { // 确保不误报
            continue;
        }
        // 将宕机事件也记录在needfix中,以实现宕机恢复提示
        eventAdd(1, __EVCODE997W, $down_srv, sprintf("Server: %s down!", $down_srv)); 
        saveEventDetail($down_srv,__MONITOR_TYPE_GENERIC,__EVCODE997W); // 存事件代码 
        $ret[]=$down_srv;
    }
    return $ret;
}
?>
