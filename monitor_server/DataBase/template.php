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
//hostid,proxy_hostid,host,status,disable_until,error,available,errors_from,lastaccess,ipmi_authtype,ipmi_privilege,ipmi_username,ipmi_password,ipmi_disable_until,ipmi_available,snmp_disable_until,snmp_available,maintenanceid,maintenance_status,maintenance_type,maintenance_from,ipmi_errors_from,snmp_errors_from,ipmi_error,snmp_error,jmx_disable_until,jmx_available,jmx_errors_from,jmx_error,name,flags,templateid

//实际需要的
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
