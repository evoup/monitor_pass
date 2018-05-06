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


//host
//status
//disable_until
//error
//available
//errors_from
//lastaccess
//snmp_disable_until
//snmp_available
//snmp_errors_from
//snmp_error
//jmx_disable_until
//jmx_available
//jmx_errors_from
//jmx_error
//name
//flags
//templateid


/**
 *@brief 添加模板
 */
function getTemplate() {
    $ret = [];
	if (($handle = fopen("template.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            //print_r($data);
            $ret[]=$data;
		}
		fclose($handle);
	}
    return $ret;
}
