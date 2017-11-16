<?php
$start_time=time();

/** version info **/
$custom_argvs=readArgv($argv);
if (isset($custom_argvs['version'])) {
    echo "version:".__VERSION."\n";
    exit;
}
$debug_level=isset($custom_argvs['_d'])?(int)$custom_argvs['_d']:0;
$debug_data="[debug_level:$debug_level]";
DebugInfo(1,$debug_level,$debug_data);

$input_source=empty($custom_argvs['_s'])?null:$custom_argvs['_s'];
$input_file=empty($custom_argvs['_f'])?null:$custom_argvs['_f'];

/** make sure only one process running **/
$pid_file=__PROC_ROOT.'/'.__RUN_SUBPATH.'/'.$process_name.'.pid';
makeDir($pid_file,"0755",0,'f');
if (SingleProcess($process_name,$pid_file)!==TRUE) {
    $sys_data="last upload process exists";
    DebugInfo(1,$debug_level,$sys_data);
    exit;
}
/** fix uncompile php script above code can`t make single proc bug **/
$lock_file=__PROC_ROOT.'/'.__RUN_SUBPATH.'/'.$process_name.'.lock';
if (!flock($tempLockFile=fopen($lock_file,'w'), LOCK_NB | LOCK_EX)) {
    $sys_data="last upload process exists,cause lock mechanisms";
    DebugInfo(1,$debug_level,$sys_data);
    exit;
}

$conf_file=__PROC_ROOT.'/'.__CONF_SUBPATH.'/'.$process_name.'.ini';
makeDir($conf_file,"0755",0,'f');

$status_file=__PROC_ROOT.'/'.__STATUS_SUBPATH."/".$process_name.'.status';
makeDir($status_file,"0755",0,'f');

$work_dir=__PROC_ROOT.'/'.__WORK_SUBPATH;
makeDir($work_dir,"0755",0,'d');

$debug_data="[run_file:$pid_file]-[conf_file:$conf_file]-[status_file:$status_file]-[work_dir:$work_dir]";
DebugInfo(1,$debug_level,$debug_data);

$del_stat=true;
$daemon_stat=true;
$run=true;

if (true===buildConf($conf_file,$array_conf)) {
    echo "build configuration file,done. run again\n";
    exit();
} else {
    $array_conf=parse_ini_file($conf_file,true);
}

//read configuration file

/* all */

//操作系统支持
$_monitor_linux=($array_conf['monitor_linux']==='1')?true:false;
//主机名
$_server_name=empty($array_conf['server_name'])?'no_name':$array_conf['server_name'];
$_grep=empty($array_conf['path_grep'])?'/usr/bin/grep':$array_conf['path_grep'];
//ip作为主机名后缀依据
$determinIp=NULL;
$_ifconfig=empty($array_conf['path_ifconfig'])?'/sbin/ifconfig':$array_conf['path_ifconfig'];
$_ifconfig_timeout=empty($array_conf['ifconfig_timeout'])?'2':$array_conf['ifconfig_timeout'];
$_netstat_timeout=empty($array_conf['netstat_timeout'])?'5':$array_conf['netstat_timeout'];
file_put_contents(__PROC_ROOT.'/work/__ifconfig_moninit.info','');
$command_if="{$_ifconfig} > ".__PROC_ROOT."/work/__ifconfig_moninit.info & sleep {$_ifconfig_timeout} ; kill $! >> /dev/null 2>&1";
@exec($command_if,$if_info,$if_stat);
$res=file_get_contents(__PROC_ROOT.'/work/__ifconfig_moninit.info');
if (empty($res)) {
    print("ifconfig timeout\n");
    exit();
}
$if_info=$res; //用shell重定向得到的结果替代函数返回的结果 
$if_info=explode("\n",$if_info);
if (empty($if_info)) {
    print("can`t get network interface info\n");
    exit();
}
$got_ip=false;
switch($_monitor_linux) {
case(false):
        foreach ($if_info as $line) {
            preg_match_all('/.*: flags=\d+<.*> metric \d+ mtu \d+.*/',$line,$match);
            if (!empty($match[0])) {
                $got_ip=false;
            }
            preg_match_all('/.*inet (\d+\.\d+\.\d+\.\d+) netmask.*/',$line,$match);
            if (!empty($match[0]) && $match[1][0]!=='127.0.0.1' && $match[1][0]!=='localhost') {
                $got_ip=$match[1][0];
            }
            //preg_match_all('/.*status: active.*/',$line,$match);
            //if (!empty($match[0]) && false!==$got_ip) {
                //$determinIp[]=$got_ip; //many 
            //}
            if (false!==$got_ip) {
                $determinIp[]=$got_ip; //many 
            }
        }
    break;
case(true):
    foreach ($if_info as $line) {
        preg_match_all('/.*inet addr:(\d+\.\d+\.\d+\.\d+).*Mask.*/',$line,$match);
        if (!empty($match[0])) {
            $got_ip=false;
        }
        if (!empty($match[0]) && $match[1][0]!=='127.0.0.1' && $match[1][0]!=='localhost') {
            $got_ip=$match[1][0];
        }
        preg_match_all('/.*UP.*MTU:\d+.*Metric:\d+.*/',$line,$match);
        if (!empty($match[0]) && false!==$got_ip) {
            $determinIp[]=$got_ip; //many 
        }
    }
    break;
}
DebugInfo(1,$debug_level,"[all_ip:".join(',',$determinIp)."]");
$suffix_name=sprintf('%x',ip2long($determinIp[0])); //根据后缀倒推ip long2ip(hexdec('suffix_name'))
$_server_name.='-'.$suffix_name;
$replace_arr = array("\r","\n","\t",' ','~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')',
    '+', ',', '.', '/', '<', '>', '?');
$_server_name=str_replace($replace_arr,'',$_server_name);
DebugInfo(1,$debug_level,"[full_server_name:{$_server_name}]");

$sleep=empty($array_conf['sleep'])?__SLEEP:(int)$array_conf['sleep'];
$proc_life=empty($array_conf['proc_life'])?__PROC_LIFE:(int)$array_conf['proc_life'];
$mcd_server=empty($array_conf['mcd_server'])?__MCD_SERVER:$array_conf['mcd_server'];
$upload_url=empty($array_conf['upload_url'])?'null':$array_conf['upload_url'];
$auto_update=empty($array_conf['auto_update'])?true:$array_conf['auto_update']; // 默认自动更新 
$update_url=empty($array_conf['update_url'])?null:$array_conf['update_url'];
$update_check_time=empty($array_conf['update_check_time'])?60:$array_conf['update_check_time'];
/*{{{如果不设置upload_url,则需要配置upload_host,upload_port,upload_version,upload_suffix以构造上传的URL*/
if ($upload_url==='null') {
    $upload_host=$array_conf['upload_host'];
    if(empty($upload_host)) {
        print("empty configure item: upload_host\n");
        exit();
    }
    $upload_port=empty($array_conf['upload_port'])?"80":"{$array_conf['upload_port']}";
    $upload_version=$array_conf['upload_version'];
    if (empty($upload_version)) {
        print("empty configure item: upload_version\n");
        exit();
    }
    $upload_suffix=$array_conf['upload_suffix'];
    $debug_data="[upload_host:$upload_host][upload_port:$upload_port][upload_version:upload_version][upload_suffix:$upload_suffix]";
    DebugInfo(1,$debug_level,$debug_data);
    $upload_url="http://{$upload_host}:{$upload_port}/{$upload_version}/{$_server_name}/$upload_suffix";
    DebugInfo(1,$debug_level,"[use join url][upload_url:$upload_url]");
    $update_suffix=$array_conf['update_suffix'];
    if (empty($upload_suffix)) {
        print("empty configure item: upload_suffix\n");
        exit();
    }
    if (empty($update_url)) {
        $update_url="http://{$upload_host}:{$upload_port}/{$upload_version}/{$_server_name}/$update_suffix";
        DebugInfo(1,$debug_level,"[use join url][update_url:$update_url]");
    }

} else {
    DebugInfo(1,$debug_level,"[use upload url][upload_url:$upload_url]");
}
/*}}}*/
/*{{{测速配置*/
$testspeedconf_suffix=$array_conf['testspeedconf_suffix'];
if (!empty($testspeedconf_suffix)) {
    $testspeedconf_url="http://{$upload_host}:{$upload_port}/{$upload_version}/{$_server_name}/$testspeedconf_suffix";
    DebugInfo(1,$debug_level,"[use join url][testspeedconf_url:$testspeedconf_url]");
}
/*}}}*/
$http_host=empty($array_conf['http_host'])?'monitor.madserving.cn':$array_conf['http_host'];

//monitor项目
$_monitor_mysql=($array_conf['monitor_mysql']==='1')?true:false;
$_monitor_serv=($array_conf['monitor_serv']==='1')?true:false;
$_monitor_manage=($array_conf['monitor_manage']==='1')?true:false;
$_monitor_report=($array_conf['monitor_report']==='1')?true:false;
$_monitor_delivery=($array_conf['monitor_delivery']==='1')?true:false;
$_monitor_madn=($array_conf['monitor_madn']==='1')?true:false;
$_monitor_security=($array_conf['monitor_security']==='1')?true:false;
$_monitor_hadoop=($array_conf['monitor_hadoop']==='1')?true:false;

if ($_monitor_mysql || $_monitor_manage) {
    $mysql_host=empty($array_conf['mysql_host'])?'127.0.0.1':$array_conf['mysql_host'];
    $mysql_user=empty($array_conf['mysql_user'])?'rmcore':$array_conf['mysql_user'];
    $mysql_pass=empty($array_conf['mysql_pass'])?'rm8888':$array_conf['mysql_pass'];
    $mysql_db=empty($array_conf['mysql_db'])?array():explode(',',$array_conf['mysql_db']);
    if (!empty($mysql_db)) {
        foreach ($mysql_db as $mysql_monitor_detail) {
            list($db_name,$table_name)=explode('.',$mysql_monitor_detail);
            $array_mysql_monitor[$db_name][]=$table_name;
        }
    }
    $login_info=empty($array_conf['login_info'])?'madhouse3.t_user':$array_conf['login_info'];
    list($login_db,$login_table)=explode('.',$login_info);
}

if ($_monitor_serv || $_monitor_manage || $_monitor_report || $_monitor_hadoop) {
    //需要读取log
    $_log_path=empty($array_conf['log_path'])?'/services/serving_log':$array_conf['log_path'];
    $_log_name=empty($array_conf['log_name'])?'counter.log':$array_conf['log_name'];
    $_max_mb=empty($array_conf['max_size'])?2:(int)$array_conf['max_size'];
    $_max_size=$_max_mb*1024*1024;
    $deal_log_file=$_log_path.'/'.$_log_name;
    $_upload_path=empty($array_conf['upload_path'])?'/services/int_log':$array_conf['upload_path'];
    //hadoop的log
    $_hdfs_log_path=empty($array_conf['hdfs_log_path'])?'/services/serving_log/':$array_conf['hdfs_log_path'];
    $_hdfs_log_name=empty($array_conf['hdfs_log_name'])?'dfsmetrics.log':$array_conf['hdfs_log_name'];
    $deal_hdfs_log_file=$_hdfs_log_path.'/'.$_hdfs_log_name;
}

if ($_monitor_madn) {
    $_madn_monitor_urls=$array_conf['madn_url'];
    $_test_speed_urls=$array_conf['test_speed_url'];
}

//path
$_ls=empty($array_conf['path_top'])?'/bin/ls':$array_conf['path_top'];
$_find=empty($array_conf['path_top'])?'/usr/bin/find':$array_conf['path_top'];
$_top=empty($array_conf['path_top'])?'/usr/bin/top':$array_conf['path_top'];
$_nc=empty($array_conf['path_nc'])?'/usr/bin/nc':$array_conf['path_nc'];
$_printf=empty($array_conf['path_printf'])?'/usr/bin/printf':$array_conf['path_printf'];
$_df=empty($array_conf['path_df'])?'/bin/df':$array_conf['path_df'];
$_awk=empty($array_conf['path_awk'])?'/usr/bin/awk':$array_conf['path_awk'];
$_netstat=empty($array_conf['path_netstat'])?'/usr/bin/netstat':$array_conf['path_netstat'];
$_wc=empty($array_conf['path_wc'])?'/usr/bin/wc':$array_conf['path_wc'];
$_ifconfig=empty($array_conf['path_ifconfig'])?'/sbin/ifconfig':$array_conf['path_ifconfig'];
$_ipfw=empty($array_conf['path_ipfw'])?'/sbin/ipfw':$array_conf['path_ipfw'];
$_curl=empty($array_conf['path_curl'])?'/usr/bin/curl':$array_conf['path_curl'];
$_mysqladmin=empty($array_conf['path_mysqladmin'])?'/usr/local/bin/mysqladmin':$array_conf['path_mysqladmin'];
$_mysqlshow=empty($array_conf['path_mysqlshow'])?'/usr/local/bin/mysqlshow':$array_conf['path_mysqlshow'];
$_mysqlcheck=empty($array_conf['path_mysqlcheck'])?'/usr/local/bin/mysqlcheck':$array_conf['path_mysqlcheck'];
$_sh=empty($array_conf['path_sh'])?'/bin/sh':$array_conf['path_sh'];
$_bash=empty($array_conf['path_bash'])?'/usr/local/bin/bash':$array_conf['path_bash'];
$_expect=empty($array_conf['path_expect'])?'/usr/local/bin/expect':$array_conf['path_expect'];


//服务配置
if (!empty($array_conf['service_table'])) {
    $_service_table=(array)$array_conf['service_table'];
} else {
    $_service_table=array();
}

//链接配置,形式是servername=rule number
if (!empty($array_conf['link_table'])){
    $_link_table=(array)$array_conf['link_table'];
} else {
    $_link_table=array();
}

//log file
$auth_log_file = !empty($array_conf['auth_log_file']) ?$array_conf['auth_log_file'] :'/var/log/auth.log'; 

@exec("uname -pr",$os_version_info,$os_version_stat);
if ($os_version_stat==0) {
    DebugInfo(1,$debug_level,"[os version][{$os_version_info[0]}]");
} else {
    echo "call uname failed!\n";
    exit();
}
$fillrate_counter=empty($array_conf['fillrate_counter'])?100:$array_conf['fillrate_counter'];
?>
