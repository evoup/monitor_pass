#!/usr/local/php5_admin/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | Name:
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */

//全部导出的
//triggerid,expression,description,url,status,value,priority,lastchange,comments,error,templateid,type,state,flags

//实际需要的
//除了triggerid不要，其他都要

/**
 *@brief 添加模板
 */
function getTriggers() {
    $ret = [];
	if (($handle = fopen("triggers.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            //print_r($data);
            $ret[]=$data;
		}
		fclose($handle);
	}
    return $ret;
}
