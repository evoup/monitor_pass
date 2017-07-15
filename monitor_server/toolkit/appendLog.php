#!/usr/local/php5_admin/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | Name:appendLog.php
  +----------------------------------------------------------------------+
  | Comment:模拟广告日志数据递增
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2012年 1月18日 星期三 10时25分55秒 CST 
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-01-18 10:26:09
  +----------------------------------------------------------------------+
 */
$productWellFillRatePct=$argv[1];
if (empty($productWellFillRatePct)) {
    fwrite(STDERR, "请输入一个产生良好fillrate的几率百分数\n");
    die;
}
$logFile='/services/serving_log/selfservice.log';
while (1) {
    $tm=time();
    $d=date('M d H:i:s', $tm);
    $version=" ycserver7 MHC-2.0.0[19393]: ";
    $hash=substr(md5(mt_rand(1,9999)), 0, 9);
    $camp=(mt_rand(0,100)<=$productWellFillRatePct) ?'90000154' :'';
    $logData="$camp|90000028||1|99992403317957|211862|208.54.44.201|$hash|$tm";
    $tail="|1.0.3.762|356357046446468\n";
    $logData=$d.$version.$logData.$tail;
    echo $logData."\n";
    echo shell_exec("echo '{$logData}' >> {$logFile}");
    sleep(0.2);
}
// TODO 转换为守护进程
?>
