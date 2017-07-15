<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.event.php
  +----------------------------------------------------------------------+
  | Comment:处理事件
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-03 16:59:05
  +----------------------------------------------------------------------+
 */

/**
 * @brief 发送警报事件到待处理列表
 * @param $ev_lev 事件等级 0为黄色警报 1为红色警报,供邮件发送时候提供呈现颜色的区别 
 * @param $ev_code 事件代码
 * @param $srv_name 服务器名
 * @param $msg 事件消息
 * @param $ev_time 事件发生时间
 * @return 
 */
function eventAdd($ev_lev, $ev_code, $srv_name, $msg, $ev_time=false) {
    global $module_name,$version,$snapshot_url_prefix;
    $fun_name = __FUNCTION__;
    $ev_code = sprintf("%04s",$ev_code); // 格式化为4位事件代码 
    if (!isset($_SERVER['needfix']) || !in_array($sv_code,$_SERVER['needfix'])) {
        $_SERVER['needfix'][] = $ev_code; // 激发的事件代码全部存到这个变量,供reloadNeedFixList()删除多余的事件代码 
    }
    SaveSysLog("[$module_name][fun.$fun_name][ev_lev:$ev_lev][ev_code:$ev_code][srv_name:$srv_name][msg:$msg]",3);
    if (false===$ev_time) {
        $ev_time = time();  // 默认为事件发生的时间 
    }

    /* 更新待处理事件列表 */
    $row_key = __KEY_NEEDFIX; // 待处理列表的key 
    $tbl = __MDB_TAB_SERVER;
    $col = __MDB_COL_EVENT;
    $need_fix_event = $GLOBALS['mdb_client']->getRowWithColumns($tbl, $row_key, array($col));
    $need_fix_event = $need_fix_event[0]->columns;
    $need_fix_event = $need_fix_event[__MDB_COL_EVENT]->value; // 获取待处理列表
    if (empty($need_fix_event)) { // 待处理列表空，直接加入事件代码
        SaveSysLog("[$module_name][fun.$fun_name][needfix list empty,just add new event]",4);
        $res = mdb_set($tbl, $col, $row_key,$ev_code);
        if (false===$res) {
            SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][mdb set failed!]",3);
        }
    } else {
        SaveSysLog("[$module_name][fun.$fun_name][needfix list has something...get them first]",4);
        // 待处理列表不为空，先取出待处理全部事件代码
        $nf_evcode = explode('|', $need_fix_event);
        foreach ($nf_evcode as $key => $value) { // 剔除空数组 
            if (empty($value)) {
                unset($nf_evcode[$key]);
            }
        }
        SaveSysLog("[$module_name][fun.$fun_name][current nf_evcode:".join(',',(array)$nf_evcode)."]",4); 
        // 如果没有加入待处理事件列表，先加入
        if (!in_array($ev_code, array_values($nf_evcode))) {
            $nf_evcode[] = $ev_code;
            asort($nf_evcode);
            $evcodes = implode('|', $nf_evcode);
            SaveSysLog("[add needfix list value:$evcodes]",4);
            $res = mdb_set($tbl, $col, $row_key, $evcodes);
        }
    }
    SaveSysLog("[$module_name][fun.$fun_name][update needfix list done!][nf_evcode:".join(',',(array)$nf_evcode)."]",3); 

    /* 更新该服务器事件 */
    makeMonitorEvent($srv_name, $ev_code, $ev_time); 
    $ev_num=substr($ev_code,0,3); // 前三位为事件号(如001) 
    $ev_lev=substr($ev_code,-1,1);// 第四位为事件等级(取值范围:n、w、c对应普通、注意、警报)
    $_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['event_on'] = true; // 得到添加服务器事件之后的服务器事件数组 
    $_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['event_time'] = $ev_time;
    $_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['mail_content'] = $msg;
    $_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['snapshot_link'] = "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$srv_name}&version={$version}' target=_BLANK>>>SNAPSHOT</a>";
    SaveSysLog("[$module_name][fun.$fun_name][ev_num:$ev_num][ev_lev:$ev_lev][srv_name:$srv_name][msg:".$_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['mail_content']."]",3); 
    return 1;
}

/**
 * @brief 存服务器待处理监控事件
 * 格式: key:nf事件代码 value:服务器1#上次产生该事件的时间|服务器2#上次产生该事件的时间 
 * 例如：nf0024 => server1#3454354|server2#3245345|
 * @param $srv 服务器名
 * @param $ev_c 事件代码
 * @param $ev_t 事件发生时间
 * @return 
 */
function makeMonitorEvent($srv, $ev_c, $ev_t) {
    global $module_name;
    $fun_name = __FUNCTION__;
    $tbl = __MDB_TAB_SERVER;
    $col = __MDB_COL_EVENT;
    $row_key = sprintf(__KEY_NF,$ev_c); // 待处理服务器事件的key 
    // 该事件是否存在，存在则取得超时信息
    $serv_event = $GLOBALS['mdb_client']->getRowWithColumns($tbl, $row_key, array($col));
    $serv_event = $serv_event[0]->columns;
    $serv_event = $serv_event[__MDB_COL_EVENT]->value; // 获取value 
    SaveSysLog("[$module_name][fun.$fun_name][current srv:$srv][event code:$ev_c][get][row_key:$row_key]",3);
    if (!empty($serv_event)) { // 待处理服务器事件存在，先取出该事件下的所有服务器和时间数据
        $serv_event = explode('|', $serv_event); // 取得例如server1#3454354 的服务器#超时信息的数组
        SaveSysLog("[$module_name][fun.$fun_name][serv_event]".join(',',(array)$serv_event),4);
        // 得到事件号和事件等级
        $ev_num = substr($ev_c,0,3); // 前三位为事件号(如001) 
        $ev_lev = substr($ev_c,-1,1);// 第四位为事件等级(取值范围:n、w、c对应普通、注意、警报)
        foreach ($serv_event as $serv_and_time) { // 便利此事件下全部服务器和事件数据
            if (!empty($serv_and_time)) { // 避免空元素
                list($srv_name, $srv_time) = explode('#', $serv_and_time);  // 取得服务器名，超时 
                $temp_srv_name[] = $srv_name;
                $temp_over_time[] = $ev_t-$srv_time>3600 ?$ev_t :$srv_time; // 如果当前事件时间大于了取出的时间戳3600秒，则使用当前事件的时间
            }
        }
        /* 重建(服务器名#事件发生时间) */
        if (!in_array($srv, (array)$temp_srv_name)) { // 加入当前待处理服务器事件 
            $temp_srv_name[] = $srv;
            $temp_over_time[] = time();
            SaveSysLog("[$module_name][fun.$fun_name][add new problem server]",4);
        }
        $final_server_overtime_arr = array_combine($temp_srv_name, $temp_over_time);
        foreach ($final_server_overtime_arr as $serv => $overtime) {
            $mdb_value[] = $serv."#".$overtime; // 得到重建后的 (服务器名#事件发生) 数组 
        }
        $mdb_value = implode('|', $mdb_value);
        SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][serv_list:$mdb_value]",3);
        $res = mdb_set($tbl,$col,$row_key,$mdb_value);
        if (false===$res) {
            SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][mdb reset server event(s) failed!]",3);
        } else {
            SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][mdb reset server event(s) ok!]",4);
        }
    } else { // 该事件为空，直接添加服务器事件
        SaveSysLog("[$module_name][fun.$fun_name][server event emypt,add new event]",4);
        $mdb_value = $srv."#".$ev_t;
        $res = mdb_set($tbl,$col,$row_key,$mdb_value); // 更新待处理服务器事件 
        if (false===$res) {
            SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][mdb set server event failed!]",3);
        } else {
            SaveSysLog("[$module_name][fun.$fun_name][$tbl][$col][$row_key][mdb set server event ok!]",4);
        }
        SaveSysLog("[$module_name][fun.$fun_name][mdb_value:$mdb_value]",4);
    }
}

/**
 * @brief 读取待处理事件列表 
 * @return 
 */
function readNeedFixList() {
    global $module_name;
    /* 取出待处理事件代码 */
    $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, __KEY_NEEDFIX, array(__MDB_COL_EVENT));
    $res = $res[0]->columns;
    $res = $res[__MDB_COL_EVENT]->value; // 获取value 
    if (empty($res)) {
        SaveSysLog("[$module_name][fun.readNeedFixList][no needfix list now]",4);
        return false;
    }
    $_SERVER['needfix_orig'] = array_unique(explode('|',$res));  // 保存到扫描前的待处理列表 
    /*{{{ 确保每个元素为事件
     */
    foreach ((array)$_SERVER['needfix_orig'] as $eventStr) {
        if (!isEventCode($eventStr)) {
            $removeItem[] = $eventStr;
        }
    }
    foreach ((array)$removeItem as $itm) { // 如果有不是事件代码的元素(TODO 找出原因)，移除非事件代码的元素 
        $idx = array_search($itm, $_SERVER['needfix_orig']);
        if ($idx===NULL || $idx===false) {
        } else {
            SaveSysLog("[$module_name][fun.readNeedFixList][Exception, reconstrut needfix key!".$_SERVER['needfix_orig'][$idx]."]",2);
            unset($_SERVER['needfix_orig'][$idx]);
            sort($_SERVER['needfix_orig']);
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, __KEY_NEEDFIX, join('|', $_SERVER['needfix_orig']));
        }
    }
    /*}}}*/
    /* 防止scan前没有待处理事件列表导致下面array_diff报错 */
    $_SERVER['needfix_orig'] =
        is_array($_SERVER['needfix_orig']) && !empty($_SERVER['needfix_orig'][0])
        ?$_SERVER['needfix_orig']
        :array();
    return;
}

/**
 *@brief 判断是否为事件代码
 *@return 为事件代码返回true，否则返回false
 */
function isEventCode($eventStr) {
    $isEventCode = true;
    $isEventCode && strlen($eventStr)!=4 && $isEventCode = false;
    $isEventCode && !is_numeric(substr($eventStr, 0, 3)) && $isEventCode = false;
    $isEventCode && !in_array(substr($eventStr, -1), array('c', 'w')) && $isEventCode = false;
    return $isEventCode;
}

/**
 * @brief 读取待处理的服务器事件 
 * @return 
 */
function readNeedfixServerEvent() {
    global $module_name;
    $fun_name = __FUNCTION__;
    $serverEvents = $_SERVER['needfix_orig']; // 得到全部待处理事件 
    if (empty($serverEvents)) {
        SaveSysLog("[$module_name][fun.$fun_name][no needfix server event now]",3);
        return false;
    } else {
        foreach ($serverEvents as $event_code) { // 取出全部服务器事件 
            $event_num = substr($event_code, 0, 3);
            $event_level = substr($event_code, -1, 1);
            $row_key = sprintf(__KEY_NF, $event_code); // 待处理列表的key 
            $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
            $res = $res[0]->columns;
            $res = $res[__MDB_COL_EVENT]->value; // 获取value 
            if ($res) {
                $server_eventtimes = explode("|", $res);
                foreach ($server_eventtimes as $srv_evtime) {
                    list($srv, $ev_time) = explode("#", $srv_evtime);

                    /* 记录下该服务器事件和发生事件 */
                    $_SERVER['nf'][$event_num][$event_level][$srv]['event_on'] = true; // [待处理][事件][等级][服务器][事件存在]
                    $_SERVER['nf'][$event_num][$event_level][$srv]['event_time'] = $ev_time; // [待处理][事件][等级][服务器][发生时间]
                }
            }
        }
    }
    SaveSysLog("[$module_name][fun.$fun_name][done]",4);
    return true;
}

/**
 * @brief 更新待处理事件列表,删除没有激发的事件代码
 * @return 
 */
function reloadNeedFixList() {
    global $module_name,$_EventDescArr,$version,$snapshot_url_prefix;
    $fun_name = __FUNCTION__;
    /*取出待处理事件代码*/
    $row_key = __KEY_NEEDFIX; // 待处理事件列表的key 
    $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
    $res = $res[0]->columns;
    $res = $res[__MDB_COL_EVENT]->value; // 获取value 
    if (!$res) {
        SaveSysLog("[$module_name][$fun_name][all alived servers are running with no problem!]",4);
        return true;
    }
    $need_fix_events = explode(__SOURCE_SPLIT_TAG3,$res);
    SaveSysLog("[$module_name][$fun_name][need_fix_events:".join(__SOURCE_SPLIT_TAG3,(array)$need_fix_events)."]",5);
    if (empty($_SERVER['needfix'])) { // 如果已经没有要处理的事件则清空待处理事件列表
        mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, "");
        SaveSysLog("[$module_name][$fun_name][nothing to be fixed,needfix_list is cleaned]",4);
        return true;
    } else {
        $_SERVER['needfix'] = array_unique($_SERVER['needfix']); 
    }
    SaveSysLog("[$module_name][$fun_name][needfix_orig".join('|', (array)$_SERVER['needfix_orig'])."]",4);
    SaveSysLog("[$module_name][$fun_name][needfix".join('|', $_SERVER['needfix'])."]",4);
    $fixed_events = array_diff((array)$_SERVER['needfix_orig'],$_SERVER['needfix']); // 得到已处理事件 
    foreach ($fixed_events as $event) { // XXX 里存在BUG（当事件代码对应的事件被解决，没有从needfix中删除） 
        // 2011年 9月 8日 星期四 18时43分06秒 这个BUG已经转到mail最后删除，须下次调整
        SaveSysLog("[$module_name][$fun_name]event:$event will be removed!",4);
        $row_key = sprintf(__KEY_NF, $event );
        // 取出nf对应的服务器,为其设置即时表的时间状态和历史表的解决记录
        try {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $row_key, "event:item");
            $arr = explode('|',$arr[0]->value);
            foreach ($arr as $srv_ts) {
                $serv = array_shift(explode('#',$srv_ts)); // TODO后面这个ts其实没有用过，准备移除 
                if (in_array($serv,(array)$GLOBALS['downed_srv'])) { // 排除已经宕机的，不宕机的才可能恢复
                    $has_event = true; // 事件还存在，只是目前宕机,通知下面不要删除掉对应的服务器事件 
                    continue;
                }
                /*
                 *写即时表状态为已解决！ 
                 */
                if (list($ev_start_timestamp, $ev_desc) = writeFixState($serv, $event)) {
                    /* {{{ 往历史表里写事件日志(历史表里只写解决了的事件)
                     */
                    saveHistoryEventDetail($serv, $event, $ev_start_timestamp);
                    /* }}} */
                    /* {{{ 发送恢复邮件BRANCH1
                     */
                    global $mail_from, $mail_to_caution, $mail_cc_caution, $alarm_interval;
                    $solved_key = sprintf(__KEY_SOLVED,$serv, $event);

                    $eventStartTm=getEventStartTm($serv,$event); // 获取事件的持续时间 
                    if (!empty($eventStartTm)) {
                        $durationTm=getDhms(time()-$eventStartTm);
                        $durationStr="Total duration:{$durationTm}.";
                    }

                    // 检查是否须要发送邮件
                    if ($alarm_interval['recover_notifiction'] && mdbPassInterval($solved_key, 3600) && !empty($serv)) {
                        $cust_gp_name = belongCustomizeGroup($serv);
                        $gp = false!=$cust_gp_name ?"[".join(',', $cust_gp_name)."]" :"[default group]"; // 对自定义的服务器报警邮件[组名] 
                        /* {{{ 对于端口问题事件，事件描述的port要放到标题
                         */
                        $addonSubject='';
                        if ($event==sprintf("%04s",__EVCODE006C)) {
                            $addonSubject=" {$tempArr[3]} port:{$tempArr[5]}"; //分别为服务名 port 端口号
                        }
                        /* }}} */
                        $subject = '-RESOLVED-'.$_EventDescArr["{$event}"]." $serv@$gp";
                        $content  = "$serv monitor event:".$_EventDescArr["{$event}"]."($event) has been resolved.<br>>>Event start at ".date("M-d-Y/H:i:s", $eventStartTm)." {$ev_desc}<br>";
                        $content .= $durationStr;
                        $content .= "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$serv}&version={$version}'>>>SNAPSHOT</a>";
                        mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content",$event,array(__ALARM_TARGET_SERVER,$serv));
                        if ($event==sprintf("%04s",__EVCODE997W)) {
                            writeMq($serv,__HOST_STATUS_UP);
                        } 
                    }
                    /* }}} */
                }
            }
        } catch (Exception $e) {
        }
        !$has_event && mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, ""); // 删除对应已处理的服务器事件 
        !$has_event && resetKeepWatchCount($serv,$event); // 已经恢复的事件，清零retry计数器 
        $has_event = false; // 重置
    }
    $needfix_list=implode('|',$_SERVER['needfix']);
    mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $needfix_list); // 删除对应已处理的服务器事件 
    SaveSysLog("[$module_name][$fun_name][needfix_list:".$needfix_list."]",4);
}

/**
 * @brief 对扫描后的变更进行服务器事件修改
 * @return 
 */
function changeNeedfixServerEvent() {
    global $module_name,$mail_from,$mail_to_caution,$mail_cc_caution,$_EventDescArr,$version,$snapshot_url_prefix;
    $fun_name = __FUNCTION__;
    if (!is_array($_SERVER['nf'])) { // 没有待处理直接退出 
        return;
    }
    $remove_server_events = array_diff_key_recursive($_SERVER['nf'],$_SERVER['nf2']); // 递归比较扫描前后的待处理事件，过滤得到需要删除掉的事件
    foreach ($remove_server_events as $ev_num => $ev_level) { 
        foreach ($ev_level as $ev_lev => $server) {
            foreach (array_keys($server) as $serv) {
                SaveSysLog("[$module_name][fun.$fun_name][follow server event has been fixed:nf$ev_num$ev_lev][$serv]",3);
                $row_key = sprintf(__KEY_NF, "$ev_num$ev_lev");
                $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
                $res = $res[0]->columns;
                $server_event=$res[__MDB_COL_EVENT]->value; // 获取value 
                /* {{{ 对比上次和本次扫描的服务器事件，以得到哪些事件被解决
                 */
                if (!empty($server_event)) {
                    $srv_and_time=explode('|',$server_event); 
                    foreach ($srv_and_time as $sv_and_tm) {
                        list($srv,$time)=explode('#',$sv_and_tm); 
                        //print_r($srv); // TODO 注意此处
                        if ($srv!=$serv || in_array($srv,(array)$GLOBALS['downed_srv'])) { // 排除不需要的,已经宕机的之前有问题的事件要保留！
                            $problem_serv_time[]="$srv#$time"; // 得到仍有问题的服务器事件 
                        } else { // 刚被解决的事件 
                            SaveSysLog("[$module_name][fun.$fun_name][remove this server event => ev_num:$ev_num ev_lev:$ev_lev serv:$serv]",4);
                            $sub_module_name = __FUNCTION__;
                            /*
                             *写即时表状态为已解决！ 
                             */
                            if (list($ev_start_timestamp, $ev_desc)=writeFixState($serv, "{$ev_num}{$ev_lev}")) {
                                /* {{{ 往历史表里写事件日志(历史表里只写解决了的事件)
                                 */
                                saveHistoryEventDetail($serv, "{$ev_num}{$ev_lev}", $ev_start_timestamp);
                                /* }}} */
                                /* {{{ 发送恢复邮件BRANCH2
                                 */
                                global $mail_from,$mail_to_caution,$mail_cc_caution,$alarm_interval;
                                $solved_key=sprintf(__KEY_SOLVED,$serv, "{$ev_num}{$ev_lev}");

                                $eventStartTm=getEventStartTm($serv,"{$ev_num}{$ev_lev}"); // 获取事件的持续时间 
                                if (!empty($eventStartTm)) {
                                    $durationTm=getDhms(time()-$eventStartTm);
                                    $durationStr="Total duration:{$durationTm}.";
                                }

                                // 检查是否须要发送邮件
                                if ($alarm_interval['recover_notifiction'] && mdbPassInterval($solved_key, 3600) && !empty($serv)) {
                                    $cust_gp_name = belongCustomizeGroup($serv);
                                    $gp  = false!=$cust_gp_name ?"[".join(',', $cust_gp_name)."]" :"[default group]"; // 对自定义的服务器报警邮件[组名] 
                                    $subject = '-RESOLVED-'.$_EventDescArr["{$ev_num}{$ev_lev}"]." $serv@$gp";
                                    $content  = "$serv monitor event:".$_EventDescArr["{$ev_num}{$ev_lev}"]."({$ev_num}{$ev_lev}) has been resolved.<br>>>Event start at ".date("M-d-Y/H:i:s",$eventStartTm)." {$ev_desc}<br>";  
                                    $content .= $durationStr;
                                    $content .= "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$serv}&version={$version}'>>>SNAPSHOT</a>";
                                    mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content","$ev_num"."$ev_lev",array(__ALARM_TARGET_SERVER,$serv));
                                    if ($ev_num.$ev_lev==sprintf("%04s",__EVCODE997W)) {
                                        writeMq($serv, __HOST_STATUS_UP);
                                    }
                                }
                                /* }}} */
                            }
                            resetKeepWatchCount($serv, "{$ev_num}{$ev_lev}"); // 已经恢复的事件，清零retry计数器 
                        }
                    }
                    if (is_array($problem_serv_time)) {
                        $row_key = sprintf(__KEY_NF,"$ev_num$ev_lev"); 
                        // 重设服务器事件 
                        mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, implode('|',array_unique($problem_serv_time)));
                    } else {
                        mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, "");
                    }
                }
                /* }}} */
            }
        }
    }
}

/**
 *@brief 往即时表写解决状态
 *@param $serv 服务器名
 *@param $evCode 事件代码
 *return $ev_start_timestamp和$event_desc组成的数组 事件发生的时间(解决的时候就是调用此函数的时间)和问题事件描述
 */
function writeFixState($serv, $evCode) {
    global $module_name,$sub_module_name;
    /* {{{ 取出该事件在即时信息表内的事件发生时间
     */
    try {
        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $serv, "event:$evCode");
        $res = array_filter((array)explode(__SOURCE_SPLIT_TAG3, $arr[0]->value)); // 获取事件状态和描述 
        if (!empty($res) && count($res)==__NUM_EVENT_VALUE) {
            $event_status = array_shift($res); // 前半段为事件状态 
            $event_desc   = array_shift($res); // 后半段为事件描述 
            $GLOBALS['fix_event_desc'][$serv] = $event_desc; // 直接存到GLOBALS方式减少返回值个数(否则还得返回一个数组,这样只要返回事件发生时间就可以) 
        } else { // 为空或数目不对，异常退出 
            SaveSysLog("[$module_name][$sub_module_name][get previous event failed!]", 2);
            return false;
        }
        if ($event_status==__EVENT_ACTIVE) {
            $ev_start_timestamp = $arr[0]->timestamp; // 得到事件的发生时间 
            SaveSysLog("[$module_name][$sub_module_name][get event start timestamp{$ev_start_timestamp}]", 3);
        } else { // 原先事件不是激活状态，返回false 
            return false;
        }
    } catch (Exception $e) {
    }
    /* }}} */
    /* {{{ 写解决的即时信息
     */
    $mutations = array(
        new Mutation( array(
            'column' => "event:$evCode",
            'value'  => __EVENT_FIX.__SOURCE_SPLIT_TAG3."Event start at ".date("Y-m-d H:i:s",$ev_start_timestamp).":{$event_desc} has been fixed now"  // 暂时先报解决 
        ))
    ); 
    try { // thrift出错直接抛出异常需要捕获 
        $GLOBALS['mdb_client']->mutateRowTs( __MDB_TAB_SERVER, $serv, $mutations, $ev_start_timestamp);
        SaveSysLog("[$module_name][$sub_module_name][save recover_status ok]", 3);
    }
    catch (Exception $e) { // 抛出异常返回false 
        SaveSysLog("[$module_name][$sub_module_name][save recover_status failed!]", 3);
    }
    /* }}} */
    return array($ev_start_timestamp, $event_desc); // 设置解决状态成功返回事件发生的时间和描述，供历史事件表设置事件的发生和解决时间
}

/**
 * @brief 保存事件标记
 * @param $srv_name 服务器名 
 * @param $mon_type 当前监控的类型
 * @param $ev_code  事件代码
 * @return 
 */
function saveEventDetail($srv_name,$mon_type,$ev_code) {
    global $module_name;
    list($row_key, $table_name) = array($srv_name, __MDB_TAB_SERVER);
    $ev_code = str_pad($ev_code, 4, "0", STR_PAD_LEFT); // 格式化为4位事件代码
    $col_name = "event:$ev_code";
    /* {{{ 检查该事件是否已经解决
     */
    try {
        $arr = $GLOBALS['mdb_client']->get($table_name, $row_key, $col_name);
        $res = array_filter((array)explode(__SOURCE_SPLIT_TAG3, $arr[0]->value)); // 获取事件状态和描述 
        if (!empty($res) && count($res)==__NUM_EVENT_VALUE) {
            $event_status = array_shift($res); // 前半段为事件状态 
            $event_desc   = array_shift($res); // 后半段为事件描述 
        } else { // 为空或数目不对视为首次 
            SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][get event status & desc empty]", 3);
        }
        SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][event_status:$event_status][event_desc:$event_desc]", 3);
    } catch (Exception $e) { //出错直接退出 
        SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][get event status & desc error!]", 2);
        return false;
    }
    /* }}} */
    /* {{{ 如果空或者已经解决，则设置事件状态为激发ACTIVE(value为事件状态|事件描述),否则不处理
     */
    $Fixed = empty($event_status)? true: false;  // 空或者__EVENT_FIX可以直接用empty判断
    $Fixed = $Fixed==false ?($event_status==__EVENT_FIX ?true :false) :true;
    SaveSysLog("[$module_name][saveEventDetail][Fixed:$Fixed]", 3);
    if ($Fixed) {
        SaveSysLog("[$module_name][saveEventDetail][server:$row_key event not active,will set to active]", 3);
        $ev_num = substr($ev_code,0,3); // 获取事件号
        $ev_lev = substr($ev_code,3,1); // 获取事件等级
        $event_content = $_SERVER['nf2'][$ev_num][$ev_lev][$srv_name]['mail_content']; // nf2为扫描后得到的有问题的事件的数组 
        SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][get event desc content:$event_content]", 3);
        $mutations = array(
            new Mutation( array(
                'column' => $col_name, // 事件代码 
                'value'  => __EVENT_ACTIVE."|{$event_content}" // 事件激发|事件内容描述 
            ))
        ); 
        try { // thrift出错直接抛出异常需要捕获 
            $ver_ts = time(); // 事件发生的时间为timestamp 
            $GLOBALS['mdb_client']->mutateRowTs( $table_name, $row_key, $mutations, $ver_ts ); 
            SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][save event status & desc ok]", 3);
            return true;
        }
        catch (Exception $e) { // 抛出异常返回false 
            SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][save event status & desc failed!]", 3);
            return false;
        }
    } else { // 未解决不设置事件状态 
        SaveSysLog("[$module_name][saveEventDetail][evcode:$ev_code][event hasn`t fixed,no need save]", 3);
        return false;
    }
    /* }}} */
}

/**
 *@brief 保存历史事件标记(for事件日志)
 * @param $srv_name 服务器名 
 * @param $ev_code  事件代码
 * @param $ev_start 事件开始时间
 */
function saveHistoryEventDetail($srv_name, $ev_code, $ev_start) {
    global $module_name,$sub_module_name;
    list($row_key, $table_name) = array($srv_name, __MDB_TAB_SERVER_HISTORY);
    $ev_code = str_pad($ev_code, 4, "0", STR_PAD_LEFT); // 格式化为4位
    $mutations = array(
        new Mutation( array(
            'column' => "event:$ev_code",
            'value'  => $ev_start.__SOURCE_SPLIT_TAG3.$GLOBALS['fix_event_desc'][$srv_name] // 存事件发生时间 
        ))
    ); 
    $ver_ts = time(); // 事件解决的时间(当前时间)作为timestamp
    try { // thrift出错直接抛出异常需要捕获 
        $GLOBALS['mdb_client']->mutateRowTs($table_name, $row_key, $mutations, $ver_ts); 
        SaveSysLog("[$module_name][$sub_module_name][saveHistoryEventDetail][save ok]", 3);
        $ret = true;
    } catch (Exception $e) { //抛出异常返回false 
        SaveSysLog("[$module_name][$sub_module_name][saveHistoryEventDetail][save failed!]", 3);
        $ret = false;
    }
    return ($ret);
}

/**
 *@brief 获取事件的发生事件
 *@param @hst 主机名
 *@param evcode 事件代码
 */
function getEventStartTm($hst,$evcode) {
    global $module_name,$sub_module_name;
    try {
        $arr = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $hst, array("event:"));
        $events = $arr[0]->columns;
        foreach ($events as $eventCode => $eventVal) {
            $eventCode = substr($eventCode, -4);
            if ($eventCode==$evcode) {
                $eventStartTime = $events["event:{$eventCode}"]->timestamp;
                SaveSysLog("[$module_name][$sub_module_name][event:$eventCode][start at:$eventStartTime]", 4);
                return $eventStartTime;
            }
        }
    } catch (Exception $e) {}
        return false;
}

/**
 *@brief 获取以天小时分秒为单位的累计秒数
 *@param $secs 秒数
 *@return 分解为天小时分秒组成的字符串
 */
function getDhms($secs) {
    $d = floor($secs/86400);
    $tmp = $secs % 86400;
    $h = floor($tmp/3600);
    $tmp %= 3600;
    $m = floor($tmp/60);
    $s = $tmp % 60;
    return $d. "d ".str_pad($h,2,' ', STR_PAD_LEFT). "h ".str_pad($m,2,' ',STR_PAD_LEFT). "m ".str_pad($s,2,' ',STR_PAD_LEFT). "s";
}

/**
 *@brief 通过四位事件代码得到三位事件号
 *@param $eventCode 四位事件代码
 *return 三位事件号
 */
function getEventNum($eventCode) {
    $eventCode=str_pad($eventCode, 4, '0', STR_PAD_LEFT);
    $eventNum=substr($eventCode,0,3);
    return $eventNum;
}

/**
 *@brief 处理由于某种原因导致异常，产生的恢复事件没有set的问题,for 界面和显示正确的解决时间
 */
function resolvedEventcleaner() {
    global $server_list,$module_name,$_EventConfArr;
    SaveSysLog("[$module_name][cleaning garbage event]", 4);
    $allSrvArr=array();
    foreach ($server_list as $servtype => $servs) {
        $srvArr=(array)explode(',', $servs);
        $allSrvArr=array_merge($allSrvArr,$srvArr);
    }
    $allSrvArr=array_unique(array_filter($allSrvArr));
    sort($allSrvArr);
    $totalEventCode=array_keys($_EventConfArr);
    foreach ((array)$allSrvArr as $serv) {
        foreach ((array)$totalEventCode as $evc) {
            try {
                $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $serv, "event:$evc");
                $res = array_filter((array)explode(__SOURCE_SPLIT_TAG3, $arr[0]->value)); // 获取事件状态和描述
                $resolvedStatus = $res[0];
                if ($resolvedStatus==__EVENT_ACTIVE) { // 如果事件表里事件激发，校对扫描事件数组，看是否已经解决
                    foreach ((array)$_SERVER['needfix'] as $problemEventCode) {
                        $tempArr=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, 'nf'.$problemEventCode, __MDB_COL_EVENT);
                        $problemServerListStr=$tempArr[0]->value;
                        if ($problemEventCode==$evc && !strstr($problemServerListStr, $serv)) {
                            SaveSysLog("[$module_name][clean a handled event!][server:$serv][evcode:$evc]", 4);
                            writeFixState($serv,$evc); // 校正事件表的数据  
                        }
                    }
                    if (!in_array($evc, (array)$_SERVER['needfix'])) {
                            SaveSysLog("[$module_name][clean a handled event!][server:$serv][evcode:$evc]", 4);
                            writeFixState($serv,$evc); // 校正事件表的数据  
                    }
                }
            } catch (Exception $e) {
                SaveSysLog("[$module_name][clean event:got error]", 4);
                return false;
            }
        }
    }
    SaveSysLog("[$module_name][cleaning garbage event][done]", 4);
    return true;
}

/**
 *@brief 写状态消息队列
 *@param $srv 服务器名
 *@param $status 状态，0宕机，1恢复在线
 */
function writeMq($srv,$status) {
    global $module_name;
    switch ($status) {
    case(__HOST_STATUS_UP):
    case(__HOST_STATUS_DOWN):
        // 核对状态
        $realHostStat=hostIsOnline($srv)?__HOST_STATUS_UP:__HOST_STATUS_DOWN;
        if ($status!=$realHostStat) {
            SaveSysLog("[$module_name][writeMq][srv:$srv][status:$status][realHostStat:$realHostStat][exception,will not write]", 4);
            return false;
        }
        // 获取provideIp
        try {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_HOST, $srv, 'info:ip');
            $provide_ip=$arr[0]->value;
            if (empty($provide_ip)) {
                throw new Exception("no provide_ip!");
            }
        } catch (Exception $e) {
            SaveSysLog("[$module_name][writeMq][srv:$srv][get provide_ip fail][cause:$e]", 4);
            return false;
        }
        // 获取load
        try {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $srv, 'info:generic_summary_load');
            $serverload=$arr[0]->value;
        } catch (Exception $e) {
            SaveSysLog("[$module_name][writeMq][srv:$srv][get load fail][cause:$e]", 4);
            return false;
        }
        // 写入
        try {
            $GLOBALS['redis_client']->select(__MQ_TABLE);
            //{opt}|edgesserver|provide_ip|serveralive|serverload|new_ip|carrier|location|timestamp|grp1#grp2
            $GLOBALS['redis_client']->rpush(__MQ_KEY,__MQ_PREFIX_OPT_STATUSCHANGE."|$srv|$provide_ip|$status|$serverload||||".microtime_float2().'|'.join('#',belongCustomizeGroup($srv)));
            SaveSysLog("[$module_name][writeMq][srv:$srv][status:$status][ok]", 4);
        } catch (Exception $e) {
            SaveSysLog("[$module_name][writeMq][srv:$srv][status:$status][fail][cause:$e]", 4);
        }
        return true;
        break;
    case(__HOST_STATUS_NEWADD):
        try {
            if (!is_object($GLOBALS['redis_client'])) {
                include_once(realpath(dirname(__FILE__).'/../').'/GPL/predis/Predis.php');
                $localIniSetting=parse_ini_string(file_get_contents(__CONF_FILE2));
                list($redis_ip,$redis_port)=explode(':',$localIniSetting['redis_host']);
                // redis
                $single_server = array(
                    'host'     => $redis_ip,
                    'port'     => $redis_port,
                    'database' => 15
                );

                if ( !is_object($GLOBALS['redis_client']) ) {
                    $GLOBALS['redis_client'] = new Predis_Client($single_server);
                }
                $GLOBALS['redis_client']->select(__MQ_TABLE);
            }
            // 判断是否是新的
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_HOST, $srv, "info:last_upload");
            if (!empty($arr[0]->value)) {
                $GLOBALS['redis_client']->rpush(__MQ_KEY,__MQ_PREFIX_OPT_STATUSCHANGE."|$srv|$provide_ip|".__HOST_STATUS_UP."|$serverload||||".microtime_float2().'|'.join('#',belongCustomizeGroup($srv)));
                SaveSysLog("[$module_name][writeMq][srv:$srv][status:recover][ok]", 4);
            } else {
                $GLOBALS['redis_client']->rpush(__MQ_KEY,__MQ_PREFIX_OPT_NEWADD."|$srv|$provide_ip|".__HOST_STATUS_UP."|$serverload||||".microtime_float2().'|'.join('#',belongCustomizeGroup($srv)));
                SaveSysLog("[$module_name][writeMq][srv:$srv][status:add][ok]", 4);
            }
        } catch (Exception $e) {
            SaveSysLog("[$module_name][writeMq][srv:$srv][status:add][fail][cause:$e]", 4);
        }
        break;
    }
}
?>
