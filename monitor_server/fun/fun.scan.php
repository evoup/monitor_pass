<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.scan.php
  +----------------------------------------------------------------------+
  | Comment:扫描的函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2011年12月 6日 星期二 17时27分39秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-12-13 10:51:10
  +----------------------------------------------------------------------+
 */

/**
 *@brief 观望一个潜在的问题事件若干分钟，确定它
 *@param $host 主机名
 *@param $eventCode 事件代码
 */
function determineEvent($host, $eventCode) {
    global $module_name;
    $eventCode=str_pad($eventCode, 4, "0", STR_PAD_LEFT); // 格式化为4位事件代码
    list($ConfScanIntervalSec, $ConfKeepWatchSec, $ConfTry)=getScanOpt($eventCode);
    // XXX 这里要计算出扫描的速度来确定一个合适的最小观望数值
    $ConfKeepWatchSec<60 && $ConfKeepWatchSec=180; // 设置观望描述小于1分钟的则视为3分钟 
    try {
        $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_ENGINE, __KEY_SCAN_DURATION, __MDB_COL_SCAN_DURATION);
        $scanDurtaion=$arr[0]->value;
    } catch (Exception $e) {
    }
    $ConfKeepWatchSec+=$scanDurtaion; // 增加扫描的持续时间确保足够的观望时间 
    SaveSysLog("[$module_name][keepwatch][ConfScanIntervalSec:$ConfScanIntervalSec][ConfKeepWatchSec:$ConfKeepWatchSec][ConfTry:$ConfTry]",4);
    // TODO ConfScanIntervalSec每n分钟扫描一次
    /*{{{已经认为needfix事件的就直接确定了*/
    if (hasEvent($host, $eventCode)) { 
        return true;
    }
    /*}}}*/
    $currentTime=time();
    $keepwatchKey=sprintf(__KEY_KEEPWATCH, $host, $eventCode);
    SaveSysLog("[$module_name][keepwatch][key:kpwc|{$host}|{$eventCode}]",4);
    // 取得观望事件的value
    try {
        $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_ENGINE, $keepwatchKey, __MDB_COL_KEEPWATCH);
        $keepwatchStr=$arr[0]->value;
        SaveSysLog("[$module_name][keepwatch][keepwatchStr:$keepwatchStr]",4);
    } catch (Exception $e) {
        SaveSysLog("[$module_name][keepwatch][get keepwatchStr error]",4);
        return false;
    }
    if (empty($keepwatchStr)) {
        $val="{$currentTime}#0"; // (记录第0次)
        $res=mdb_set(__MDB_TAB_ENGINE, __MDB_COL_KEEPWATCH, $keepwatchKey, $val);
        SaveSysLog("[$module_name][keepwatch][set keepwatchStr:$val]",4);
        if (!$res) {
            return false;
        }
    } else {
        list($lastKeepwatchedTime, $try)=explode("#", $keepwatchStr);
        SaveSysLog("[$module_name][keepwatch][currentTime:$currentTime][lastKeepwatchedTime:$lastKeepwatchedTime][try:$try]",4);
        if ($currentTime-$lastKeepwatchedTime<$ConfKeepWatchSec) {
            if ($try>=$ConfTry) {
                SaveSysLog("[$module_name][keepwatch][ensure event active][try:$try]",4);
                // 不管是否成功返回true，否则重复了
                return true;
            } else {
                $try++;
                $val="{$lastKeepwatchedTime}#{$try}";
                $res=mdb_set(__MDB_TAB_ENGINE, __MDB_COL_KEEPWATCH, $keepwatchKey, $val);
                SaveSysLog("[$module_name][keepwatch][add try:$try]",4);
            }
        } else {
            // 逾时重新记录
            SaveSysLog("[$module_name][keepwatch][time passed,refresh watch]",4);
            $res=mdb_set(__MDB_TAB_ENGINE, __MDB_COL_KEEPWATCH, $keepwatchKey, "");
        }
    }
    return false;
}

/**
 *@brief 主机是否存在问题事件(use to观望事件时,对存在于待处理事件列表内的事件,不在逾时重新记录时删除)
 *@param $host 主机名
 *@param $evcode 事件代码
 *@return true存在该问题事件,false不存在该问题事件
 */
function hasEvent($host, $evcode) {
    global $module_name;
    try {
        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, __KEY_NEEDFIX , __MDB_COL_EVENT);
        SaveSysLog("[$module_name][hasEvent]".$arr[0]->value,4);
        $needfixArr=array_filter((array)explode('|', $arr[0]->value));
        SaveSysLog("[$module_name][hasEvent][needfixArr:".serialize($needfixArr)."]",4);
        foreach ((array)$needfixArr as $problemEvent) {
            if ($evcode==$problemEvent) {
                $arr2 = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, "nf{$evcode}", __MDB_COL_EVENT);
                $problemSrvArr=explode('|', $arr2[0]->value);
                foreach ((array)$problemSrvArr as $tmpStr) {
                    list($problemSrv, $checkedtime)=explode('#', $tmpStr);
                    if ($problemSrv==$host) {
                        SaveSysLog("[$module_name][hasEvent][y]",4);
                        return true; 
                    }
                } 
            }
        }
    } catch (Exception $e) {
    }
    SaveSysLog("[$module_name][hasEvent][n]",4);
    return false;
}

/**
 *@brief 将观望事件计数器清零，for 事件恢复
 *@param $hst 主机名 
 *@param #evcode 事件代码
 */
function resetKeepWatchCount($hst, $evcode) {
    global $module_name;
    $row_key=sprintf(__KEY_KEEPWATCH, $hst, $evcode);
    if (mdb_set(__MDB_TAB_ENGINE, __MDB_COL_KEEPWATCH, $row_key, '')) {
        SaveSysLog("[$module_name][resetKeepWatchCount][ok]",4);
    } else {
        SaveSysLog("[$module_name][resetKeepWatchCount][fail]",4);
    }
}

/**
 *@brief 获取扫描的三个选项(扫描间隔、观望秒数、重试次数)
 *@param 事件代码(4位)
 */
function getScanOpt($evcode) {
    global $_CONFIG,$_EventConfArr,$module_name;
    $confSection=$_EventConfArr[$evcode];
    SaveSysLog("[$module_name][keepwatch][confSection:$confSection]",4);
    // 得到扫描间隔时间、观望时间、重试次数
    list($scanIntervalSec,$keepwatchSec,$try) = explode('|', $_CONFIG["$confSection"]['scan_opt']);
    $scanIntervalSec*=60; // 分钟转换为秒 
    $keepwatchSec*=60; // 同上 
    return array($scanIntervalSec, $keepwatchSec, $try);
}
?>
