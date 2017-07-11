<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun/downloadsFun.m                                                
  +----------------------------------------------------------------------+
  | Comment:download的数据的函数                                            
  +----------------------------------------------------------------------+
  | Author:evoup                                                         
  +----------------------------------------------------------------------+
  | Created:
  +----------------------------------------------------------------------+
  | Last-Modified: 2013-05-16 16:30:51
  +----------------------------------------------------------------------+
 */
$GLOBALS['httpStatus']=__HTTPSTATUS_OK;
header("Content-type: application/json; charset=utf-8");

$out=array(
    "madmonitor1.0-FreeBSD8.x-amd64.bz2"=>"http://27.115.15.8/mmsapi_beta2/get/get_download_file/@self/madmonitor1.0-FreeBSD8.x-amd64.bz2",
    "madmonitor1.0-FreeBSD9.x-amd64.bz2"=>"http://27.115.15.8/mmsapi_beta2/get/get_download_file/@self/madmonitor1.0-FreeBSD9.x-amd64.bz2"
);
echo json_encode($out);
?>
