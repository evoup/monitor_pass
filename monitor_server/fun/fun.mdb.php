<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.mdb.php
  +----------------------------------------------------------------------+
  | Comment:Mdb操作函数
  +----------------------------------------------------------------------+
  | Author:evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-20 15:34:26
  +----------------------------------------------------------------------+
 */

/**
 *@brief 打开Mdb连接 
 *@param $hosts 服务器IP:PORT数组
 *@param $sendtimeout 发送超时 
 *@param $recvtimeout 接收超时
 *@return 
 */
function openMdb($hosts, $sendtimeout, $recvtimeout) {
    global $module_name,$general_conf;
    $try=0;
    while ($try<10) {
        try {
            if (empty($hosts) || !@is_array($hosts)) {
                throw new Exception("no db servers!");
            }
            foreach ($hosts as $serverInfo) {
                list($masterHost,$masterPort)=explode(':',$serverInfo);
                $dbHosts[]=$masterHost;
                $dbPorts[]=$masterPort;
            }
            $socket = new TSocketPool($dbHosts, $dbPorts);
            $socket->setSendTimeout($sendtimeout); // 2 seconds
            $socket->setRecvTimeout($recvtimeout); // 2 seconds
            $GLOBALS['mdb_transport'] = new TBufferedTransport($socket);
            $protocol = new TBinaryProtocol($GLOBALS['mdb_transport']);
            $GLOBALS['mdb_client'] = new HbaseClient($protocol);
            $GLOBALS['mdb_transport']->open();
            // 当前连接的host
            $connectedHost=$socket->getHost();
            $connectedPort=$socket->getPort();
            SaveSysLog("[$module_name][mdb connected:{$connectedHost}({$connectedPort})]",2,$general_conf['scan_log_facility'],$general_conf['scan_log_level']);
            return true;
        } catch (Exception $e) {
            SaveSysLog("[$module_name][open mdb error for {$try} times,check mdb server addr and whether mdb table integrity!]",2,$general_conf['scan_log_facility'],$general_conf['scan_log_level']);
        }
        $try++;
    }
}

/**
 *@brief 关闭Mdb连接 
 *@return 
 */
function closeMdb() {
    if (isset($GLOBALS['mdb_transport'])) {
        $GLOBALS['mdb_transport']->close();
        unset($GLOBALS['mdb_client']);
        unset($GLOBALS['mdb_transport']);
    }
}

/**
 *@brief 将thrift的scannerGet函数得到的tRowResult对象数组转为自定义的Cell二维数组,再将多个Cell组成的数组返回
 *@return $ret_arr 以array('row'=>$row,'column'=>$recordArray)构成的cell为元素的数组
 */
function makeCells($record) {
    $resultArray = array();
    $ret_arr = array();
    foreach ($record as $TRowResult) {
        $column = $TRowResult->columns;
        foreach ($column as $family_column=>$cell) {
            $recordArray[$family_column] = $cell;
        }
        $ret_arr = array('row'=>$TRowResult->row, 'column'=>$recordArray);
    }
    return ($ret_arr);
}

/**
 *@brief 根据ini配置文件中的全部服务器名,更新服务器表，供其它表做为server列参考值
 *数据最后的格式举例: 表__MDB_TAB_SERVERNAME 列族servername,列name,rowkey为00001 value为服务器名
 ,''''''''''''''''''''''''''''|
 |  (表)__MDB_TAB_SERVERNAME  |
 |''''''''''''''''''''''''''''|
 |  (列族)servername:         |
 |''''''''''''''''''''''''''''|
 |  (列)name                  |
 '''''''''''''''''''''''''''''|
 |  (值)server??             .....rowkey:<服务器id(5位整数,不足补零，取值范围00001~99999)>
 `'''''''''''''''''''''''''''''
 *@return 
 */
function mdbUpdateTableSrv() {
    global $module_name;
    $all_srv = getAllConfSrv(); // 获取全部ini配置文件中的服务器名 
    $all_server_num = count($all_srv); // 统计总数 
    /*{{{ 从数据库取出已经保存的服务器名
     */
    list($table, $start_scan, $end_scan, $column_family) = array(__MDB_TAB_SERVERNAME, "00000", "99999", "servername:");

    // 查询rowkey从00000到99999对应的服务器value
    $scanner = $GLOBALS['mdb_client']->scannerOpenWithStop( $table, $start_scan ,$end_scan , array($column_family) );
    do {
        $record = $GLOBALS['mdb_client']->scannerGet($scanner); // 得到tRowResult构成的数组 
        $cells = makeCells($record);
        !empty($cells) && $resultArray[]=$cells; // 转换为row column构成的数组 
    } while ($record!=NULL);
    $max_id = count($resultArray); // id从0开始，则判断记录数再+1做为新记录的rowkey
    SaveSysLog("[$module_name][hbase table:{$table} record num:{$max_id}]",4);
    /*}}}*/
    foreach ((array)$resultArray as $cell ) {
        $server_name = $cell['column']['servername:name']->value;
        $stored_srvs[] = $server_name;
    }
    $target_srv = empty($stored_srvs) ?$all_srv :array_diff($all_srv, $stored_srvs); // 获取没有存的服务器名 
    /*{{{ 将未存的服务器入库
     */
    if (!empty($target_srv)) {
        $rowkey = $max_id;
        $col = "servername:name";
        foreach ($target_srv as $server_name) { // TODO 这里递增的方法要换掉！而且要去掉这个表 
            $rowkey = str_pad($rowkey+1, 5, "0", STR_PAD_LEFT); // id增加1,rowkey没有自增方法，只能自己加 
            mdb_set($table, $col, $rowkey, $server_name);
        }
    }
    /*}}}*/
}

/**
 *@brief 存上次服务器上传的时间
 *@param $serv_name 主机名
 *@param $serv_type 监控类型
 */
function mdbSetAliveServerList($serv_name, $serv_type) {
    global $module_name, $mdb_host, $mdb_port, $mdb_sendtimeout, $mdb_recvtimeout;
    $fun_name = __FUNCTION__;
    SaveSysLog("[$module_name][$fun_name][serv_name:$serv_name]",4); // TODO 调试信息太多，没问题去掉一些 
    $cust_group_name = belongCustomizeGroup($serv_name);
    if (false === $cust_group_name) { // 默认组 
        $row_keys[]=sprintf(__KEY_ALIVESRV, $serv_type);
        SaveSysLog("[$module_name][$fun_name][is default group serv]",4);
    } else { // 自定义组,可能对应多个
        foreach ($cust_group_name as $group) {
            $row_keys[]=sprintf(__KEY_ALIVESRV, $group);
        }
        $row_keys[]=sprintf(__KEY_ALIVESRV, $serv_type); // 默认组的也设置 
        SaveSysLog("[$module_name][$fun_name][is customize group serv]",4);
    }
    SaveSysLog("[$module_name][$fun_name][row_keys:$row_key".join('|',$row_keys)."]",4);
    foreach ($row_keys as $row_key) {
        try {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $row_key, __MDB_COL_EVENT);
        } catch (Exception $e) {
            SaveSysLog("[$module_name][$fun_name][got alivesrv fail!][$e]",4);
            return;
        } 
        SaveSysLog("[$module_name][$fun_name][row_key:$row_key][arr".serialize($arr)."]",4);
        $serv_list = array_filter(array_unique(explode("|", $arr[0]->value)));
        SaveSysLog("[$module_name][$fun_name][serv_list:".serialize($serv_list),4);
        if (!in_array($serv_name, $serv_list)) {
            $serv_list[] = $serv_name;
            try {
                $arrIp = $GLOBALS['mdb_client']->get(__MDB_TAB_HOST, $serv_name, 'info:ip');
                if (empty($arrIp[0]->value)) {
                    mdb_set(__MDB_TAB_HOST, "info:ip", $serv_name, getIp()); // 第一次进来记录ip 
                    //writeMq($serv_name,__HOST_STATUS_NEWADD);
                    SaveSysLog("[$module_name][$fun_name][add new server {$serv_name} to serv list]",4);
                } else {
                    /* >> don not write mq!在线的队列需要到恢复时再写! << */
                    SaveSysLog("[$module_name][$fun_name][already has this server in hostlist]",4);
                }
            } catch (Exception $e) {
                SaveSysLog("[$module_name][$fun_name][get ip fail cause:$e]",4);
            }
        }
        $serv_list_str = join("|", $serv_list);
        if (!empty($serv_list_str)) {
            SaveSysLog("[$module_name][$fun_name][row_key:$row_key][serv_list_str:$serv_list_str]",4); 
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serv_list_str);
        }
    }
}

/**
 *@brief 设置mdb中指定表指定列的rowkey对应的value(仅限一个mutation的封装)
 *@param $table 表名
 *@param $column_name 列名（格式列族:名字）
 *@param $rowkey 行键
 *@param $value 值
 */
function mdb_set($table, $column_name, $rowkey,$value) {
    $mutations = array(
        new Mutation( array(
            'column' => $column_name,
            'value'  => $value 
        ) )
    );

    try { // thrift出错直接抛出异常需要捕获 
        $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
        $ret = true;
    }
    catch (Exception $e) { // 抛出异常返回false 
        return false;
    }
    return ($ret);
}


/**
 * @brief 处理down掉的监控点/组 
 * @param $alive_srvs 存活的服务器数组
 * @param $group_type 组类型(默认组还是自定义组)
 * @param $rest 是否已经按照服务器存活列表mdbProcessDown过了，剩余不在存活列表的处理
 * @return 全部点down掉则返回false 否则返回当掉的点和当掉的组构成的数组 
 *       【宕机判断流程】
 *   _________________________
 *  |  检查存活列表中默认组   |  // mdbProcessDown($alive_servers,__GROUP_TYPE_DEFAULT);
 *   `''''''''''''''''''''''''
 *            . | .
 *   __________ ._____________
 *  |  检查存活列表中自定义组 |  // mdbProcessDown($alive_cust_servers,__GROUP_TYPE_CUSTOMIZE);
 *   `''''''''''''''''''''''''
 *            . | .
 *   __________ .______________________________
 *  |  剩下配置文件中的减去存活的，再检查一次  |  // mdbProcessDown(NULL,NULL,true); 
 *   `'''''''''''''''''''''''''''''''''''''''''
 */
function mdbProcessDown($alive_srvs, $group_type,$rest=false) {
    global $monitored_servers, $cust_monitored_servers, $mail_from, $mail_to_caution, $mail_cc_caution, $module_name, $alarm_interval, $server_list, $down_over_time;
    $fun_name = __FUNCTION__;
    SaveSysLog("[$module_name][monitored_servers:".json_encode($monitored_servers)."]",4);
    SaveSysLog("[$module_name][cust_monitored_servers:".json_encode($cust_monitored_servers)."]",4);
    SaveSysLog("[$module_name][server_list:".json_encode($server_list)."]",4);
    /*{{{不在监控存活列表内的处理
     */
    if ($rest) {
        foreach ($server_list as $srvs) { // 读取全部配置文件里的服务器名到all_servers数组 
            foreach (explode(',', $srvs) as $srv) {
                if (!in_array($srv,(array)$all_servers)) {
                    $all_servers[] = $srv;
                }
            }
        }

        /* 从all_servers中减去运行中的服务器，就是全部down的服务器 */ 
        // XXX 现在是先报上次存活列表中当掉的服，再报没有在runed_servers数组中存在的服，是否优化?
        $rest_down_machine = array_diff($all_servers, (array)$_SERVER['runed_servers']); 
        foreach ($rest_down_machine as $machine) {
            $downed_servers[] = $machine;
        }
        SaveSysLog("[$module_name][downed_serversy:".serialize($downed_servers)."]",4); 
        return ($downed_servers); 
    }
    /*}}}*/
    if (empty($alive_srvs)) {
        /*{{{监控存活列表空，没有存活的监控点
         */
        $group_name = $group_type===__GROUP_TYPE_DEFAULT?"default":"customize";
        $ev_code  = __EVCODE999W;
        $subject  = "GROUP $group_name:WARNING!!";
        $subject .= " (event code:$ev_code)";
        $content  = "All server of $group_name group downed? check local network and configuration! ";
        $interval_key = $group_type===__GROUP_TYPE_DEFAULT?__KEY_INTERVAL_DEFAULT_GP_ALLDOWN:__KEY_INTERVAL_CUST_GP_ALLDOWN;
        $interval_key = sprintf($interval_key, $server, $ev_code); // 发送间隔的row_key 
        $b_mail = mdbPassInterval($interval_key, isset($alarm_interval['all_default_gp_down']) ?$alarm_interval['all_default_gp_down'] :3600);  // 如配置按配置否则发送间隔1小时 
        if ($b_mail) {
            // mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time()).$content, $ev_code);  // FIXME 这部分先注释掉，全部做完再加一个开关 
        }
        SaveSysLog("[$module_name][$fun_name][send mail][event_code:$ev_code]:[group $group_name no alive server at all!]",4);
        return false;
        /*}}}*/
    } else {
        /*{{{有存活的监控点
         */
        /*{{{获取down掉的监控点
         */
        $downed_servers = array();
        foreach ($alive_srvs as $mon_type => $srv_group) { // 遍历当前存活服务器 
            $srv_group = explode('|',$srv_group); // 得到该监控类型下的服务器组 
            $srv = array_filter($srv_group);
            if ($group_type==__GROUP_TYPE_DEFAULT) { // 默认组 
                foreach ($srv as $server) { // 也有可能不在本监控组，但是在其他监控组里有存活的，for定义一个服务器到n个组的fix,判断服务器信息是否存在
                    $row_key = sprintf(__KEY_LASTTIME, $server);
                    $res = "";
                    $tried = 0;
                    while (empty($res) && $tried<=1) {
                        // 重连机制
                        try {
                            $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
                            $res = $res[0]->columns;
                            $res = $res[__MDB_COL_EVENT]->value; // 获取value 
                        } catch (Exception $e) {
                        }
                        $tried++;
                    }
                    if (empty($res)) {
                        $tmpArr = array_diff($monitored_servers[$mon_type],$srv); // 得到某类型下down掉的几台机器 key=>监控类型 value=>服务器名 
                        foreach ($tempArr as $dwn) {
                            !in_array($downed_servers) && $downed_servers[] = $dwn;
                        }
                        SaveSysLog("[$module_name][downed_servers:".serialize($downed_servers)."]",4); 
                    } else {
                        foreach (array_values($srv) as $server) {
                            if (!in_array($server, (array)$_SERVER['runed_servers']) && !belongCustomizeGroup($server)) {
                                $_SERVER['runed_servers'][] = $server; // runned_servers为目前运行的服务器,给processDown来判断哪些机器当机
                            }
                        }
                    }
                }
            } elseif ($group_type==__GROUP_TYPE_CUSTOMIZE) { // 自定义组 
                foreach ($srv as $server) { // 也有可能不在本监控组，但是在其他监控组里有存活的，for定义一个服务器到n个组的fix,判断服务器信息是否存在
                    $row_key=sprintf(__KEY_LASTTIME, $server);
                    $res="";
                    $tried=0;
                    while (empty($res) && $tried<=1) {
                        // 重连机制
                        try {
                            $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
                            $res = $res[0]->columns;
                            $lasttime = $res[__MDB_COL_EVENT]->value; // 获取value 
                        } catch (Exception $e) {
                        }
                        $tried++;
                    }
                    if (empty($lasttime)) { // 判定为down 
                        $downed_servers[]=array_diff($cust_monitored_servers["$mon_type"],$srv); // 得到某类型下down掉的几台机器 key=>监控类型 value=>服务器名 
                        SaveSysLog("[$module_name][downed_servers:".serialize($downed_servers)."]",4); 
                    } else {
                        $now = time();
                        $b_not_overtime = $now-$lasttime<=$down_over_time ?true :false; // 是否超时 
                        SaveSysLog("[$module_name][fun.$fun_name][rowkey:$row_key][lasttime:$lasttime][now:$now][not_overtime:$b_not_overtime]",4);
                        if (!$b_not_overtime) {
                            !in_array($server,$downed_servers) && $downed_servers[]=$server;
                        }
                        foreach (array_values($srv) as $server) { // XXX runed_servers考虑删除，这块写的比较乱！ 
                            if (!in_array($server,(array)$_SERVER['runed_servers']) && belongCustomizeGroup($server) &&
                                $b_not_overtime) {
                                    $_SERVER['runed_servers'][] = $server;
                                }
                        }
                    }
                }
            }
        }
        /*}}}*/
        return (array($downed_servers, $downed_groups)); 
        /*}}}*/
    }
}

/**
 * @brief 是否经过一定时间的间隔
 * @param $interval_row_key 上次执行的时间记录key
 * @param $want_intverval_time 需要实现间隔的时间秒数
 * @return 如果超时一定时间(可以再发邮件)则返回true，否则没有在这段时间间隔里就返回false
 */
function mdbPassInterval($lasttime_row_key, $want_intverval_time) {
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $lasttime_row_key, array(__MDB_COL_EVENT));
        $res = $res[0]->columns;
        $last_time = $res[__MDB_COL_EVENT]->value; // 获取value 
    } catch (Exception $e) {
    }
    if (empty($last_time)) { // 没有取到上次保存在cache里的时间戳,视为为已超时
        $Ret = true;
    } else { // 取到间隔信息，判断是否已经超过间隔
        $Ret = time()-$last_time>=$want_intverval_time ?true :false;
    }
    if ($Ret) {
        // 如果已经超时则重设上次保存时间为当前时间 
        mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $lasttime_row_key, time());
    }
    return ($Ret);
}


/**
 * @brief 从存活服务器列表里删除指定类型的服务器
 * @param $serv_name 服务器名
 * @param $serv_type 类型
 * @return 
 */
function mdbRemoveFromAliveServerList($serv_name,$serv_type) {
    global $module_name;
    $fun_name = __FUNCTION__;
    SaveSysLog("[$module_name][fun.$fun_name][ready remove follow server from aliveServerList:][serv_name:$serv_name][serv_type:$serv_type]",3);
    // 取出指定监控类型的全部存活服务器
    $row_key = sprintf(__KEY_ALIVESRV, $serv_type);
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
        $res = $res[0]->columns;
        $alive_serv_list = $res[__MDB_COL_EVENT]->value; // 获取value 
        SaveSysLog("[$module_name][fun.$fun_name][get alive serv list:$alive_serv_list]",4);
    } catch (Exception $e) {
    }
    $alive_serv_list = explode("|",$alive_serv_list);
    foreach ($alive_serv_list as $node_key => $node_val) { // 遍历servtype存活列表
        if ($serv_name==$node_val) { // 如果找到则移除
            unset($alive_serv_list[$node_key]);
            SaveSysLog("[$module_name][fun.$fun_name][remove ".$alive_serv_list[$node_key]."]",4);
        }
    }
    $mdb_value = implode("|",$alive_serv_list);
    SaveSysLog("[$module_name][fun.$fun_name][remove it!then reconstrust alive serv list:$mdb_value]",4);
    $res = mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $mdb_value);
    if (!$res) {
        SaveSysLog("[$module_name][fun.$fun_name][mdb query failed!]",3);
        return false;
    } else {
        SaveSysLog("[$module_name][fun.$fun_name][mdb query ok!]",4);
        return true;
    }
}

/**
 *@brief 更新全局服务器列表 for 客户端上传时直接加入
 *@param $srv 当前服务器(如果不再全局服务器列表则加入)
 *@param $srv_type 服务器的类型
 */
function mdbUpdateSrvList($srv ,$srv_type) { 
    global $module_name;
    $fun_name = __FUNCTION__;
    /*取出MDB中所有服务器*/
    $row_key = __KEY_ALLSRV; // 所有服务器的key 
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVERNAME, $row_key, array(__MDB_COL_SERVERNAME_ALL));
    } catch (Exception $e) {
        SaveSysLog("[$module_name][getRowWithColumns error][$e]", 2);
    }
    $res = $res[0]->columns;
    $res = $res[__MDB_COL_SERVERNAME_ALL]->value; // 获取value 
    if (empty($res)) {
        SaveSysLog("[$module_name][$fun_name][no authenticated server now,global server list is empty]", 2);
        // XXX 以下代码要删除
        //doExit("empty servername:all"); // 建库时未防止获取到空数据判断其为第一次添加，默认输入-1代表没有数据，此处应判断为异常退出
    }
    $globalSrvList = array_filter(explode('|', $res)); // 得到全部服务器数组 
    SaveSysLog("[$module_name][$fun_name][number of globalSrvList:".sizeof($globalSrvList)."]", 5);
    /* {{{ 发现有新的服务器加入全局服务器列表
     */
    if (!in_array($srv, $globalSrvList)) { 
        $globalSrvList[] = $srv;
        asort($globalSrvList); // 按字母排序 
        $remove_key = array_search('-1', $globalSrvList);
        if ($remove_key) { // 如果有-1的删除 XXX 这里是否有必要置-1 
            unset($globalSrvList[$remove_key]);
        }
        $globalSrvList = implode('|', $globalSrvList);
        if (false === mdb_set(__MDB_TAB_SERVERNAME, __MDB_COL_SERVERNAME_ALL, $row_key, $globalSrvList)) { // 更新存储
            SaveSysLog("[$module_name][$fun_name][update global server list failed!]",2);
        } else {
            SaveSysLog("[$module_name][$fun_name][update global server ok!]",4);
        }
    }
    /* }}} */
    mdbUpdateSrvGroup($srv, $srv_type); // 自动配置到默认组 
}

/**
 *@brief 自动配置客户端为默认组监控对象 for 客户端上传时直接加入
 *@param $host 客户端主机名 
 *@param $type 上传的默认组类别
 *@return 成功返回true，失败返回false
 */
function mdbUpdateSrvGroup($host, $type) {
    global $module_name;
    SaveSysLog("[$module_name][$fun_name][host:$host]", 4);
    $fun_name = __FUNCTION__;
    $valid_types = array( // 合法的监控类型 
        __MONITOR_TYPE_GENERIC,
        __MONITOR_TYPE_MYSQL,
        __MONITOR_TYPE_SERVING,
        __MONITOR_TYPE_DAEMON,
        __MONITOR_TYPE_REPORT,
        __MONITOR_TYPE_MADN,
        __MONITOR_TYPE_HADOOP,
        __MONITOR_TYPE_SECURITY
    );
    if (!$host && !is_int($type) && !in_array($type, $valid_types)) {
        return false;
    }
    /* {{{ 获取该组下的所有服务器 
     */
    $row_key = sprintf(__KEY_SERVGROUP, $type); // 服务器组列表的key(此处为默认组,因为这是程序自动配置) 
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVERNAME, $row_key, array(__MDB_COL_SERVERNAME_ALL));
    } catch (Exception $e) {
        SaveSysLog("[$module_name][getRowWithColumns error][$e]", 2);
        return false;
    }
    $res = $res[0]->columns;
    $res = $res[__MDB_COL_SERVERNAME_ALL]->value; // 获取value 
    SaveSysLog("[$module_name][$fun_name][row_key:$row_key][$res]", 4);
    $serverNodes = array_filter(explode('|', $res)); // 得到该组下所有服务器数组 
    /* }}} */
    /* {{{ 发现有新的服务器加入该组
     */
    if (!in_array($host, $serverNodes)) { 
        $serverNodes[] = $host;
        asort($serverNodes); // 按字母排序 
        $serverNodes = implode('|', $serverNodes);
        if (false === mdb_set(__MDB_TAB_SERVERNAME, __MDB_COL_SERVERNAME_ALL, $row_key, $serverNodes)) { // 更新存储
            SaveSysLog("[$module_name][$fun_name][row_key:$row_key][update failed!]", 2);
        } else {
            SaveSysLog("[$module_name][$fun_name][row_key:$row_key][serverNodes".join(',',(array)$serverNodes)."][add host:$host][update ok!]", 4);
        }
    }
    /* }}} */
}

/**
 *@brief 保存主机状态for display
 *@param $host_name 主机
 *return 保存成功返回true，保存失败返回false
 */
function mdbSaveHostStatus($host_name) {
    global $module_name,$sub_module_name;
    list($row_key, $column_family ,$table_name) = array($host_name, 'info:', __MDB_TAB_HOST);
    $mutations = array(
        new Mutation( array(
            'column' => "{$column_family}status", // 状态这里先存up，扫描到down的时候再设置为down
            'value'  => __HOST_STATUS_UP  
        ) ),
        new Mutation( array(
            'column' => "{$column_family}last_upload", // 存上传时间(扫描的时候再存检查时间，并计算总计运行时间)
            'value'  => time() 
        ) )
    );
    try { // thrift出错直接抛出异常需要捕获
        $GLOBALS['mdb_client']->mutateRow($table_name, $row_key, $mutations);
    } catch (Exception $e) { // 抛出异常返回400
        SaveSysLog("[$module_name][$sub_module_name][mdb set host status failed!]", 2);
        return false;
    }
    return true;
}

/*
 *@brief 保存主机被检查的时间(for 统计主机总计在线时间)
 *@param $hosts 若干主机的一维数组(可以是一台，也可以是多台)
 *@param $is_down 保存上次检查的同时设置status状态为down(for display)
 */
function mdbSaveLastCheckTime($hosts, $is_down = false) {
    $hosts = array_filter((array)$hosts);
    if (empty($hosts)) {
        return false;
    }
    global $module_name;
    $sub_module_name = __FUNCTION__;
    $time = time();
    foreach ($hosts as $host_name) {
        $online_time = mdbGetHostSummaryUpTime($host_name); // 获取保存前的总计在线时间 
        $lastdown_time = mdbGetHostLastDownTime($host_name); // 获取上次down机时间 
        $lastcheck_time = mdbGetHostLastCheckTime($host_name); // 获取上次检查时间 
        SaveSysLog("[$module_name][$sub_module_name][host:$host_name][staus:$is_down][online_time:$online_time][last_down_time:$lastdown_time][lastcheck_time:$lastcheck_time]", 3);
        if (false===$online_time || false===$lastdown_time || false===$lastcheck_time) {
            continue; // 获取异常跳过 
        }
        list($row_key, $column_family, $table_name) = array($host_name, 'info:', __MDB_TAB_HOST);
        switch ($is_down) {
        case(true): // 如果检查发现宕机 
            $mutations = array(
                new Mutation( array(
                    'column' => "{$column_family}last_check", // 存检查时间
                    'value'  => $time 
                ) ),
                new Mutation( array(
                    'column' => "{$column_family}status", // 存down机状态
                    'value'  => __HOST_STATUS_DOWN 
                ) ),
                new Mutation( array(
                    'column' => "{$column_family}last_down", // 存上次down机时间
                    'value'  => $time 
                ) )
            );
            SaveSysLog("[$module_name][$sub_module_name][host:$host_name][staus:DOWN][last_check:$time][last_down:$time]", 3);
            break;
        default: // 如果检查发现在线 
            if (empty($online_time)) { // 没有在线时间，为初次记录，存在线时间为一秒 
                $mutations = array(
                    new Mutation( array(
                        'column' => "{$column_family}last_check", // 存检查时间
                        'value'  => $time 
                    ) ),
                    new Mutation( array(
                        'column' => "{$column_family}summary_uptime", // 存在线时间 
                        'value'  => 1 
                    ) )
                );
                SaveSysLog("[$module_name][$sub_module_name][host:$host_name][staus:UP][last_check:$time][last_down:$time][summary_uptime:1]", 3);
            } else { // 已有在线时间
                $add_time = $time - $lastcheck_time;
                SaveSysLog("[$module_name][$sub_module_name][host:$host_name][staus:UP has online time][check_time:$time][lastcheck_time:$lastcheck_time][add_time:$add_time]", 3);
                $summary_uptime_val = $online_time + $add_time;
                // 保存状态
                $mutations = array(
                    new Mutation( array(
                        'column' => "{$column_family}last_check", // 存检查时间
                        'value'  => $time 
                    ) ),
                    new Mutation( array(
                        'column' => "{$column_family}summary_uptime", // 存在线时间 
                        'value'  => $summary_uptime_val 
                    ) )
                );
                SaveSysLog("[$module_name][$sub_module_name][host:$host_name][staus:UP][online_time:$online_time][add_time:$add_time][summary_uptime:$summary_uptime_val]", 3);
            }
            break;
        }

        try { // thrift出错直接抛出异常需要捕获
            $GLOBALS['mdb_client']->mutateRow( $table_name, $row_key, $mutations );
        } catch (Exception $e) { // 抛出异常继续下一个
            SaveSysLog("[$module_name][$sub_module_name][mdb set lastcheck status failed!]", 2);
            continue;
        }
    }
    return true;
}

/**
 *@brief 获取主机总计在线时间
 *@param $host 主机名
 *@return 主机总计在线时间的秒数
 */
function mdbGetHostSummaryUpTime($host) {
    global $module_name,$sub_module_name;
    list($row_key, $column_family, $table_name) = array($host, 'info:', __MDB_TAB_HOST);
    $fam_col_name = $column_family."summary_uptime";
    try {
        $arr = $GLOBALS['mdb_client']->get($table_name, $row_key , $fam_col_name);
        $host_summary_uptime = $arr[0]->value;
    } catch (Exception $e) {
        return false; 
    }
    $host_summary_uptime = @intval($host_summary_uptime);
    return $host_summary_uptime;
}

/**
 *@brief 获取主机上次宕机时间
 *@param $host 主机名
 *@return 主机上次宕机的时间戳
 */
function mdbGetHostLastDownTime($host) {
    global $module_name,$sub_module_name;
    list($row_key, $column_family, $table_name) = array($host, 'info:', __MDB_TAB_HOST);
    $fam_col_name = $column_family."last_down";
    try {
        $arr = $GLOBALS['mdb_client']->get($table_name, $row_key , $fam_col_name);
        $lastdown_tm = $arr[0]->value;
    } catch (Exception $e) {
        return false; 
    }
    $lastdown_tm = @intval($lastdown_tm);
    return $lastdown_tm;
}

/**
 *@brief 获取主机上次检查时间
 *@param $host 主机名
 *@return 主机上次检查的时间戳
 */
function mdbGetHostLastCheckTime($host) {
    global $module_name,$sub_module_name;
    list($row_key, $column_family, $table_name) = array($host, 'info:', __MDB_TAB_HOST);
    $fam_col_name = $column_family."last_check";
    try {
        $arr = $GLOBALS['mdb_client']->get($table_name, $row_key , $fam_col_name);
        $lastcheck_tm = $arr[0]->value;
    } catch (Exception $e) {
        return false; 
    }
    $lastcheck_tm = @intval($lastcheck_tm);
    return $lastcheck_tm;
}

/**
 *@brief 获取明细监控选项,服务器监控项+服务器组监控项
 *@param $hst 主机名
 *@return 返回哪些事件号是被监控的数组,如果没有找到默认全部项目都监控则返回false
 */
function mdbGetHostMonDetailSetting($hst) {
    global $host_monitor_detail,$_CONFIG,$monitor_item_arr;

    if (in_array($hst, (array)array_keys((array)$host_monitor_detail))) { // 先获取服务器的监控项
        $monitoredItemStr = $host_monitor_detail[$hst];
        if (!empty($monitoredItemStr)) {
            $totalMonitorItem = (array)explode('|', $monitoredItemStr);
        }
    }

    if (false!==($custServGrp=belongCustomizeGroup($hst))) { // 再获取服务组的监控项
        foreach ($custServGrp as $custgrp) {
            $monstr=$_CONFIG['server_group'][$custgrp];
            list(,,$grpMonitem)=explode('#',$monstr);
            SaveSysLog("[mdbGetHostMonDetailSetting][monstr:$monstr][grpMonitem:$grpMonitem]",4);
            if (!empty($grpMonitem)) {
                $monArr=explode('|',$grpMonitem);
                $monCls=0;
                foreach($monArr as $mClassStr) {
                    SaveSysLog("[mdbGetHostMonDetailSetting][mClassStr:$mClassStr]",5);
                    $mClassArr=str_split($mClassStr);
                    SaveSysLog("[mdbGetHostMonDetailSetting][mClassArr:".join(',',$mClassArr)."]",5);
                    foreach ($mClassArr as $currentMonitemIdx=>$isMon) {
                        SaveSysLog("[mdbGetHostMonDetailSetting][monCls:$monCls][currentMonitemIdx:$currentMonitemIdx][isMon:$isMon]",5);
                        if ($isMon) {
                            $willMonitorItem=$monitor_item_arr[$monCls][$currentMonitemIdx];
                            SaveSysLog("[mdbGetHostMonDetailSetting][willMonitorItem:$willMonitorItem]",4);
                            if (!empty($willMonitorItem) && !in_array($willMonitorItem,$totalMonitorItem)) {
                                $totalMonitorItem[]=$willMonitorItem;
                                SaveSysLog("[mdbGetHostMonDetailSetting][monitoritem in host($hst) not exist,but is monitored in group:$willMonitorItem]",3);
                            }
                        }
                    }
                    $monCls++;
                }
            }
        }
    }
    // TODO 这里视全部没有勾选不监控的状况为全部监控，需要注意
    return empty($totalMonitorItem)?false:$totalMonitorItem;
}
?>
