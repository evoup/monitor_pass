<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/generate_serving_info.m
  +----------------------------------------------------------------------+
  | Comment:投放点信息收集
  +----------------------------------------------------------------------+
  | Author:Odin,Yinjia
  +----------------------------------------------------------------------+
  | Create:2009-09-30 08:13:25
  +----------------------------------------------------------------------+
  | Last-Modified: 2013-03-15 23:54:36
  +----------------------------------------------------------------------+
 */
$module_name='serving_info';

$total_request=0;
$total_time=0;
$total_error=0;
if (isset($total_log)) unset($total_log);
if (isset($total_campLog)) unset($total_campLog);
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
            /*{{{access_transfer_scribe ignore*/
            $tmpLen=sizeof(explode(" ",$log_content));
            if ($tmpLen>0) {
                $tmpLogContent=explode(" ",$log_content);
                if (strstr($tmpLogContent[$tmpLen-2],'access_transfer_scribe')) {
                    continue;
                }
            }
            /*}}}*/
            $final_content=end(explode(" ",$log_content));
            if(empty($final_content)==FALSE) {
                //include(dirname(__FILE__).'/read_log.m');
                include('read_log.m');
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
    exit();
}

//make update string
$serving_str=__FLAG_SERV.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;
$str_request=$total_time>0?round($total_request/$total_time,2):0;

//防止误报,大于$fillrate_counter才有意义
$rate_request+=$total_request;
if ($rate_request>=$fillrate_counter) {
    $err_rate=$total_error>0?round($total_error/$rate_request,2)*$fillrate_counter:0;
    $str_enginestat=$err_rate>30?0:1;
    $rate_request=0;    //归零
} else {
    //采样不足$fillrate_counter,所有状态都算正常,继续累积采样数
    $err_rate=0;
    $str_enginestat=1;
}

//防止误报,大于100才有意义
//$rate_request+=$total_request;
//if ($rate_request>=100) {
    //$err_rate=$total_error>0?round($total_error/$rate_request,2)*100:0;
    //$str_enginestat=$err_rate>30?0:1;
    //$rate_request=0;    //归零
//} else {
    ////采样不足100,所有状态都算正常,继续累积采样数
    //$err_rate=0;
    //$str_enginestat=1;
//}

$str_adimage=implode('|',$ArrayDeliver);
/*{{{计算selfservice的大小，提供服务端检查有无请求的依据*/
//$sslogDir='/services/serving_log/selfservice.log';
//$sslogMd5Arr=explode('= ', shell_exec("md5 $sslogDir"));
//$sslogMd5=substr($sslogMd5Arr[1],0,8); // 取前8位减少上传量 
//$sslog=basename($sslogDir);
/*}}}*/
$sslogMd5=''; // 不再计算md5，改为直接从服务端判断累计广告数和累计有活动的广告是否持续为0来判断日志创建失败
$sslog='';
//$str_log="$total_log|$total_log";
$str_log="$total_log|$total_campLog|$sslog|$sslogMd5"; // 累计广告数|累计有活动的广告|selfservices.log|selfservices.logMD5哈希值 
$str_traffic=$total_traffic;

$serving_str.=$str_request.__SOURCE_SPLIT_TAG2.$str_adimage.__SOURCE_SPLIT_TAG2.$str_log.__SOURCE_SPLIT_TAG2.$str_traffic.__SOURCE_SPLIT_TAG2.$str_enginestat;

if (!empty($serving_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$serving_str;
    else $upload_str=$serving_str;
    unset($serving_str);
}
?>
