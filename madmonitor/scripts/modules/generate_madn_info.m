<?php
/*
  +----------------------------------------------------------------------+
  | Name:generate_madn_info.m
  +----------------------------------------------------------------------+
  | Comment:收集MADN信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 7月17日 星期二 18时38分01秒 CST
  +----------------------------------------------------------------------+
  | Last Modified: 2013-06-05 14:43:28
  +----------------------------------------------------------------------+
 */
$module_name='madn_info';
// 构造异步方式请求的shell
$procDir=__PROC_ROOT;


/* MADN连通性监测 */
foreach ( $_madn_monitor_urls as $monitorItem => $monitor_url ) {
    list($monitor_url,$hostname)=array_pad(explode(',',$monitor_url),2,"");
    $partFile="{$procDir}/work/{$monitorItem}StatusCode.part";
    $doneFile="{$procDir}/work/{$monitorItem}StatusCode";
    if ( file_exists($partFile) ) {
        @copy($partFile,$doneFile); // 连通性测试完成的文件复制一份 
    }
    //-k支持SSL，注意CURL --version中的Features: 需要带SSL
    $hostname=str_replace('"','',$hostname);
    if (!empty($hostname)) {
        $getShell=<<<EOT
{$_bash} -c '({$_curl} -k -o /dev/null -s -m 10 -H "Host: {$hostname}" --connect-timeout 10 -w %{http_code}\|%{size_download} "{$monitor_url}") & sleep 10 ; kill $! >> /dev/null 2>&1' > {$procDir}/work/{$monitorItem}StatusCode.part &
EOT;
    } else {
        $getShell=<<<EOT
{$_bash} -c '({$_curl} -k -o /dev/null -s -m 10 --connect-timeout 10 -w %{http_code}\|%{size_download} "{$monitor_url}") & sleep 10 ; kill $! >> /dev/null 2>&1' > {$procDir}/work/{$monitorItem}StatusCode.part &
EOT;
    }
    file_put_contents(__PROC_ROOT."/work/{$monitorItem}",$getShell);
    chmod("{$procDir}/work/{$monitorItem}",'755');
    unset($monitor_url,$hostname);
}
usleep(10000);
foreach ($_madn_monitor_urls as $monitorItem=>$monitor_url) {
    list($monitor_url,$hostname)=array_pad(explode(',',$monitor_url),2,"");
    // 针对每一个监控的url，派生出一个后台进程执行上面的监控shell
    shell_exec('nohup sh /services/monitor_deal/work/'.$monitorItem.' >> /dev/null');
    $statusFile='/services/monitor_deal/work/'.$monitorItem.'StatusCode';
    $tmpInfo=@explode(__SOURCE_SPLIT_TAG3,file_get_contents($statusFile));
    $tmpInfo=array_pad($tmpInfo,2,"");
    $statusCode=$tmpInfo[0];
    $downSize=$tmpInfo[1];
    $urlStatus[]="{$monitorItem}|".base64_encode($monitor_url)."|{$statusCode}|{$downSize}";
    unset($monitor_url,$hostname);
}
$madn_str=__FLAG_MADN.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;
$madn_str.=join(__SOURCE_SPLIT_TAG2,$urlStatus);
unset($urlStatus);

/* MADN访问速度监测 */
$_addon_testspeed_conf=parse_ini_file('/services/monitor_deal/conf/testspeed.conf');
$_test_speed_urls=array_merge($_test_speed_urls,$_addon_testspeed_conf);
if ( empty($queueTestSpeed) ) {
    foreach ($_test_speed_urls as $testSpeedItem=>$test_speed_url) {
        $queueTestSpeed[]=join('|',array($testSpeedItem,$test_speed_url));
    }
} else {
    list($testSpeedItem,$test_speed_url)=explode('|',$queueTestSpeed[0]);
    DebugInfo(1,$debug_level,"[$process_name]::[queueTestSpeed][currentItem:$testSpeedItem][currentURL:$test_speed_url]");
    array_shift($queueTestSpeed);
}


foreach ($_test_speed_urls as $testSpeedItemTmp=>$test_speed_urlTmp) {
    $partFile="{$procDir}/work/_{$testSpeedItemTmp}TestSpeed.part";
    $doneFile="{$procDir}/work/_{$testSpeedItemTmp}TestSpeed";
    if ( file_exists($partFile) ) {
        @copy($partFile,$doneFile); // 测速完成的文件复制一份 
    }
}
$getShell=<<<EOT
{$_bash} -c '({$_curl} -o /dev/null -s -r 0-10240 -m 10 --connect-timeout 10 -w %{http_code}\|%{size_download}\|%{time_total} "{$test_speed_url}") & sleep 10 ; kill $! >> /dev/null 2>&1' > {$procDir}/work/_{$testSpeedItem}TestSpeed.part &
EOT;
file_put_contents(__PROC_ROOT."/work/_{$testSpeedItem}",$getShell);
chmod("{$procDir}/work/_{$testSpeedItem}",'755');
usleep(10000);
// 针对每一个测速的url，派生出一个后台进程执行上面的测速shell
shell_exec('nohup sh /services/monitor_deal/work/_'.$testSpeedItem.' >> /dev/null');
$speedFile='/services/monitor_deal/work/_'.$testSpeedItem.'TestSpeed';
$speed=@file_get_contents($speedFile);
if ( strlen($testSpeedItem)>4 ) { // 去掉ini中的键site的后缀
    $testSpeedItem=substr($testSpeedItem,0,strlen($testSpeedItem)-4);
    $urlSpeed[]="{$testSpeedItem}|".base64_encode($test_speed_url)."|{$speed}";
}
//}
$madn_str.=__SOURCE_SPLIT_TAG4.join(__SOURCE_SPLIT_TAG2,$urlSpeed);
unset($urlSpeed);


if (!empty($madn_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$madn_str;
    else $upload_str=$madn_str;
    unset($madn_str);
}
?>
