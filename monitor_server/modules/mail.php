<?php
/*
  +----------------------------------------------------------------------+
  | Name: mail.php
  +----------------------------------------------------------------------+
  | Comment: 邮件模块
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-04 17:53:54
  +----------------------------------------------------------------------+
 */

$module_name = "mail";
SaveSysLog("[$module_name][server event check done,mailing...]",4);

/*{{{ down掉的默认组的监控点和组的邮件报警 */
$type_arr = array('generic'=>__MONITOR_TYPE_GENERIC,'mysql'=>__MONITOR_TYPE_MYSQL,'serving'=>__MONITOR_TYPE_SERVING,
    'daemon'=>__MONITOR_TYPE_DAEMON,'report'=>__MONITOR_TYPE_REPORT,'madn'=>__MONITOR_TYPE_MADN);
if (false===$notDownGroup) {
    $downed_groups = array_filter($downed_groups); // 去掉空元素 
    foreach ($downed_groups as $down_gp_num) {
        if (!empty($down_gp_num)) {
            $down_group_name = array_search($down_gp_num, $type_arr);
            $subject = "$down_group_name:WARNING!!";
            $evcode  = __EVCODE998W;
            $subject .= " (event code:$evcode)";
            $content  = "group $down_group_name down!";
            $interval_key = sprintf(__KEY_INTERVAL_DEFAULT_GP_ONEDOWN, $down_group_name); // 发送间隔 
            $b_mail = mdbPassInterval($interval_key,isset($alarm_interval['one_default_gp_down']) ?$alarm_interval['one_default_gp_down'] :3600);
            if ($b_mail) {
                SaveSysLog("[$module_name][sending][event_code:$evcode]:[one default group down:$down_group_name]",4);
                mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content", $evcode, array(__ALARM_TARGET_SERVERGROUP,$down_group_name), true);
            } else {
                SaveSysLog("[$module_name][not send due in interval time][event_code:$evcode]:[one default group down:$down_group_name]",4);
            }
        }
    }
}
if (false===$notDownServer) {
    $downed_servers = array_filter($downed_servers); // 去掉空元素 
    foreach ($downed_servers as $type => $downed_srv) {
        $type+=1; // 修正diff出来的type下标索引值少1 
        $down_group_name = array_search($type, $type_arr);
        foreach (array_values($downed_srv) as $down_srv) {
            //$subject = "$down_srv:WARNING!!";
            $evcode  = __EVCODE997W;
            //$subject .= " (event code:$evcode)";
            $subject = '[WARNING!!]Host down '."$down_srv@[default group {$down_group_name}]";
            $content  = "Server of default groups $down_group_name: $down_srv down!";
            $content .= "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$down_srv}&version={$version}' target=_BLANK>>>SNAPSHOT</a>";
            $interval_key = sprintf(__KEY_INTERVAL_SERVER_EVENT, $down_srv, $evcode); // 发送间隔 
            $b_mail = mdbPassInterval($interval_key, isset($alarm_interval['one_default_server_down']) ?$alarm_interval['one_default_gp_down'] :3600); 
            if ($b_mail) {
                SaveSysLog("[$module_name][sending][event_code:$evcode]:[one default server down:$down_srv]",4);
                mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content",$evcode,array(__ALARM_TARGET_SERVER,$down_srv),true);
                //writeMq($down_srv,__HOST_STATUS_DOWN);
            } else {
                SaveSysLog("[$module_name][not send due in interval time][event_code:$evcode]:[one default server down:$down_srv]",4);
            }
            $_SERVER['downed'][] = $down_srv;
            saveEventDetail($down_srv, __MONITOR_TYPE_GENERIC, __EVCODE997W); // 存事件代码 
        }
    }
}
/*}}}*/

/*{{{ down掉的自定义组的监控点和组的邮件报警 */
if (false===$notDownCustGroup) {
    $downed_cust_groups = array_filter($downed_cust_groups); // 去掉空元素 
    foreach ($downed_cust_groups as $down_group_name) {
        if (!empty($down_group_name)) {
            //$subject = "[$down_group_name]:WARNING!!";
            $evcode  = __EVCODE998W;
            //$subject .= " (event code:$evcode)";
            $subject = 'Group Down '." WARNING!! {$down_group_name}";
            $content  = "group $down_group_name down!";
            $interval_key = sprintf(__KEY_INTERVAL_CUST_GP_ONEDOWN, $down_group_name); // 发送间隔 
            $b_mail = mdbPassInterval($interval_key, isset($alarm_interval['one_cust_gp_down']) ?$alarm_interval['one_cust_gp_down'] :3600); 
            if ($b_mail) {
                SaveSysLog("[$module_name][sending][event_code:$evcode]:[one customize group down:$down_group_name]",4);
                mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content",$evcode,array(__ALARM_TARGET_SERVERGROUP,$down_group_name),true);
            } else {
                SaveSysLog("[$module_name][not send due in interval time][event_code:$evcode]:[one customize tgroup down:$down_group_name]",4);
            }
        }
    }
}    
if (false===$notDownCustServer) {
    $downed_cust_servers = array_filter($downed_cust_servers); // 去掉空元素 
    foreach ($downed_cust_servers as $type => $downed_srv) {
        foreach (array_values((array)$downed_srv) as $down_srv) {
            $group_name = join(',', belongCustomizeGroup($down_srv));
            //$subject = "[$group_name]$down_srv:WARNING!!";
            $evcode  = __EVCODE997W;
            //$subject .= " (event code:$evcode)";
            $subject = '[WARNING!!]host down '."$down_srv@[$group_name]";
            $content  = "Server of customize group [$group_name]: $down_srv down!";
            $content .= "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$down_srv}&version={$version}' target=_BLANK>>>SNAPSHOT</a>";
            $interval_key = sprintf(__KEY_INTERVAL_SERVER_EVENT, $down_srv, $evcode); // 发送间隔 
            $b_mail = mdbPassInterval($interval_key,isset($alarm_interval['one_cust_server_down'])?$alarm_interval['one_cust_server_down']:3600);
            if ($b_mail) {
                SaveSysLog("[$module_name][sending][event_code:$evcode]:[customize group:$group_name][server down:$down_srv]",4);
                mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content",$evcode,array(__ALARM_TARGET_SERVER,$down_srv),true);
                //writeMq($down_srv,__HOST_STATUS_DOWN);
            } else {
                SaveSysLog("[$module_name][not send due in interval time][event_code:$evcode]:[customize group:$group_name][server down:$down_srv]",4);
            }
            $_SERVER['downed'][] = $down_srv;
            saveEventDetail($down_srv,__MONITOR_TYPE_SERVING,__EVCODE997W); // 存事件代码 
        }
    }
}
/*}}}*/

/*{{{ 剩下不在服务器存活列表中的也要报(可能遗漏的要补上)
 */
if (false===$notDownRest) {
    $down_rest = array_filter($down_rest); // 去掉空元素 
    foreach ($down_rest as $down_srv) {
        if (!in_array($down_srv, (array)$_SERVER['downed'])) {
            $group_name = ($gp=belongCustomizeGroup($down_srv)) ?join(',', $gp) :"default group";
            //$subject = $gp ?"[$group_name]$down_srv:WARNING!!" :"$down_srv:WARNING!!";
            $evcode  = __EVCODE997W;
            //$subject .= " (event code:$evcode)";
            $subject = '[WARNING!!]Host down '."$down_srv@[$group_name]";
            $content  = $gp ?"Server of [$group_name]: $down_srv down!" :"Server: $down_srv down!";
            $content .= "<a href='".$snapshot_url_prefix."/monitorui/mobile/mail.html?snapshotid=de437&server={$down_srv}&version={$version}' target=_BLANK>>>SNAPSHOT</a>";
            $interval_key = sprintf(__KEY_INTERVAL_SERVER_EVENT, $down_srv, $evcode); // 发送间隔 
            $b_mail = mdbPassInterval($interval_key, isset($alarm_interval['one_cust_server_down']) ?$alarm_interval['one_cust_server_down'] :3600);
            if ($b_mail) {
                SaveSysLog("[$module_name][notDownRest][sending][event_code:$evcode]:[group:$group_name][server down:$down_srv]",4);
                mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", time())." $content", $evcode, array(__ALARM_TARGET_SERVER,$down_srv), true);
                //writeMq($down_srv,__HOST_STATUS_DOWN);
            } else {
                SaveSysLog("[$module_name][not send due in interval time][event_code:$evcode]:[customize group:$group_name][server down:$down_srv]",4);
            }
            saveEventDetail($down_srv,__MONITOR_TYPE_GENERIC,__EVCODE997W); // 存事件代码 
        }
    }
}
/*}}}*/


/*{{{ 已经完成down邮件报警，存活的监控点的事件报警(默认组加定义组) 
 */
$row_key = __KEY_NEEDFIX; // 查询待处理事件列表，得到所有待处理服务器事件
try {
    $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
    $res = $res[0]->columns;
    $res = $res[__MDB_COL_EVENT]->value; // 获取value 
} catch (Exception $e) {
}
if (!empty($res)) {
    $needfix_events = explode('|', $res);
} else { 
    SaveSysLog("[$module_name][No needfix event,scan done!]",1);
}
/*}}}*/

foreach ((array)$needfix_events as $event) { // 对应每个待处理服务器事件发送警报邮件

    $row_key = sprintf(__KEY_NF, $event);
    try {
        $res = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $row_key, array(__MDB_COL_EVENT));
        $res = $res[0]->columns;
        $res = $res[__MDB_COL_EVENT]->value; // 获取value 
    } catch (Exception $e) {
    }
    SaveSysLog("[$module_name][res:$res]",4); 
    if (!empty($res)) {
        $servers_and_times=explode('|',$res); 
        foreach ($servers_and_times as $server_time) {
            list($server, $time) = explode('#', $server_time);

            // 发送邮件
            $ev_num = substr($event, 0, 3); // 取出事件号 
            $ev_lev = substr($event, -1, 1); // 取出事件等级
            $ev_code = sprintf("%04s", $ev_num.$ev_lev);

            SaveSysLog("[$module_name][event_code:$ev_num$ev_lev]:[server:$server]",4); 
            $cust_gp_name = belongCustomizeGroup($server);
            $cust_gp_name = false!=$cust_gp_name ?"[".join(',', $cust_gp_name)."]" :"[default group]"; // 对自定义的服务器报警邮件前置[组名] 
            $lev = $ev_lev=='c' ?"CAUTION!" :"WARNING!!";
            //$subject .= " (event code:$ev_num$ev_lev)";
            /* {{{ 对于端口问题事件，事件描述的port要放到标题
             */
            $addonSubject='';
            if ($ev_code==sprintf("%04s",__EVCODE006C)) {
                $tempArr=explode(' ',$_SERVER["nf2"]["$ev_num"]["$ev_lev"]["$server"]["mail_content"]);
                $addonSubject="{$tempArr[3]} port:{$tempArr[5]}"; //分别为服务名 port 端口号
            }
            /* }}} */
            $subject = str_replace('tcpip service', '',"[{$lev}]".$_EventDescArr[$ev_code].$addonSubject." $server@$cust_gp_name");
            $content = $_SERVER["nf2"]["$ev_num"]["$ev_lev"]["$server"]["mail_content"];
            $content.=$_SERVER['nf2']["$ev_num"]["$ev_lev"]["$server"]['snapshot_link'];

            if (!empty($content)) {
                $interval_key = sprintf(__KEY_INTERVAL_SERVER_EVENT, $server, $ev_code); // 发送间隔 
                $b_mail = mdbPassInterval($interval_key, isset($alarm_interval['general_server_event']) ?$alarm_interval['general_server_event'] :3600); // 默认间隔3600秒发一次 
                if ($b_mail) {
                    SaveSysLog("[$module_name][sending][event_code:$ev_num$ev_lev]:[server:$server]",4);

                    if ($ev_lev=='c') {
                        mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", $_SERVER["nf2"]["$ev_num"]["$ev_lev"]["$server"]["event_time"])." $content","$ev_num"."$ev_lev",array(__ALARM_TARGET_SERVER,$server));
                    } else {
                        // 宕机的已经报了这里不用再报 XXX 把上面的宕机报警多余代码删除
                        $ev_code!=__EVCODE997W && mail2Admin($mail_from, $mail_to_caution, $mail_cc_caution, $subject, date("M-d-Y/H:i:s", $_SERVER["nf2"]["$ev_num"]["$ev_lev"]["$server"]["event_time"])." $content","$ev_num"."$ev_lev",array(__ALARM_TARGET_SERVER,$server),true);
                    }

                } else {
                    SaveSysLog("[$module_name][not send due in interval time][event_code:$ev_num$ev_lev]:[server:$server]",4);
                }
            } else {
                SaveSysLog("[$module_name][exception:mail content empty?maybe event has fixed!][event_code:$ev_num$ev_lev]:[server:$server]",4);
                /* {{{ 可能是事件被解决了，从待处理列表里删除事件，同时删除对应的nf TODO 放到mail之前处理
                 */
                try {
                    $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, __KEY_NEEDFIX, __MDB_COL_EVENT);
                    $nf_list = $arr[0]->value;
                    $nf_list = explode('|', $nf_list);
                    $ev = $ev_num.$ev_lev;
                    $idx = array_search($ev, $nf_list);
                    if (false!==$idx && null!==$idx) {
                        unset($nf_list[$idx]);
                    }
                    mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, __KEY_NEEDFIX, implode('|', $nf_list));
                    SaveSysLog("[$module_name][remove $ev_num$ev_lev from needfix list]", 4);
                } catch (Exception $e) {
                }
                mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, "nf{$ev}", "");
                /* }}} */
                // 设置事件表中该事件的解决状态和解决时间
                writeFixState($server, $ev);
            }
        }
    }
}
SaveSysLog("[$module_name][Mail has been sent]",4);
/*}}}*/

?>
