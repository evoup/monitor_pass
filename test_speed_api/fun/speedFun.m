<?php
/*
  +----------------------------------------------------------------------+
  | Name:speedFun.m
  +----------------------------------------------------------------------+
  | Comment: 测速函数 
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2012年 8月31日 星期五 13时21分10秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-19 11:28:53
  +----------------------------------------------------------------------+
 */
$module_name='speedFun';
$GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; // 默认返回400 
header("Content-type: application/json; charset=utf-8");
$err=false;
$allSiteSpeed=array(); 
DebugInfo("[$module_name][query][single_redis_server:".json_encode($single_redis_server)."]",2);
switch ( $GLOBALS['selector'] ) {
case( __SELECTOR_MASS ):
    if ($GLOBALS['operation'] == __OPERATION_READ) {
        //合法的post参数
        $valid_key = array(
            'date'
        ); 
        if ( isset($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') { // 要求上传数据非空和请求类型 
            // 检查必要的参数
            foreach ($valid_key as $needed) {
                if ( !in_array($needed, array_keys($_POST)) ) { // 对少传判断为非法
                    $err = true;
                }
            }
            list($dayStart,$dayEnd)=explode(',',$_POST['date'],2);
            $dayStart=intval(strtotime(str_pad($dayStart, 8, "0", STR_PAD_LEFT)));
            $dayEnd=intval(empty($dayEnd)?$dayStart:strtotime(str_pad($dayEnd, 8, "0", STR_PAD_LEFT)));
            if ($dayEnd-$dayStart>0) {
                $GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; 
            }
            DebugInfo("[$module_name][query][dayStart:$dayStart][dayEnd:$dayEnd]",2);
            if ( !$err ) {
                DebugInfo("[$module_name][query][starting query all site]",2);
                $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
                // 查询各站点该日期的统计速度
                for ($day=$dayStart;$day<=$dayEnd;$day=strtotime("+1 day","$day")) {
                    try {
                        DebugInfo("[$module_name][start get]",2);
                        //$arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED_HISTORY,$day,'info:count');
                        //DebugInfo("[$module_name][end get]",2);
                        //$countJson=$arr[0]->value;
                        $GLOBALS['redis_client']->select(__REDIS_TABLE_SPEED);
                        $countJson=$GLOBALS['redis_client']->get($day);
                        foreach ( (array)json_decode($countJson) as $date => $siteInfo ) {
                            foreach ( (array)$siteInfo as $site => $speedInfo ) {
                                $allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed']=$speedInfo->lspeed;
                                $allSiteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$speedInfo->hspeed;
                                if ($speedInfo->lspeed>$speedInfo->hspeed) {
                                    $allSiteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed'];
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $GLOBALS['httpStatus']=__HTTPSTATUS_INTERNAL_SERVER_ERROR;
                        return;
                    }
                }
                echo json_encode($allSiteSpeed);
            }
        }
    }
    break;
case( __SELECTOR_SINGLE ):
    if ($GLOBALS['operation'] == __OPERATION_READ) {
        if (isset($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') { // 要求上传数据非空和请求类型 
            //合法的post参数
            $valid_key = array(
                'date',
                'site'
            ); 
            // 检查必要的参数
            foreach ($valid_key as $needed) {
                if ( !in_array($needed, array_keys($_POST)) ) { // 对少传判断为非法
                    $err = true;
                }
            }
            list($dayStart,$dayEnd)=explode(',',$_POST['date'],2);
            $dayStart=intval(strtotime(str_pad($dayStart, 8, "0", STR_PAD_LEFT)));
            $dayEnd=intval(empty($dayEnd)?$dayStart:strtotime(str_pad($dayEnd, 8, "0", STR_PAD_LEFT)));
            if ($dayEnd-$dayStart>0) {
                $GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST;
            }
            DebugInfo("[$module_name][query][dayStart:$dayStart][dayEnd:$dayEnd]",2);
            if ( !$err ) {
                DebugInfo("[$module_name][query][starting query single site]",2);
                $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
                for ($day=$dayStart;$day<=$dayEnd;$day=strtotime("+1 day","$day")) {
                    try {
                        //$arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED_HISTORY,$day,'info:count');
                        //$countJson=$arr[0]->value;
                        $GLOBALS['redis_client']->select(__REDIS_TABLE_SPEED);
                        $countJson=$GLOBALS['redis_client']->get($day);
                        foreach ( (array)json_decode($countJson) as $date => $siteInfo ) {
                            foreach ( (array)$siteInfo as $site => $speedInfo ) {
                                if ( $site==$_POST['site'] ) {
                                    $siteSpeed[date('Y-m-d',$day)][$site]['lspeed']=$speedInfo->lspeed;
                                    $siteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$speedInfo->hspeed;
                                    if ($speedInfo->lspeed>$speedInfo->hspeed) {
                                        $siteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed'];
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $GLOBALS['httpStatus']=__HTTPSTATUS_INTERNAL_SERVER_ERROR;
                        return;
                    }
                }
            }
            echo json_encode($siteSpeed);
        }
    }
    break;
case( __SELECTOR_BATCH ):
    if ($GLOBALS['operation'] == __OPERATION_READ) {
        //合法的post参数
        $valid_key = array(
            'site',
            'date'
        ); 
        if ( isset($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') { // 要求上传数据非空和请求类型 
            // 检查必要的参数
            foreach ($valid_key as $needed) {
                if ( !in_array($needed, array_keys($_POST)) ) { // 对少传判断为非法
                    $err = true;
                }
            }
            $sites=(array)explode(',',$_POST['site']);
            list($dayStart,$dayEnd)=explode(',',$_POST['date'],2);
            $dayStart=intval(strtotime(str_pad($dayStart, 8, "0", STR_PAD_LEFT)));
            $dayEnd=intval(empty($dayEnd)?$dayStart:strtotime(str_pad($dayEnd, 8, "0", STR_PAD_LEFT)));
            if ($dayEnd-$dayStart>0) {
                $GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; 
            }
            DebugInfo("[$module_name][query][dayStart:$dayStart][dayEnd:$dayEnd]",2);
            if ( !$err ) {
                DebugInfo("[$module_name][query][starting query all site]",2);
                $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
                // 查询各站点该日期的统计速度
                for ($day=$dayStart;$day<=$dayEnd;$day=strtotime("+1 day","$day")) {
                    try {
                        //$arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED_HISTORY,$day,'info:count');
                        //$countJson=$arr[0]->value;
                        $GLOBALS['redis_client']->select(__REDIS_TABLE_SPEED);
                        $countJson=$GLOBALS['redis_client']->get($day);
                        foreach ( (array)json_decode($countJson) as $date => $siteInfo ) {
                            foreach ( (array)$siteInfo as $site => $speedInfo ) {
                                if ( in_array($site,$sites) ) {
                                    $allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed']=$speedInfo->lspeed;
                                    $allSiteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$speedInfo->hspeed;
                                    if ($speedInfo->lspeed>$speedInfo->hspeed) {
                                        $allSiteSpeed[date('Y-m-d',$day)][$site]['hspeed']=$allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed'];
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $GLOBALS['httpStatus']=__HTTPSTATUS_INTERNAL_SERVER_ERROR;
                        return;
                    }
                }
                echo json_encode($allSiteSpeed);
            }
        }
    }
    break;
default:
    break;
}
?>
