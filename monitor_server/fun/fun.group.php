<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.group.php
  +----------------------------------------------------------------------+
  | Comment:关于组处理的函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-20 18:59:33
  +----------------------------------------------------------------------+
 */

/**
 *@brief 获取服务器隶属的服务器组
 *@param $srv 服务器
 *return 返回所在的服务器组数组，空返回空数组
 */
function getServerGroup($host) {
    global $_CONFIG;
    foreach ($_CONFIG['server_list'] as $serv_type=>$Srvs) {
        $srvs=(array)explode(',',$Srvs);
        if (in_array($host,$srvs) && !in_array($host,(array)$Host[$host]['belongGroup'])) {
            $Host[$host]['belongGroup'][]=str_replace('type_','',$serv_type);
        }
    }
    return (array)$Host[$host]['belongGroup'];
}

/**
 *@brief 获取服务器组的报警设置
 *@param $sev_gp 服务器组
 *@return 返回服务器组报警设置的成员用户组的数组
 */
function getServergroupAlarmInfo($sev_gp) {
    global $_CONFIG;
    list($mail_type,$usergroupArr,)=explode('#',$_CONFIG['server_group'][$sev_gp]);
    $usergroupArr=explode('|',$usergroupArr);
    return array($mail_type,$usergroupArr);
}

/**
 * @brief 检查服务器是否属于自定义组 
 * @param $srv 服务器id
 * @return 如果是自定义组，返回多个自定义组的数组，否则返回false
 */
function belongCustomizeGroup($srv) {
    global $cust_monitored_servers;
    foreach ((array)$cust_monitored_servers as $cust_group_name => $cust_servers) {
        if (in_array($srv, $cust_servers) && !in_array($srv, (array)$belongCustGroup)) {
            $belongCustGroup[] = $cust_group_name;
        }
    }
    return empty($belongCustGroup) ?false: $belongCustGroup;
}

/**
 *@brief 获取服务器的监控类型,自动配置会配置到默认组，即获取该服务器属于哪些默认组
 *@param 主机名
 *@return 监控类型数组
 */
function getServerMonitorType($srv) {
    global $_CONFIG,$module_name;
    $ret_type=array();
    foreach ((array)$_CONFIG['server_list'] as $st=>$manySrvs) {
        $srvs=explode(',',$manySrvs);
        if (in_array($srv, (array)$srvs)) {
            $st=str_replace('type_', '', $st);
            if (!in_array($st, (array)$ret_type) && is_numeric($st)) { // 自定义组不是数字的,如type_group1
                $ret_type[]=intval($st);
            }
        }
    }
    if (empty($ret_type)) {
        SaveSysLog("[$module_name][not a customize group server]",4);
        return false;
    } else {
        SaveSysLog("[$module_name][ret_type".join(',', $ret_type)."]",5);
        return $ret_type;
    }
}
?>
