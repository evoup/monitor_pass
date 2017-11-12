<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.common.php
  +----------------------------------------------------------------------+
  | Comment:常用函数
  +----------------------------------------------------------------------+
  | Author:evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-10-11 18:43:28
  +----------------------------------------------------------------------+
 */

/**
 * @brief 返回浮点microtime
 * @return 
 */
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


/**
 * @brief 记录syslog日志
 * @param $data 记录的数据
 * @param $debug_lev调试等级
 * @param $syslog_facility
 * @param $syslog_level
 * @param $syslog_tag
 * @return 
 */
function SaveSysLog($data, $debug_lev=4, $syslog_facility='LOG_LOCAL1', $syslog_level='LOG_ALERT', $syslog_tag='monitor') {
    $debug_level=$GLOBALS['debug_level']; // 调试等级
    $controller_type=$GLOBALS['controller_type']; // 控制器类型 
    $save_upload_log=$GLOBALS['save_upload_log']; // 是否保存上传日志 
    $save_update_log=$GLOBALS['save_update_log']; // 是否保存更新日志 
    $upload_log_facility=$GLOBALS['upload_log_facility'];
    $upload_log_level=$GLOBALS['upload_log_level'];
    $scan_log_facility=$GLOBALS['scan_log_facility'];
    $scan_log_level=$GLOBALS['scan_log_level'];
    $update_log_facility=$GLOBALS['update_log_facility'];
    $update_log_level=$GLOBALS['update_log_level'];
    $stag=$GLOBALS['stag'];
    switch ($controller_type) {
    case('upload'):
        if (!$save_upload_log) { // 是否保存客户机上传监控信息日志
            return; 
        } else {
            // 如果配置了syslog level使用配置的，否则使用默认的
            $syslog_level = !empty($upload_log_level)?$upload_log_level:$syslog_level;
            // 如果配置了syslog facility使用配置的，否则使用默认的
            $syslog_facility = !empty($upload_log_facility)?$upload_log_facility:$syslog_facility;
        }
        break;
    case('testspeed_update'):
        if (!$save_update_log) {
            return;
        } else {
            // 如果配置了syslog level使用配置的，否则使用默认的
            $syslog_level = !empty($update_log_level)?$update_log_level:$syslog_level;
            // 如果配置了syslog facility使用配置的，否则使用默认的
            $syslog_facility = !empty($update_log_facility)?$update_log_facility:$syslog_facility;
        }
    case('update'):
        if (!$save_update_log) { // 是否保存客户机访问更新的日志
            return; 
        } else {
            // 如果配置了syslog level使用配置的，否则使用默认的
            $syslog_level = !empty($update_log_level)?$update_log_level:$syslog_level;
            // 如果配置了syslog facility使用配置的，否则使用默认的
            $syslog_facility = !empty($update_log_facility)?$update_log_facility:$syslog_facility;
        }
        break;
    default:
        $syslog_level = !empty($scan_log_level)?$scan_log_level:"LOG_DEBUG";
        $syslog_facility =!empty($scan_log_facility)?$scan_log_facility:$syslog_facility;
        break;
    }

    if ($debug_lev<=$debug_level) {
        if (function_exists("define_syslog_variables")) define_syslog_variables();
        openlog($syslog_tag,LOG_PID,constant($syslog_facility));
        syslog(constant($syslog_level),"[$stag]".$data."[".__VERSION."]");
        closelog();
    }
}

/**
 *@brief 调试信息 
 *@param $debug_level_org
 *@param $debug_level_input
 *@param $debug_data
 *@return 
 */
function DebugInfo($debug_level_org,$debug_level_input,$debug_data) {
    if ($debug_level_org<=$debug_level_input && !empty($debug_data)) {
        $debug_data.='::['.__VERSION."]";
        echo $debug_data."\n";
    }
}


/**
 * @brief 创建配置文件
 * @param $conf_file
 * @param $conf_data
 * @return 
 */
function buildConf($conf_file,$conf_data) {
    if (!file_exists($conf_file)) {
        file_put_contents($conf_file,$conf_data);
        return true;
    }
}

/**
 * @brief     from php.net递归array_diff得到全部维数的区别数组
 * @param $a1 数组1
 * @param $a2 数组2
 * @return    返回全部出现在数组1中但不出现在数组2中的数组
 */
function array_diff_key_recursive ($a1, $a2) {
    foreach ($a1 as $k => $v) {
        if (is_array($v)) {
            $r[$k] = array_diff_key_recursive($a1[$k], $a2[$k]);
        } else {
            $r = @array_diff_key($a1, $a2);
        }

        if (is_array($r[$k]) && count($r[$k])==0) {
            unset($r[$k]);
        }
    }
    return $r;
}

/**
 * @brief          发送邮件给监控管理员
 * @param $from    发件人字符串
 * @param $to      收件人数组
 * @param $cc      抄送数组
 * @param $subject 主题字符串
 * @param $message 内容字符串
 * @param $eventCode 事件代码  为空则邮件只发给超级管理员 
 * @param $tgt     报警的目标，可以传服务器id，或者组名等
 * @return 
 */
function mail2Admin($from, $to, $cc, $subject, $message, $eventCode=NULL, $tgt=false, $warning=false) {
    global $send_mail_type, $sender_name, $_CONFIG;
    list($alarm_target_type, $alarm_target) = $tgt;
    switch ($alarm_target_type) { // 报警目标选择 
    case (__ALARM_TARGET_SERVER): // 单台 
        $belongSrvGroup = getServerGroup($alarm_target); // 得到服务器所属的组 
        foreach ($belongSrvGroup as $srvGroup) { // 得到服务器所在组的报警type$SrvGrp
            if (!is_numeric($srvGroup)) { // 自定义组 
                list($mail_t, $memberUsergroup) = getServergroupAlarmInfo($srvGroup);
                $SrvGrp[$srvGroup]['cust_mail_t'] = $mail_t;
                $SrvGrp[$srvGroup]['memberUsergroups'] = $memberUsergroup;
            }
        }
        foreach ($_CONFIG['user_group'] as $ugroup=>$userArr) { // 得到用户所在用户组$Ugroups 
            $Ugroups[$ugroup]['members'] = explode('#', $userArr);
        }
        foreach ($_CONFIG['user'] as $user => $userSetting) { // 得到全部用户$Users 
            list($mail_type, $email) = explode('#', $userSetting);
            $Users[$user]['mail_type'] = $mail_type;
            $Users[$user]['email'] = $email;
        }
        /* 最终根据$Users,$Ugroups,$SrvGrp的关系报警 */
        foreach ((array)$Users as $user => $tmp_item) {
            SaveSysLog("[mail2Admin][users: $user][mail_type:{$Users[$user]['mail_type']}]",3);
            switch ($Users[$user]['mail_type']) {
            case(__MAILTYPE_USER_NOSEND):
                SaveSysLog("[mail2Admin][users: $user][mail_type:no send]",3);
                break;
            case(__MAILTYPE_USER_CAUTION):
                if (!$warning && !in_array($Users[$user]['email'], $to)) {
                    $to[] = $Users[$user]['email'];
                    SaveSysLog("[mail2Admin][users: $user][mail_type:caution]",3);
                }
                break;
            case(__MAILTYPE_USER_WARNING):
                if ($warning && !in_array($Users[$user]['email'], $to)) {
                    $to[] = $Users[$user]['email'];
                    SaveSysLog("[mail2Admin][users: $user][mail_type:warning]",3);
                }
                break;
            case(__MAILTYPE_USER_BOTH):
                if (!in_array($Users[$user]['email'], $to)) {
                    $to[] = $Users[$user]['email'];
                    SaveSysLog("[mail2Admin][users: $user][mail_type:both]",3);
                }
                break;
            case(__MAILTYPE_USE_BY_GROUP):
                /* {{{ 用户设置了按用户组所属服务器组的设置 
                 */
                foreach ((array)$Ugroups as $ugroup => $tmpArr) {
                    // 检查用户组中是否有该用户
                    if (!in_array($user,$tmpArr['members'])) {
                        continue;
                    }
                    foreach ((array)$SrvGrp as $srvGroup => $tmpArr2) {
                        if (in_array($ugroup, (array)$SrvGrp[$srvGroup]['memberUsergroups'])) {
                            SaveSysLog("[mail2Admin][ugroup:$ugroup]".serialize((array)$SrvGrp[$srvGroup]['memberUsergroups']),3);
                            if (isset($SrvGrp[$srvGroup]['cust_mail_t'])) {
                                // 按该服务器组的设置报
                                if ($eventCode!=__EVCODE997W && !alarmedByServGrp($user,$alarm_target,$eventCode)) {
                                    continue; // 不属于监控的事件略过
                                }
                                switch ($SrvGrp[$srvGroup]['cust_mail_t']) {
                                case(__MAILTYPE_USER_NOSEND):
                                    break;
                                case(__MAILTYPE_USER_CAUTION):
                                    if (!$warning && !in_array($Users[$user]['email'], $to)) {
                                        $to[] = $Users[$user]['email'];
                                    }
                                    break;
                                case(__MAILTYPE_USER_WARNING):
                                    if ($warning && !in_array($Users[$user]['email'],$to)) {
                                        $to[] = $Users[$user]['email'];
                                    }
                                    break;
                                case(__MAILTYPE_USER_BOTH):
                                    if (!in_array($Users[$user]['email'], $to)) {
                                        $to[] = $Users[$user]['email'];
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                /* }}} */
                break;
            }
        }
        break;
    case(__ALARM_TARGET_SERVERGROUP):
        // 暂时不处理glsb报警方式 // TODO 
        break;
    }
    // 计算收件人完成，整理工作
    $cc = $to = array_unique($to);
    
    // To send HTML mail, the Content-type header must be set
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";

    // Additional headers
    $headers .= "From: {$sender_name}<" . $from . ">\r\n";
    @$headers .= "Cc: " . implode(", ", $cc) . "\r\n";
    if ($warning) {
        $message = "<div style='color:red'>{$message}</div>";
    }
    switch ($send_mail_type) {
    case(__MAIL_USE_SENDMAIL): // 使用基于sendmail的php默认mail函数
        mail(implode(", ", $to), ucfirst($subject), $message, $headers);
        break;    
    case(__MAIL_USE_SMTP): // 使用SMTP发送 
        $res = "";
        $tried = 0;
        while (!$res && $tried<=1) { // 重发机制 
            $res = new smtpMail($to, ucfirst($subject), $message);
            $tried++;
        }
        break;
    default: // 默认sendmail的方式 
        mail(implode(", ", $to), ucfirst($subject), $message, $headers);
        break;
    }
}

/**
 *@brief 基础设施初始化
 */
function infrastructionInit() {
    global $mcd_ip,$mcd_port;
    $mcd = memcache_connect($mcd_ip, $mcd_port); //初始化memcache对象 
    $lastStatus = memcache_get($mcd, __INFRASTRUCTURE_STATUS);
    /*{{{从基础设施严重错误中恢复*/
    if ( $lastStatus==__INFRASTRUCTURE_STATUS_ERR  && (passInterval($mcd,__INFRASTRUCTURE_RECOVERY_KEY,800)) ) {
        $subject="-RESOLVED-[infrastructure system failure!]";
        $message="Infrastruction system has been recoveried from a fatal error.will delay ".__INFRASTRUCTURE_RECOVERY_LATENCY." secs then run monitor engine.";
        $cachedConf=parse_ini_string(file_get_contents(__CONF_FILE),true);
        $send_mail_type=$cachedConf['general']['send_mail_type'];
        $sender_name=$cachedConf['mail']['sender_name'];
        $smtp_server=$cachedConf['general']['smtp_server'];
        $smtp_port=$cachedConf['general']['smtp_port'];
        $smtp_timeout=$cachedConf['general']['smtp_timeout'];
        $smtp_username=$cachedConf['general']['smtp_username'];
        $smtp_password=$cachedConf['general']['smtp_password'];
        $smtp_domain=$cachedConf['general']['smtp_domain'];
        $_CONFIG=$cachedConf;
        SaveSysLog("[infrastructionInit][recovery][sender_name:$sender_name][stmphost:$smtp_server.$smtp_port][smtp ttl:$smtp_timeout]");
        SaveSysLog("[infrastructionInit][recovery][smtp_username:$smtp_username][smtp_password:$smtp_password][smtp_domain:$smtp_domain]");
        foreach ($_CONFIG['user_group'] as $ugroup=>$userArr) { // 得到用户所在用户组$Ugroups 
            $Ugroups[$ugroup]['members'] = explode('#', $userArr);
        }
        foreach ($_CONFIG['user'] as $user => $userSetting) { // 得到全部用户$Users 
            list($mail_type, $email) = explode('#', $userSetting);
            $Users[$user]['mail_type'] = $mail_type;
            $Users[$user]['email'] = $email;
            switch ($Users[$user]['mail_type']) {
            case(__MAILTYPE_USER_WARNING):
            case(__MAILTYPE_USER_BOTH):
                if (!in_array($Users[$user]['email'], (array)$to)) {
                    $to[] = $Users[$user]['email'];
                }
                break;
            }
        }
        SaveSysLog('[infrastructionInit][recovery][will send to :'.join(',',$to).']');
        //smtp classs needed
        //$smtp_server,$smtp_port,$smtp_timeout,$smtp_username,$smtp_password,$smtp_domain
        // To send HTML mail, the Content-type header must be set
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";

        // Additional headers
        $headers .= "From: {$sender_name}<" . $cachedConf['mail_from'] . ">\r\n";
        @$headers .= "Cc: " . implode(", ", $cachedConf['mail_to_warning']) . "\r\n";
        switch ($send_mail_type) {
        case(__MAIL_USE_SENDMAIL):
            SaveSysLog("[infrastructionInit][recovery][send sendmail mail]");
            mail(implode(", ", $to), ucfirst($subject), $message, $headers);
            break;
        case(__MAIL_USE_SMTP):
            SaveSysLog("[infrastructionInit][recovery][send smtp mail]");
            $tried = 0;
            while (!$res && $tried<=1) { // 重发机制 
                $res = new smtpMail($to, ucfirst($subject), $message);
                $tried++;
            }
            break;
        }
    }
    /*}}}*/
    memcache_set($mcd,__INFRASTRUCTURE_STATUS,__INFRASTRUCTURE_STATUS_OK); // 能进行到这步证明基础设施可用,设置状态为ok 
    $lastErrTs = memcache_get($mcd, __INFRASTRUCTURE_ERR_TS);
    memcache_close($mcd);
    if ( empty($lastErrTs) ) {
        return true;
    }
    return ( time()-$lastErrTs<__INFRASTRUCTURE_RECOVERY_LATENCY ) ? false : true;  // 延迟扫描，等待客户端上传完毕
}

/**
 *@brief 基础设施hbase的关键报警
 *@param $message 邮件内容
 */
function infrastructionMail2admin($message) {
    global $mcd_ip,$mcd_port;
    /*{{{ 链接不上hbase，改用memcache发送基础设施严重报警*/
    // TODO add can access zookeeper server`s alarm
    $mcd = memcache_connect($mcd_ip, $mcd_port); //初始化memcache对象 
    // 设监控服务端状态为infrastuction错误
    memcache_set($mcd,__INFRASTRUCTURE_STATUS,__INFRASTRUCTURE_STATUS_ERR);
    memcache_set($mcd,__INFRASTRUCTURE_ERR_TS,time(),0,86400); // 扫描延迟,恢复之后等待从该时刻开始的若干秒再扫描
    if (passInterval($mcd,__INFRASTRUCTURE_ALARM_KEY,1800)) { // 半小时报一次 
        SaveSysLog('[infrastructionMail2admin][hass passed interval,will send]');
        $subject="[WARNING!!][infrastructure system failure!]";
        $cachedConf=parse_ini_string(file_get_contents(__CONF_FILE),true);
        $send_mail_type=$cachedConf['general']['send_mail_type'];
        $sender_name=$cachedConf['mail']['sender_name'];
        $smtp_server=$cachedConf['general']['smtp_server'];
        $smtp_port=$cachedConf['general']['smtp_port'];
        $smtp_timeout=$cachedConf['general']['smtp_timeout'];
        $smtp_username=$cachedConf['general']['smtp_username'];
        $smtp_password=$cachedConf['general']['smtp_password'];
        $smtp_domain=$cachedConf['general']['smtp_domain'];
        $_CONFIG=$cachedConf;
        SaveSysLog("[infrastructionMail2admin][sender_name:$sender_name][stmphost:$smtp_server.$smtp_port][smtp ttl:$smtp_timeout]");
        SaveSysLog("[infrastructionMail2admin][smtp_username:$smtp_username][smtp_password:$smtp_password][smtp_domain:$smtp_domain]");
        foreach ($_CONFIG['user_group'] as $ugroup=>$userArr) { // 得到用户所在用户组$Ugroups 
            $Ugroups[$ugroup]['members'] = explode('#', $userArr);
        }
        foreach ($_CONFIG['user'] as $user => $userSetting) { // 得到全部用户$Users 
            list($mail_type, $email) = explode('#', $userSetting);
            $Users[$user]['mail_type'] = $mail_type;
            $Users[$user]['email'] = $email;
            switch ($Users[$user]['mail_type']) {
            case(__MAILTYPE_USER_WARNING):
            case(__MAILTYPE_USER_BOTH):
                if (!in_array($Users[$user]['email'], (array)$to)) {
                    $to[] = $Users[$user]['email'];
                }
                break;
            }
        }
        SaveSysLog('[infrastructionMail2admin][will send to :'.join(',',$to).']');
        //smtp classs needed
        //$smtp_server,$smtp_port,$smtp_timeout,$smtp_username,$smtp_password,$smtp_domain
        // To send HTML mail, the Content-type header must be set
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";

        // Additional headers
        $headers .= "From: {$sender_name}<" . $cachedConf['mail_from'] . ">\r\n";
        @$headers .= "Cc: " . implode(", ", $cachedConf['mail_to_warning']) . "\r\n";
        $message = "<div style='color:red'>{$message}</div>";
        switch ($send_mail_type) {
        case(__MAIL_USE_SENDMAIL):
            SaveSysLog("[infrastructionMail2admin][send sendmail mail]");
            mail(implode(", ", $to), ucfirst($subject), $message, $headers);
            break;
        case(__MAIL_USE_SMTP):
            SaveSysLog("[infrastructionMail2admin][send smtp mail]");
            $tried = 0;
            while (!$res && $tried<=1) { // 重发机制 
                $res = new smtpMail($to, ucfirst($subject), $message);
                $tried++;
            }
            break;
        }
    } else {
        SaveSysLog('[infrastructionMail2admin][during alarm interval,won`t send now]');
    }
    memcache_close($mcd);
    doExit("open mdb");
    /*}}}*/
}

/**
 * @brief php4的array_combine实现
 * @param  
 * @return 
 */
if (!function_exists('array_combine')) {
    function array_combine($arr1,$arr2) {
        $out = array();
        foreach ($arr1 as $key1 => $value1) {
            $out[$value1] = $arr2[$key1];
        }
        return $out;
    }
}

/**
 * @brief 生成随机数种子
 * @return 随机数种子
 */
function make_seed() {
    list($usec, $sec)=explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

/**
 * @brief 生成session id
 * @return sid
 */
function GenerateStag() {
    $min = date('i');
    $min_tail = $min%10;
    $cur_min = date('H')*60 + $min;
    $cur_key = $_SERVER['ls_skey'][$cur_min-1];
    mt_srand(make_seed());
    $mk1 = mt_rand(0, 46655);
    mt_srand(make_seed());
    $mk2 = mt_rand(0, 1256);
    $random_num = sprintf("%03s",base_convert($mk1,10,36)).sprintf("%02s",base_convert($mk2,10,36));
    $check_str = substr(md5($random_num.$cur_key),0,3);
    $stag_base = $random_num.$check_str.$min_tail;
    return $stag_base;
}

/**
 *@brief 转换到Kb，不带单位
 *@return 不带单位的Kb数
 */
function convertToKb($capacity) {
    $unit = strtoupper(substr($capacity, -1));
    $num = substr($capacity, 0, strlen($capacity)-1);
    switch ($unit) {
    case("P"):
        $num = $num*1099511627776;
        break;
    case("T"):
        $num = $num*1073741824;
        break;
    case("G"):
        $num = $num*1048576;
        break;
    case("M"):
        $num = $num*1024;
        break;
    default:
        break;
    }
    return $num;
}

/**
 *@brief 获取全部已定义的服务器
 *@return 全部定义的服务器名字 
 */
function getAllConfSrv() {
    global $_CONFIG;
    $all_srv = array();
    $server_groups = array_values($_CONFIG['server_list']);
    foreach ($server_groups as $srv_grp) {
        $srv_grp = explode(',', $srv_grp);
        foreach ($srv_grp as $srv) {
            !in_array($srv, (array)$all_srv) && $all_srv[] = $srv;
        }
    }
    return $all_srv;
}

/**
 *@brief 获取实际IP,code snipper from discuz!
 *@return ip(ipv4)
 */
function getIp() {
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 *@brief 处理退出需要做的工作
 */
function doExit($why="") {
    global $module_name;
    !empty($why) && SaveSysLog("[$module_name][fun.doExit]because:$why]",3);
    @closeMdb(); // 关闭MDB连接 
    exit;
} 

/**
 * 守护进程化
 */
function daemonize() {
    setEngineStatUsable();
    // 查询zk是否有其他服务器在线
    if (!checkZkAlone()) {
        doExit('Another server node is online.exit');
    }
    // important for daemon
    declare(ticks = 1);
    global $module_name,$master_pid_fp,$pid_file,$pid_file2;
    $fun_name = __FUNCTION__;
    SaveSysLog("[$module_name][fun.$fun_name][daemonizing][sapi:".substr(php_sapi_name(), 0, 3)."]",3);
    if ($pid = pcntl_fork() > 0) {
        //parent process
        //pcntl_wait($pid, $status); //avoid zombie process 
        pcntl_wait($status); //Protect against Zombie children
        //pcntl_wait($status, WUNTRACED); //取得子进程结束状态
        exit(0);
    }
    posix_setsid();
    /*{{{处理信号，以防莫名退出*/
    pcntl_signal(SIGTERM, SIG_IGN);
    pcntl_signal(SIGHUP,  SIG_IGN);
    pcntl_signal(SIGUSR1, SIG_IGN);
    pcntl_signal(SIGPIPE, SIG_IGN);
    /*}}}*/
    if ($pid = pcntl_fork() > 0) {
        exit(0);
    }
    /*{{{切断任何联系*/
    chdir('/');
    umask(0);
    clearstatcache();
    /*}}}*/
    registerSignal('master');
    //killZkCliProc();
    if ($pid = pcntl_fork() > 0) {
        // 扫描干扫描的
        if (function_exists('setproctitle')) {
            setproctitle(__PROCNAME_FATHER." (".__PRGM_PATH."/index.php scan)");
        }
        file_put_contents(__ADDON_ROOT.'/monitorSrv.work',$GLOBALS['stag']);
        setEngineStatProcStart();
    } else {
        file_put_contents($pid_file2, posix_getpid()); // 保存child process pid 
        fclose($master_pid_fp); // 释放文件锁供父进程判断单例用 
        // zookeeper的干zookeeper的
        if (function_exists('setproctitle')) {
            setproctitle(__PROCNAME_ZKCLI);
        } 
        Zookeeper::setDebugLevel(Zookeeper::LOG_LEVEL_DEBUG);
        $zk = new zookeeper_instance();
        $zk->connect($GLOBALS['zookeeper_host'], array($zk, 'connect_cb'));
        usleep(10000);
        while (true) {
            // 子进程尝试获取父进程的文件锁，如果能获取证明父进程挂了，需要退出！
            if (flock($master_pid_fp=fopen($pid_file,'r'), LOCK_NB | LOCK_EX)) {
                exit();
            }
            if ($GLOBALS['stag']!=file_get_contents(__ADDON_ROOT.'/monitorSrv.work')) {
                exit();
            }
            file_put_contents(__ADDON_ROOT.'/zkcli.work',time());
            if ($count<100) { // 防止wcpu占用过大
                $count++;
            } else {
                // 写入redis告诉父进程子进程操作仍在继续，（for 扩展僵死等异常）
                SaveSysLog("[$module_name][daemonize][zk proc][will set alive status]",3);
                file_put_contents(__ADDON_ROOT.'/zkcli.work',time());
                try {
                    $znd=$zk->getChildren('/monitor_server');
                    SaveSysLog("[$module_name][daemonize][zk proc][znd:".serialize($znd)."][my node:".__ZOOKEEPER_NODENAME."]",3);
                    if (!in_array(__ZOOKEEPER_NODENAME,$znd) || count($znd)>1) { // 可能存在挂断之后cron起多个节点同时在线，此时主动退，更好的方式是实现一个锁，参见《基于zookeeper的分布式lock实现》http://agapple.iteye.com/blog/1184040
                        SaveSysLog("[$module_name][daemonize][zk proc][detect another server is online,exit]",3);
                        killZkCliProc();
                        exit();
                    }
                } catch (Exception $e) {
                    SaveSysLog("[$module_name][daemonize][zk proc][Exception:".$e->getMessage()."]",3);
                    exit(); // 有异常直接退
                }
                SaveSysLog("[$module_name][daemonize][zk proc][recounter]",3);
                $count=0;
            }
            usleep(100000);
        }
    }
}

/**
 *@安装信号处理句柄
 */
function registerSignal($roleTag) {
    global $module_name;
    foreach ($GLOBALS['_daemon']['signalsName'] as $signo => $name) {
        if (($name === 'SIGKILL') || ($name == 'SIGSTOP')) {
            //SIGKILL(9), SIGSTOP(19), 是不能被caught或者ignored的
            continue;
        }
        $handlerTag='_'.$roleTag;
        if (pcntl_signal($signo, $handlerTag.'SigHandler')) {
            SaveSysLog("[$module_name][fun.registerSignal][sig:{$name} install signal handler done.]",3);
        } else {
            SaveSysLog("[$module_name][fun.registerSignal][sig:{$name} install signal handler fail.]",3);
        }
    }
}

/**
 *@master进程信号处理函数
 */
function _masterSigHandler($signo) {
    global $pid_file2;
    SaveSysLog("[$module_name][fun._masterSigHandler][got a signal]",3);
    /* {{{解决不同系统SIGCHLD不同的问题
     */
    if ($GLOBALS['_daemon']['signalsName'][$signo]=='SIGCHLD') {
        $sigCHLD=$signo;
    } else {
        $sigCHLD=SIGCHLD;
    }
    /* }}} */
    $pid=file_get_contents($pid_file2);
    switch($signo) {
    case SIGTERM:
    case SIGHUP:
        // If we are being restarted or killed, quit all workers
        // Send the same signal to the children which we recieved
        posix_kill($pid,$signo);
        SaveSysLog("[$module_name][fun._masterSigHandler][signo:SIGHUP][server exit]",3);
        exit();
        break;
    case SIGINT:
        posix_kill($pid,$signo);
        SaveSysLog("[$module_name][fun._masterSigHandler][signo:SIGINT][server exit]",3);
        exit();
        break;
    case $sigCHLD: //Handler for the SIGCHLD (child is dead) signal in master process
        SaveSysLog("[$module_name][fun._masterSigHandler][signo:SIGCHLD][server exit]",3);
        shell_exec("/bin/kill -9 {$pid}");
        //pcntl_waitpid(-1, $status, WNOHANG);
        // pcntl_wifexited 和 pcntl_wexitstatus 某些机器会阻塞无限等待？没有说明文档显示有这个问题，先注释
        /*
         *if (pcntl_wifexited($status) && pcntl_wexitstatus($status) == 1) {
         *    SaveSysLog("[$module_name][fun._masterSigHandler][child exit successful][gone]",3);
         *    exit();
         *} else {
         *    //posix_kill($pid,$signo);
         *    shell_exec("/bin/kill -9 {$pid}");
         *    SaveSysLog("[$module_name][fun._masterSigHandler][force kill $pid]",3);
         *    exit();
         *}
         */
        exit();
        break;
    default:
        SaveSysLog("[$module_name][fun._masterSigHandler][signo:otherSig][server exit]",3);
        exit();
        break;
    }
}

// 子进程已经通过zookeeper进程循环判断，不用再注册函数应对父进程退出

/**
 *@brief  获取毫秒数
 *@return 毫秒数
 */
function microtime_float2() {
    list($usec, $sec) = explode(" ", microtime());
    return (int)(((float)$usec + (float)$sec)*1000);
} 


/**
 *@brief 强杀zkcli进程，for扩展可能有卡死bug的终极fix
 * */
function killZkCliProc() {
    $tmpConf=parse_ini_string(file_get_contents(__CONF_FILE2));
    SaveSysLog('[KillZkCliProc][conf:'.__CONF_FILE2."]");
    list($zkcli_host,$zkcli_port)=explode(':',$tmpConf['zookeeper_host']);
    SaveSysLog("[KillZkCliProc][zkcli_host:{$zkcli_host}][zkcli_port:{$zkcli_port}]");
    $shell="/usr/local/sbin/lsof -i:{$zkcli_port} | awk '{print $2\" \"$9}'";
    SaveSysLog("[KillZkCliProc][shell:{$shell}]");
    @exec($shell,$lsofInfo,$lsofStat);
    if ($lsofStat==0) {
        SaveSysLog("[KillZkCliProc][lsofStat:0][lsofInfo:".serialize($lsofInfo)."]");
        array_shift($lsofInfo);
        foreach ($lsofInfo as $line) {
            list($zkcliPid,)=explode(' ',$line);
            SaveSysLog("[KillZkCliProc][zkcliPid:{$zkcliPid}]");
            if (strstr($line,$tmpConf['zookeeper_host'])) {
                SaveSysLog("[KillZkCliProc][line matched]");
                @exec("/bin/kill -9 {$zkcliPid}",$killInfo,$killStat);
                if ($killStat==0) {
                    SaveSysLog("[KillZkCliProc][killStat:{$killStat}]");
                } else {
                    SaveSysLog("[KillZkCliProc][killStat:{$killStat}]");
                }
            } else {
                SaveSysLog("[KillZkCliProc][not match line]");
            }
        }
    } else {
        SaveSysLog("[KillZkCliProc][lsofStat:{$lsofStat}]");
    }
}
/**
 * @brief 是否经过一定时间的间隔
 * @param mcd memcache对象
 * @param $interval_mcd_key 存在memcached的上次执行的时间记录key
 * @param $want_intverval_time 需要实现间隔的时间秒数
 * @return 如果超时一定时间(可以再发邮件)则返回true，否则没有在这段时间间隔里就返回false
 */
function passInterval($mcd,$lasttime_mcd_key,$want_intverval_time) {
    $last_time=memcache_get($mcd,$lasttime_mcd_key);
    if(empty($last_time)){ //没有取到上次保存在cache里的时间戳,视为为已超时
        $Ret=true;
    }else{ //取到间隔信息，判断是否已经超过间隔
        $Ret=time()-$last_time>=$want_intverval_time?true:false;
    }
    if($Ret){
        //如果已经超时则重设上次保存时间为当前时间 
        memcache_set($mcd,$lasttime_mcd_key,time(),0,7200); //存2小时
    }
    return ($Ret);
}

/**
 *@brief 检查是否有其他服务端节点在线
 *@return true 无其他就节点，false其他节点存在
 */
function checkZkAlone() {
    $tmpConf=parse_ini_string(file_get_contents(__CONF_FILE2));
    SaveSysLog('[checkZkAlone][conf:'.__CONF_FILE2."]");
    list($zkcli_host,$zkcli_port)=explode(':',$tmpConf['zookeeper_host']);
    SaveSysLog("[checkZkAlone][zkcli_host:{$zkcli_host}][zkcli_port:{$zkcli_port}]");
    $fp = pfsockopen($zkcli_host, $zkcli_port, $errno, $errstr); 
    if (!$fp) {
        SaveSysLog("[checkZkAlone][couldn`t open socket to $zkcli_host:$zkcli_port][cause:$errstr ($errno)]");
        fclose($fp);
        return false;
    } else {
        fputs($fp,"dump\r\n");
        fwrite($fp, $out); 
        while (!feof($fp)) { 
            $lines[]=fgets($fp, 256); 
        }
        fclose($fp);
    }
    foreach ((array)$lines as $line) {
        $srvNodeLine=ltrim(trim($line));
        if (!empty($srvNodeLine)) {
            if (strstr($line,'/monitor_server/')) {
                list(,,$node)=explode('/',$srvNodeLine);
                SaveSysLog("[srvNodeLine:$srvNodeLine][node:$node]");
                if (!empty($node) && gethostname()!=$node) {
                    SaveSysLog("[detect $node online]");
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 *@brief 检查某用户所在用户组对应的服务器组，是否对该事件设置为报警
 *@param $user 用户
 *@param $server 当前报警的服务器
 *@param $evcode 事件代码
 *@return true 监控 false 不监控
 */
function alarmedByServGrp($user,$server,$evcode) {
    global $module_name,$monitor_item_arr,$_CONFIG;
    SaveSysLog("[$module_name][alarmedByServGrp][user:{$user}][server:{$server}][evcode:{$evcode}]",5);
    // 找用户所在用户组
    $matchedUgs=array();
    foreach ($_CONFIG['user_group'] as $ugroup => $memberStr) {
        $members=explode('#',$memberStr);
        if (in_array($user,$members) && !in_array($ugroup,$matchedUgs)) {
            $matchedUgs[]=$ugroup;
        }
    }
    SaveSysLog("[$module_name][alarmedByServGrp][matched usergroup:".join(',',$matchedUgs)."]",4);
    // 找用户组管理哪些服务器组
    if (false!==($sgroups=belongCustomizeGroup($server))) {
        SaveSysLog("[$module_name][alarmedByServGrp][server:$server belong to server group:".join(',',$sgroups)."]",3);
        do {
            foreach ($sgroups as $sgroup) {
                if (!empty($_CONFIG['server_group'][$sgroup])) {
                    list($alarmLev,$ugStr,$monitemStr)=explode('#',$_CONFIG['server_group'][$sgroup]);
                    $evcode=str_pad($evcode,4,'0',STR_PAD_LEFT);
                    $evLev=substr($evcode,-1);
                    $evNum=substr($evcode,0,strlen($evcode)-1);
                    SaveSysLog("[$module_name][alarmedByServGrp][check evcode:$evcode][srvGrp:$sgroup][alarmLev:$alarmLev][usergrpStr:$ugStr][monitemStr:$monitemStr][evLev:$evLev][evNum:$evNum]",4);
                    // 检查是否监控
                    $monitemArr=explode('|',$monitemStr);
                    $monCls=0;
                    foreach ($monitemArr as $monInfo) {
                        SaveSysLog("[$module_name][alarmedByServGrp][monInfo:$monInfo]",3);
                        $monInfoArr=str_split($monInfo);
                        SaveSysLog("[$module_name][alarmedByServGrp][monInfoArr][".json_encode($monInfoArr)."]",5);
                        SaveSysLog("[$module_name][alarmedByServGrp][monitor_item_arr][".json_encode($monitor_item_arr)."]",5);
                        SaveSysLog("[$module_name][alarmedByServGrp][count:".count($monInfoArr)."]",4);
                        for ($i=0;$i<sizeof($monInfoArr);$i++) {
                            if ($monInfoArr[$i] && $monitor_item_arr[$monCls][$i]==$evNum) {
                                $determinedMonItem=$monitor_item_arr[$monCls][$i];
                                SaveSysLog("[$module_name][alarmedByServGrp][determineMonItem:$determinedMonItem]",3);
                                return true;
                            }
                        }
                        $monCls++;
                    }
                }
            }
        } while (false);
    }
    // 遍历服务器组拿设置的监控项
    return false;
}

/**
 *@brief 非阻塞fileGetContents
 *@param $url string url地址
 *@param $timeout int 超时时间
 */
function noBlockFileGetContents($url, $timeout) {
    $opts = array(
        'http'=>array(
            'method'=>"GET",
            'timeout' => $timeout
        )
    );
    $context = stream_context_create($opts);
    $html = file_get_contents($url, false, $context);
    return $html;
}

/**
 *@brief 恢复客户端的上传
 */
function restoreUpload() {
    global $client_fpm_start_shell,$master_pid_fp;
    exec($client_fpm_start_shell);
    flock($master_pid_fp, LOCK_UN); 
    fclose($master_pid_fp);
} 
?>
