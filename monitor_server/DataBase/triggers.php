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
//triggerid,expression,description,url,status,value,priority,lastchange,comments,error,templateid,type,state,flags,hostid

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

// 触发器id,表达式，描述，url，状态，数值、优先级、最后变更、注释、错误、模板id，类型、状态、标记、真模板id
//$triggersData = getTriggers();
//$triggers=[];
//for($i=1;$i<sizeof($triggersData);$i++) {
    //list($triggerid,$expression,$description,$url,$status,$value,$priority,$lastchange,$comments,$error,$templateid,$type,$state,$flags)=$triggersData[$i];
    //echo "add trigger:${triggerid}\n";
    //$rowkey = "$triggerid"; 
    //$mutations = [];

//}
