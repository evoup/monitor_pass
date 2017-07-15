<?php
/*
  +----------------------------------------------------------------------+
  | Name:daily.php
  +----------------------------------------------------------------------+
  | Comment:日常事务
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-16 12:33:09
  +----------------------------------------------------------------------+
 */
$module_name = "daily";
try {
    $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER,'generial_setting',__MDB_COL_CONFIG_INI);
    $res=$arr[0]->value;
    $res=json_decode($res);
    $sendHour=$res->sendHour;
    $sendMin=$res->sendMin;
} catch (Exception $e) {
    $err = true;
}

/** 
 * [发送日常邮件逻辑]
 * 如果未发送，时间未到，不处理
 * 如果未发送，时间已到，发送后设置为已发送
 * 如果已发送，则不处理
 */
/* {{{ 从MDB中获取全部用户
 */
list($table_name,$start_row,$family) = array(__MDB_TAB_USER, '', array('info')); // 从row的起点开始 
try {
    $scanner = $GLOBALS['mdb_client']->scannerOpen( $table_name, $start_row , $family );
    while (true) { // TODO 这里可能会发生超时，需要加时限 
        $get_arr = $GLOBALS['mdb_client']->scannerGet( $scanner );
        if ($get_arr == null) break;
        foreach ( $get_arr as $TRowResult ) {
            $user = $TRowResult->row; // 以用户名为rowkey 
            /* {{{ 取出实际用户名和电子邮件
             */
            $column = $TRowResult->columns;
            foreach ($column as $family_column=>$Tcell) {
                switch ($family_column) {
                case('info:email'):
                    $email = $Tcell->value;
                    break;
                }
            }
            $users_mail[$user]=$email; //组成用户名为key的数组 
            /* }}} */
        }
    }
    $GLOBALS['mdb_client']->scannerClose( $scanner ); // 关闭scanner 
} catch (Exception $e) {
    SaveSysLog("[$module_name][scan user err]",4);
    $err = true;
}
/* }}} */
/* {{{ 对所有用户都发送日常邮件
 */
$to=array_unique(array_merge($mail_to_caution, $mail_to_warning));
$cc=array_unique(array_merge($mail_cc_caution, $mail_cc_warning));
foreach ($users_mail as $user => $email) {
    if (!in_array($email, $to)) {
        $to[] = $email;
    }
    if (!in_array($email, $cc)) {
        $cc[] = $email;
    }
}
/* }}} */
if (!$err && $_CONFIG['general']['send_daily_mail']) {
    try {
        // 计算目前有多少要解决的事件,TODO定义一些不用报警的事件，但是可以显示在界面上，和每日报表里
        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, __KEY_NEEDFIX, __MDB_COL_EVENT);
        $needfix_str=$arr[0]->value;
        $needfix_arr=array_filter(explode('|',$needfix_str));
        $needfixEvents=count($needfix_arr);
        foreach ((array)$needfix_arr as $eventCode) { // 每种事件下有几个问题服务器 
            $arr2 = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, "nf$eventCode", __MDB_COL_EVENT);
            $arr2 = $arr2[0]->value;
            $evNum+=count(array_filter(explode('|',$arr2)));
        }

        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_ENGINE, 'mail_status', __MDB_COL_DAILY_MAIL); 
        $todaySendStatus = $arr[0]->value; 
        if ($todaySendStatus!=sprintf(date('Ymd',time())."%s", __DAILY_MAILED_YES)) {
            SaveSysLog("[$module_name][daily mail hasn`t send,check time]",4);
            list(,$currentMin,$currentHour,,,,,,,,)=array_values(getDate());
            if (($sendHour==$currentHour && $sendMin<=$currentMin) || $sendHour<$currentHour) { // 可以发送 
                SaveSysLog("[$module_name][time passed,send daily report mail!]",4);
                $subject = 'ENGINE DAILY REPORT';
                $pid_file = __PRGM_PATH . '/' . __RUN_SUBPATH . '/' . __PROCESS_NAME.'.pid';
                $server_pid=file_get_contents($pid_file);
                $content = "Monitor Engine working status OK.\nPid is: {$server_pid}.\n";
                if (empty($evNum)) {
                    $content.="Conguratulations, all of our servers run OK currently.\n";
                } else {
                    if ($evNum==1) {
                        $content.="There is {$evNum} event to be fix,take a look at it!";
                    } else {
                        $content.="There`re {$evNum} events to be fix,take a look at them!";
                    }
                }
                $res = "";
                $tried = 0;
                while (empty($res) && $tried<=1) { // TODO 注意其他mdb_set的处理 
                    if (mdb_set(__MDB_TAB_ENGINE, __MDB_COL_DAILY_MAIL, 'mail_status', sprintf(date('Ymd',time())."%s", __DAILY_MAILED_YES))) {
                        $res = true;
                    }
                    $tried++;
                }
                mail2Admin($mail_from, $to, $cc, $subject, date("M-d-Y/H:i:s", time())." $content");
            } else {
                SaveSysLog("[$module_name][time hasn`t passed,not send the mail]",4);
            }
        } else {
            SaveSysLog("[$module_name][daily mail already sent,no need to send again.]",4);
        }
    } catch (Exception $e) {
        $err = true;
        SaveSysLog("[$module_name][daily report error.]", 4);
    }
} else {
    SaveSysLog("[$module_name][daily report is set off.]", 4);
}
$GLOBALS['redis_client']->quit();
