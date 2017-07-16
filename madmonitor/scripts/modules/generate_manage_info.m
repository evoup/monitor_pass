<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/generate_manage_info.m                                  |
  +----------------------------------------------------------------------+
  | Comment:监控管理界面                                                 |
  +----------------------------------------------------------------------+
  | Author:Odin                                                          |
  +----------------------------------------------------------------------+
  | Create:2009-10-11 22:18:18                                           |
  +----------------------------------------------------------------------+
  | Last-Modified:2009-10-11 22:18:23                                    |
  +----------------------------------------------------------------------+
*/
$module_name='manage_info';

$manage_str=__FLAG_MANAGE.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;

//web server status
$str_webserver=($web_server_status==true)?'1':'0';

//守护进程执行的状态
$_daemon_status_files=empty($array_conf['daemon_status_file'])
    ?(array)'/services/mhDaemons/status/mhDaemon.status'
    :(array)explode(',',$array_conf['daemon_status_file']);
$str_daemon='1';
foreach($_daemon_status_files as $_daemon_status_file) {
    if ('1'!==$str_daemon) {
        break;
    }
    $handle=@fopen($_daemon_status_file,"r");
    if ($handle) {
        $fstat=fstat($handle);
        $daemon_ts=$fstat['mtime'];
        if ($now-$daemon_ts>600) { //status文件在最近一次执行之后被修改 
            $str_daemon='0'; //异常（可能卡住）
            DebugInfo(2,$debug_level,"[$module_name]::[timestamp:$daemon_ts][now:$now][str_daemon:$str_daemon]-[$_daemon_status_file]");
        } else {
            $str_daemon='1'; //正常 
            DebugInfo(2,$debug_level,"[$module_name]::[timestamp:$daemon_ts][now:$now][str_daemon:$str_daemon]-[$_daemon_status_file]");
        }
        fclose($handle);
    } else {
        @$str_daemon='0'; //异常 
        DebugInfo(2,$debug_level,"[$module_name]::[open status file err][str_daemon:$str_daemon]-[$_daemon_status_file]");
        break;
    }
}

//login,检查主库的t_user表
$cmd_chk_login="$_mysqlcheck -u$mysql_user -p$mysql_pass -h$mysql_host $login_db $login_table | $_grep  -i 'OK' | $_wc -l";
$login_chk_result=@exec($cmd_chk_login);
$login_chk_status=(int)trim($login_chk_result);
DebugInfo(2,$debug_level,"[$module_name]::[cmd_chk_login:$cmd_chk_login]-[result:$login_chk_result]");
if ($login_chk_status>0) {
    $str_login='1';
} else {
    $str_login='0';
}

if ($str_webserver=='1') {
    $str_adserv='1';
} else {
    $str_adserv='0';
}

//error log rate
$total_read=0;
$total_request=0;
$total_time=0;
$total_error=0;
if (isset($total_log)) unset($total_log);
$ArrayDeliver=array();
if (file_exists($deal_log_file) && false!==($readlog_info=readInfo($last_ustamp,$last_offset,$last_inode,$now,$_log_name,$_log_path))) {
    DebugInfo(2,$debug_level,"[$process_name][$module_name]::[log:$deal_log_file]-[read_it]");
    $read_log_file=$readlog_info['file'];
    $read_stat=$readlog_info['read'];
    $read_offset=$readlog_info['offset'];
    $read_inode=fileinode($read_log_file);

    DebugInfo(1,$debug_level,"[$process_name]::[file:$read_log_file]-[read_inode:$read_inode]-[start_to_read:".(($read_stat===true)?$read_offset:'skip')."]");

    if ($read_stat && file_exists($read_log_file) && $fp0=@fopen($read_log_file,"rb")) {
        fseek($fp0,$read_offset);
        while (!feof($fp0)) {
            $log_content=trim(fgets($fp0,1024));
            $final_content=end(explode(" ",$log_content));
            if(empty($final_content)==FALSE) {
                include('modules/read_log.m');
            }
            $cur_offset=ftell($fp0);
            $read_size=$cur_offset-$read_offset;
            if ($read_size>$_max_size) {
                break;
            }
        }
        fclose($fp0);
    } else {
        $cur_offset=$last_offset;
        DebugInfo(1,$debug_level,"[$module_name]::[inode:$read_inode]-[offset:$cur_offset]-[not_read_this_time]");
    }
} else {
    DebugInfo(2,$debug_level,"[$process_name][$module_name]::[log:$deal_log_file]-[error]");
}
$err_rate=($total_error>0 && $total_read>0)?round($total_error/$total_read,2)*100:100;
$str_errlog=$err_rate<70?0:1;

$manage_str.=$str_webserver.__SOURCE_SPLIT_TAG2.$str_daemon.__SOURCE_SPLIT_TAG2.$str_login.__SOURCE_SPLIT_TAG2.$str_adserv.__SOURCE_SPLIT_TAG2.$str_errlog;

if (!empty($manage_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$manage_str;
    else $upload_str=$manage_str;
    unset($manage_str);
}
?>
