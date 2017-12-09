<?php
/*
  +----------------------------------------------------------------------------+
  | Name:madmonitor.m
  +----------------------------------------------------------------------------+
  | Comment:生成监控数据(require，curl & bash)
  +----------------------------------------------------------------------------+
  | Author:Odin,Yinjia
  +----------------------------------------------------------------------------+
  | Compile: pcc -v --static -c /etc/pcc_mysql.conf madmonitor.m -o madmonitor
  +----------------------------------------------------------------------------+
  | Commit Notice: 每次提交SVN注意修改__SVNVER常量定义，在inc/inc.monitor.m
  +----------------------------------------------------------------------------+
  | Others: 用phc编译，include('read_log.m');
  +----------------------------------------------------------------------------+
  | Create:2009-09-15 13:46:10
  +----------------------------------------------------------------------------+
  | Last Modified: 2012-10-31 15:47:18
  +----------------------------------------------------------------------------+
 */
include_once('inc/inc.monitor.m');
include_once('inc/inc.domain.m');
include_once('fun/fun.common.m');
include_once('fun/fun.fs.m');
include_once('fun/fun.mcd.m');
include_once('fun/fun.monitor.m');
include_once('fun/fun.security.m');
list($process_name,$ext_name)=explode('.',basename(__FILE__));
include_once('modules/monitor_init.m');

chdir($work_dir);
if (!empty($socket_send_server) && !empty($socket_send_port)) {
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($socket, $socket_send_server, $socket_send_port);
}
while ($run) {
    $now=time();

    /*** read status file***/
    if ($fp=@fopen($status_file,"rb")) {
        flock($fp,LOCK_SH);
        $last_status=trim(fread($fp,filesize($status_file)));
        list($last_ustamp,$last_offset,$last_inode)=explode('|',$last_status);
        fclose($fp);
        $debug_data="[$process_name]::[last_time:".date("Y-m-d H:i:s",$last_ustamp)."]-[last_offset:$last_offset]-[last_inode:$last_inode]";
        DebugInfo(1,$debug_level,$debug_data);
    }

    //build upload string
    $upload_str='';

    //读取服务器信息
    include_once('modules/domain/cls_generic.php');
    include('modules/generate_server_info.m');

    //读取DB信息
    if ($_monitor_mysql) {
        include('modules/generate_mysql_info.m');
    }

    //serving node/delivery node
    if ($_monitor_serv) {
        include('modules/generate_serving_info.m');
    }

    //admin node
    if ($_monitor_manage) {
        include('modules/generate_manage_info.m');
    }

    //report
    if ($_monitor_report) {
        include('modules/generate_report_info.m');
    }

    // madn
    if ($_monitor_madn) {
        include('modules/generate_madn_info.m');
    }
    // security
    if ($_monitor_security) {
        include('modules/security/generate_network_info.m');
        include('modules/security/generate_passwd_info.m');
    }
    // hadoop
    if ($_monitor_hadoop) {
        include('modules/generate_hadoop_info.m');
    }

    //上传信息
    if (!empty($upload_str)) {
        if (!empty($socket_send_server) && !empty($socket_send_port)) {
            print_r($server_metrics);
            socket_send($socket,'foo',3,0);
        }
        $command_upload="$_curl -d \"$upload_str\" -H \"Host: $http_host\" \"$upload_url\" 2>>/dev/null";
        DebugInfo(3,$debug_level,"[$process_name]::[command_upload:$command_upload]");
        @exec($command_upload);
        /*{{{ 定时版本自检以触发自动更新 */
        if ($auto_update) {
            $updateCounter=time()-$lastUpdateTime;
            DebugInfo(3,$debug_level,"[$process_name]::[auto update:on][lastUpdateTime:$lastUpdateTime][updateCounter:$updateCounter][update_check_time:$update_check_time]");
            if ($updateCounter>$update_check_time) {
                DebugInfo(3,$debug_level,"[$process_name]::[has passed update_check_time,will query update server]");
                $command_upload="$_curl -d \"host=$_server_name&clientVer=".__VERSION.'&'.
                    'confContent='.base64_encode(file_get_contents($conf_file)).
                    "\" -H \"Host: $http_host\" \"$update_url\" 2>>/dev/null";
                DebugInfo(3,$debug_level,"[$process_name]::[command_upload:$command_upload]");
                if(!$res=exec($command_upload)) {
                    DebugInfo(3,$debug_level,"[$process_name]::[update server unreachable!][update_url:$update_url]");
                } else {
                    list($currentVersion,$downUrl)=explode('#',$res);
                    DebugInfo(3,$debug_level,"[$process_name]::[accessed update server][update_url:$update_url][server ret current version:$currentVersion][my version:".__VERSION."]");
                    if ($currentVersion!=__VERSION && !empty($currentVersion) && is_numeric($currentVersion)) {
                        DebugInfo(3,$debug_level,"[$process_name]::[need update,call the updater program.]");
                        DebugInfo(3,$debug_level,"[$process_name]::[start download from $downUrl.]");
                        shell_exec(__PROC_ROOT.'/madmonitorUpdater');
                    } else {
                        DebugInfo(3,$debug_level,"[$process_name]::[version equaled,already newest version.]");
                    }
                }
                $lastUpdateTime=time(); 
            } else {
                DebugInfo(3,$debug_level,"[$process_name]::[need`nt access update server now]");
            }
            if (empty($lastUpdateTime)) {
                $lastUpdateTime=time();
            }
        }
        /* }}} */
    } else {
        DebugInfo(1,$debug_level,"[$process_name]::[upload_str:null]");
    }
    $command_get_testspeed_conf="$_curl -m 10 -H \"Host: $http_host\" \"$testspeedconf_url\" 2>>/dev/null";
    if ( !$res=exec($command_get_testspeed_conf) ) {
        DebugInfo(3,$debug_level,"[$process_name]::[get testspeedconf unreachable!][testspeedconf_url:$testspeedconf_url]");
    } else {
        unset($allSite);
        $siteUrlArr=explode('#',$res);
        if ( !empty($siteUrlArr) ) {
            foreach ( (array)$siteUrlArr as $siteUrl ) {
                list($site,$url)=explode('|',$siteUrl);
                $allSite["{$site}"]=base64_decode($url);
            }
            foreach ( (array)$allSite as $site => $url ) {
                $confLine.="{$site}site=\"$url\"\n";
            }
            if ( !empty($res) ) {
                file_put_contents('/services/monitor_deal/conf/testspeed.conf',$confLine);
            }
            unset($confLine);
            DebugInfo(3,$debug_level,"[$process_name]::[get testspeedconf][testspeedconf_url:$testspeedconf_url][ok]");
        } else {
            DebugInfo(3,$debug_level,"[$process_name]::[get testspeedconf][testspeedconf_url:$testspeedconf_url][site empty]");
        }
    }

    //update status
    $tmp_status="$now|$cur_offset|$read_inode";
    if ($fp=@fopen($status_file,"wb")) {
        fputs($fp,$tmp_status);
        ftruncate($fp,strlen($tmp_status));
        fclose($fp);
    }

    //continue?
    if (!$daemon_stat || $proc_life<=($process_old=$now-$start_time)) {
        $run=false;
    } else {
        sleep($sleep);
    }
}
?>
