<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/read_log.m
  +----------------------------------------------------------------------+
  | Comment:读取log信息
  +----------------------------------------------------------------------+
  | Author:Odin,Yinjia
  +----------------------------------------------------------------------+
  | Create:2009-09-30 10:11:25
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-01-18 14:24:16
  +----------------------------------------------------------------------+
*/
$module_name='readlog';

$log_pieces=explode('|',$final_content);
$log_tag=$log_pieces[0];
DebugInfo(4,$debug_level,"[$module_name]::[final_content:$final_content]");
if ($log_tag==__LOGTAG_PF) {
    //访问数
    $total_read+=(int)$log_pieces[2];
    $total_request+=(int)$log_pieces[3];
    $total_time+=floatval($log_pieces[6]);
    $total_error+=(int)$log_pieces[4];
    DebugInfo(3,$debug_level,"[$module_name]::[total_read:$total_read]-[total_request:$total_request]-[total_time:$total_time]-[total_error:$total_error]");
} elseif ($_monitor_delivery && $log_tag==__LOGTAG_READ && $domain_id=$log_pieces[1]) {
    //广告发布接收
    $ArrayDeliver[$domain_id]="$domain_id,{$log_pieces[2]},{$log_pieces[3]},{$log_pieces[4]},".substr($log_pieces[5],0,16).substr($log_pieces[6],16,16).",1";
} elseif ($_monitor_delivery && $log_tag==__LOGTAG_DELIVER) {
    //广告发布生成
    $ArrayDeliver[$domain_id]="$domain_id,{$log_pieces[2]},{$log_pieces[3]},{$log_pieces[4]},".substr($log_pieces[5],0,16).substr($log_pieces[6],16,16).",0";
} elseif ($log_tag==__LOGTAG_LOG) {
    //传送log
    $total_log+=(int)$log_pieces[2]; // 累计广告请求数 
    $total_campLog+=(int)$log_pieces[4]; // 累计有活动的广告请求数量(for 计算填充率) 
}
?>
