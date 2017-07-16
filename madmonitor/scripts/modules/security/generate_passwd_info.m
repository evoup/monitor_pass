<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/security/generate_passwd_info.m
  +----------------------------------------------------------------------+
  | Comment:监控安全=>帐号权限
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last Modified:2011-11-17 17:43:42
  +----------------------------------------------------------------------+
 */

/* 更多参考
 * http://www.koders.com/noncode/fidE2EBCB0159F8988B6B659C814EE235A7455E55C3.aspx?s=file#L807
 * freebsd host only!
 */
$module_name='security.passwd';
$err = false;
$arr=$versions=$version_arr=$getLines=$softs=array();

/* {{{ 安全监控项(Check PASSWD)
 */
$passwd_files = array(
    '/etc/passwd'=>'0644',
    '/etc/master.passwd'=>'0600',
    '/etc/pwd.db'=>'0644',
    '/etc/spwd.db'=>'0600'
);

/*
 *检查文件是否存在的问题
 */
$err = in_array(false, array_map('is_file', array_keys($passwd_files))) ?true :false;
if ($err) {
    $_statusPasswdExist=0;
    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][check passwd file exist ,not exist!]");
} else {
    $_statusPasswdExist=1;
    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check passwd file exist ok.]");
}

/*
 *检查其中/etc/pwd.db和/etc/spwd.db是否为二进制文件被替换为ascii的问题
 */
$err = isBinaryFile('/etc/pwd.db') && isBinaryFile('/etc/spwd.db') ?false :true;
if ($err) {
    $_statusPassBinary=0;
    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][check binary err!]");
} else {
    $_statusPassBinary=1;
    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check binary ok.]");
}

/*
 *检查文件掩码默认值是否存在问题
 */
$err=false;
foreach ($passwd_files as $file=>$correct_umask) {
    if (getUmask($file)!=$correct_umask) {
        $err = true;
        break;
    }
}
if ($err) {
    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][check umask err!]");
    $_statusPassDefaultMask=0;
} else {
    $_statusPassDefaultMask=1;
    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check umask ok.]");
}

//------------------------------------------------------------------------
/*
 *检查/etc/passwd内容
 */
$_statusNoEmptyPasswd=$_statusNoEmptyShell=$_statusNoStrangeShell=$_statusNoRootHomeAcc=1;
$_statusNoSendMailBadShell=$_statusLockAccNoValidShell=1;
$res = explode("\n", file_get_contents('/etc/passwd'));
$master_res = file_get_contents('/etc/master.passwd');
foreach ($res as $line) {
    preg_match('/(:).+(:).+(:).+(:).+(:).+(:)/', $line, $match);
    if (!empty($match)) {
        list($user,$hash,$uid,$gid,$desc,$user_home,$user_shell)=explode(':', $line);
        if ($_statusNoEmptyPasswd) { // 检查是否有存在空密码的问题
            if (empty($hash)) {
                DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][$user allow empty password!]");
                $_statusNoEmptyPasswd=0;
            } else {
                DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check empty password ok.]");
            }
        }
        if ($_statusNoEmptyShell) { // 检查是否存在空的shell的问题
            if (empty($user_shell) && $user!='toor') {
                DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][$user allow empty user shell!]");
                $_statusNoEmptyShell=0;
            } else {
                DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check empty shell ok.]");
            }
        }
        if ($_statusNoStrangeShell) { // 检查用户配置的shell是否有问题
            if (!in_array($user_shell, array('/bin/csh', '/usr/bin/false', '/usr/sbin/nologin', '/usr/local/libexec/uucp/uucico', '/bin/sh', '/bin/tcsh', '/sbin/nologin', '/usr/local/bin/bash', '/usr/local/bin/ksh')) && $user!='toor') {
                DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][$user has a strange shell!]");
                $_statusNoStrangeShell=0;
            } else {
                DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check strange shell ok.]");
            }
        }
        if ($_statusNoZeroUidAcc) { // 检查是否存在uid为0的非默认超级账户的问题
            if ($uid==0 && $user!="root" && $user!="toor") {
                DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][$user has uid == 0!]");
                $_statusNoZeroUidAcc=0;
            } else {
                DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check uid == 0 ok.]");
            }
        }
        if ($_statusNoRootHomeAcc) { // 检查是否存在用户的home在根目录的问题
            if ($user_home=='/' && !in_array($user, array('operator', 'bin', 'tty', 'kmem', 'news', 'bind'))) {
                DebugInfo(3,$debug_level,"[$process_name][$module_name]::[passwd][$user has $user_home as home directory!]");
                $_statusNoRootHomeAcc=0;
            } else {
                DebugInfo(4,$debug_level,"[$process_name][$module_name]::[passwd][check home directory ok.]");
            }
        }
        //检查非常用帐号的shell是否被更改的问题
        // TODO 如bind会被hack改成/bin/csh
        if ($_statusNoSendMailBadShell) { // 检查SendMail的帐号所持有的shell是否有问题 
            if (stristr($line, 'Sendmail')) {
                if (!in_array($user_shell, array('/usr/sbin/nologin'))) {
                    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[sendmail][sendMail has a bad shell!]");
                    $_statusNoSendMailBadShell=0; 
                } else {
                    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[sendmail][check sendMail ok.]");
                }
            }
        }
    }
}

/*
 * 检查/etc/master.passwd的内容
 */
if (userLockHasShell($master_res)) {
    $_statusLockAccNoValidShell=0;
    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[master.passwd][locked user has valid shell!]");
} else {
    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[master.passwd][check locked user has valid shell ok.]");
}
//TODO 重复的用户名，重复的uid的检测 

/* }}} */
/* {{{ 安全监控项(别名检查) 
 */
//TODO
/* }}} */
/* {{{ 安全监控项(匿名FTP) 
 */
//TODO
/* }}} */
/* {{{ 安全监控项(crontab)
 */
//TODO
/* }}} */
/* {{{ 安全监控项(sendmail)
 */
// 移至检查/etc/passwd
/* }}} */
//------------------------------------------------------------------------

$dbgStr="[noEmptyPasswd:$_statusNoEmptyPasswd][noEmptyShell:$_statusNoEmptyShell][noStrangeShell:$_statusNoStrangeShell]";
$dbgStr.="[noRootHomeAcc:$_statusNoRootHomeAcc][noSendMailBadShell:$_statusNoSendMailBadShell][lockAccNoValidShell:$_statusLockAccNoValidShell]";
DebugInfo(3,$debug_level,"[$process_name][$module_name]::[check passwd&sendmail][$dbgStr]");
/* {{{ 安全监控项(Shell md5)
 */
$shell_cmds = array(
    '/bin/ls',
    '/usr/bin/netstat',
    '/bin/ps',
    '/usr/bin/strings',
    '/usr/bin/top',
    '/usr/bin/login',
    '/usr/bin/su',
    '/sbin/init',
    '/sbin/sysctl',
    '/usr/bin/find',
    '/usr/bin/passwd',
    '/bin/cat',
    '/bin/chmod',
    '/usr/sbin/chown',
    '/bin/df',
    '/usr/bin/egrep',
    '/usr/bin/fgrep',
    '/usr/bin/grep',
    '/bin/kill',
    '/usr/bin/more',
    '/sbin/ifconfig',
    '/usr/bin/du',
    '/usr/bin/file',
    '/usr/bin/killall',
    '/usr/bin/locate',
    '/sbin/md5',
    '/usr/bin/size',
    '/usr/bin/sort',
    '/usr/bin/touch',
    '/usr/bin/w',
    '/usr/bin/whatis',
    '/usr/bin/whereis',
    '/usr/bin/which',
    '/usr/bin/who',
    '/usr/sbin/cron',
    '/usr/sbin/inetd',
    '/usr/libexec/tcpd',
    '/usr/sbin/adduser',
    '/usr/sbin/vipw',
    '/bin/date',
    '/usr/bin/stat',
    '/usr/bin/users',
    '/usr/sbin/watch',
    '/usr/bin/whoami',
    '/usr/bin/id',
    '/usr/bin/env',
    '/usr/bin/groups',
    '/sbin/mount',
    '/usr/bin/vmstat',
    '/usr/bin/head',
    '/usr/bin/tail',
    '/bin/mkdir',
    '/bin/ln',
    '/bin/echo',
    '/bin/sleep',
    '/bin/unlink',
    '/bin/mv',
    '/bin/hostname'
);
foreach ((array)$shell_cmds as $command) { // 对每个命令进行求其md5 
    // $md5_hash = exec("md5 {$command} | awk -F \"=\" '{print $2}'"); // 既然是监控shell程序，尽量不调用shell程序,不然Unreliable
    $md5_hash = substr(md5(file_get_contents($command)),0,8); 
    $arr[] = basename($command).','.$md5_hash;
}
$shell_progarm_md5 = join('|', $arr); // 得到安全监控项(Shell md5)
DebugInfo(5,$debug_level,"[$process_name][$module_name]::[generic shell md5:$shell_progarm_md5]");
/* }}} */

/* {{{ 安全监控项(Ssh status)
 */
$ssh_auth_status = is_file($auth_log_file) ?__SSH_AUTH_STATUS_OK :__SSH_AUTH_STATUS_LOG_NOT_EXIST;
if ($ssh_auth_status!=__SSH_AUTH_STATUS_LOG_NOT_EXIST) {
    // 开始计算ssh失败的次数，每次执行时读5行，如果发现有3次的登录失败，视为登录异常
    $fp = fopen($auth_log_file, "r");
    while (!feof($fp)) {
        fgets($fp,4096);
        $max++; // 得到最大行数 
    }
    $lastLineNum = @file_get_contents($work_dir.'/authfile.count'); // 上次读到的行数
    DebugInfo(5,$debug_level,"[$process_name][$module_name]::[lastLineNum:$lastLineNum]");
    if (empty($lastLineNum) || !is_numeric($lastLineNum) || $lastLineNum>$max) { // 最后一条件说明：如果该文件被log rotation了再来的处理 
        $lastLineNum = 0;
        DebugInfo(5,$debug_level,"[$process_name][$module_name]::[set lastLineNum to 0]");
    }
    rewind($fp);
    $read_line_nums = $max - $lastLineNum >0 ?($max - $lastLineNum>=5 ?5 :$max - $lastLineNum) :0; // 每次读5行 
    DebugInfo(5,$debug_level,"[$process_name][$module_name]::[max:$max][lastLineNum:$lastLineNum][read: $read_line_nums line(s)]");
    for ($i=0; $i<$read_line_nums; $i++) {
        $current_line = $lastLineNum+$i;
        DebugInfo(5,$debug_level,"[$process_name][$module_name]::[read:$current_line]");
        $getLines[] = readLine($current_line, $fp); // 得到获取到的行数据数组 
    }
    fclose($fp);
    $lastLineNum+=$read_line_nums;
    $lastLineNum>$max && $lastLineNum=$max;
    $fp = fopen($work_dir.'/authfile.count', "w+"); // 写上次读取的行数
    fputs($fp, $lastLineNum);
    fclose($fp);
    foreach ((array)$getLines as $line) {
        if (strstr($line, 'Failed password for')) {
            DebugInfo(5,$debug_level,"[$process_name][$module_name]::[line:$line]");
            $wrong++;
        }
    }
    $wrong>=3 && $ssh_auth_status = __SSH_AUTH_STATUS_ABNORMAL; // Ssh status为不正常 
    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[ssh status:$ssh_auth_status]");
}
/* }}} */

/* {{{ 安全监控项(Software version)
 */
$softwares = array(
    'nginx' => array('/.*bin\/nginx$/', ' -v 2>&1'),
    'memcached' => array('/.*bin\/memcached$/', " -help | awk '{if (NR==1) print}'"),
    'ttserver' => array('/.*\/ttserver$/', ' --version'),
    'mysql' => array('/.*\/mysqld$/', ' --version')
);
foreach ($softwares as $soft => $vcmd) {
    $res = shell_exec("ps -aux | grep {$soft}");
    $softs[$soft] = (array)explode(' ', $res); // 劈开后过滤空元素 
}
foreach ($softs as $soft => $tmpPrograms) {
    foreach ($tmpPrograms as $tmpProgram) {
        $tmpProgram = str_replace("\n", " ", $tmpProgram);  // 分割可能出现换行符 
        $tmpProgram = array_shift(explode(" ", $tmpProgram));
        if (is_file($tmpProgram)) {
            DebugInfo(5,$debug_level,"[$process_name][$module_name]::[$tmpProgram]"); // TODO 对madhouse web server 也加入版本 
            preg_match($softwares[$soft][0], $tmpProgram, $match);
            if (!empty($match)) {
                DebugInfo(5,$debug_level,"[$process_name][$module_name]::[running version][$tmpProgram".$softwares[$soft][1]."]");
                $res = shell_exec($_sh." -c \"".$tmpProgram.$softwares[$soft][1]."\"");
                DebugInfo(5,$debug_level,"[$process_name][$module_name]::[running version return: $res]");
                $pattern = "/(\d+)\.(\d+)\.(\d+)/";
                preg_match($pattern, $res, $match);
                if (!empty($match)) {
                    $versions[$soft]=$match[0]; // 获得各软件的版本号 
                    DebugInfo(4,$debug_level,"[$process_name][$module_name]::[soft:$soft][version:".$match[0]."]");
                }
            }
        }
    }
}
foreach ($versions as $soft => $version) {
    $version_arr[]=$soft.__SOURCE_SPLIT_TAG4.$version;
}
$version_str = join(__SOURCE_SPLIT_TAG3, $version_arr);
$version_str = str_replace(__SOURCE_SPLIT_TAG2, "", $version_str);
/* }}} */

/* {{{ 安全监控项(用户的md5)
 */
$res = md5(file_get_contents('/etc/passwd'));
$u_md5 = trim(ltrim($res));
DebugInfo(4,$debug_level,"[$process_name][$module_name]::[users md5:$u_md5]");
/* }}} */

/* {{{ 安全监控项 以root用户运行的进程
 */
//$command = 'ps -a -Ouser -Ocommand | awk \'{if ($2="root") {$1="";$2="";$3="";$4="";$5="";print $0}}\'';
//$command = "ps -a -Ouser -Ocommand | awk '{if ($2=\"root\") {\$1=\"\";\$2=\"\";\$3=\"\";\$4=\"\";\$5=\"\";print \$0}}'";
//$res = shell_exec($command);
//$res = str_replace(__SOURCE_SPLIT_TAG2, "", $res);
//$root_cmds = array_unique((array)explode("\n", $res));
//$root_cmds = array_filter($root_cmds, 'notEmpty');
//array_shift($root_cmds); // 剔除首行的COMMAND COMMAND, 得到全部root权限运行的进程
//DebugInfo(4,$debug_level,"[$process_name][$module_name]::[commands:".join(',', $root_cmds)."]"); // TODO 用base64传递  
/* }}} */

/* {{{ 安全监控项 Syn半连接数
 */
$command = "netstat -an | grep SYN | wc -l";
$syn_half_num = intval(trim(ltrim(shell_exec($command)))); // 得到Syn半连接数
DebugInfo(4,$debug_level,"[$process_name][$module_name]::[syn half connections:$syn_half_num]");
/* }}} */

/* {{{ 安全监控项 嗅探状态
 */
$network_safe_status = __INTERFACE_SAFE_STATUS_OK;
$res = shell_exec('ifconfig -a');
$arr = explode("\n", $res);
foreach ($arr as $ifconfig_line) {
    $pattern = "/^.*: flags=(\d+)<.*>/";
    preg_match($pattern, $ifconfig_line, $match);
    if ($match) {
        $res = $match[0];
        $network_interface = (array)explode(':', $res);
        $network_interface = $network_interface[0];
        if (strstr($ifconfig_line, 'PROMISC')) { // 网卡处于混杂模式 
            $network_safe_status = __INTERFACE_SAFE_STATUS_ABNORMAL;
        }
    }
}
DebugInfo(4,$debug_level,"[$process_name][$module_name]::[interface safe status:{$network_safe_status}]");
/* }}} */
$security_str = __FLAG_SECURITY.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;
$security_str .= $_statusPasswdExist;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusPassBinary;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusPassDefaultMask;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusNoEmptyPasswd; 
$security_str .= __SOURCE_SPLIT_TAG2.$_statusNoEmptyShell;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusNoStrangeShell;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusNoRootHomeAcc;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusNoSendMailBadShell;
$security_str .= __SOURCE_SPLIT_TAG2.$_statusLockAccNoValidShell;
$security_str .= __SOURCE_SPLIT_TAG2.$shell_progarm_md5;
$security_str .= __SOURCE_SPLIT_TAG2.$ssh_auth_status;
$security_str .= __SOURCE_SPLIT_TAG2.$version_str.__SOURCE_SPLIT_TAG2.$u_md5;
$security_str .= __SOURCE_SPLIT_TAG2.$syn_half_num.__SOURCE_SPLIT_TAG2.$network_safe_status;

DebugInfo(5,$debug_level,"[$process_name][$module_name]::[security_str:$security_str]");
if (!empty($security_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$security_str;
    else $upload_str=$security_str;
    unset($security_str);
}
?>
