<?php
/*
  +----------------------------------------------------------------------+
  | Name:servingSaveData.php
  +----------------------------------------------------------------------+
  | Comment:保存客户端RAWPOST上传的监控数据
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-02-20 15:21:03
  +----------------------------------------------------------------------+
 */

$sub_module_name='servingSaveData';

foreach ($run_serving_info as $serving_info) {
    mdbUpdateSrvList($serving_info->m_server, __MONITOR_TYPE_SERVING); // 更新全局服务器列表 
    $cust_group_names=belongCustomizeGroup($serving_info->m_server);
    if (false==$cust_group_names) { // 默认组(1~5的组)，直接按照监控类型设置服务器cache 
        SaveSysLog("[$sub_module_name][mdb_set][montype:".__MONITOR_TYPE_SERVING."][server:".$serving_info->m_server."][message:".($serving_info->m_client_message)."]",3);
        /*{{{ 存服务器上传时间戳
         */
        $res=mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_LASTTIME, $serving_info->m_server), time());
        if (false===$res) {
            SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp failed!]",3);
        } else {
            SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp ok!]",4);
        }
        /* }}} */
        /*{{{ 存客户端监控信息
         */
        $res = mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_CLIENT_MSG, __MONITOR_TYPE_SERVING, $serving_info->m_server),
            $serving_info->m_client_message);
        if (false===$res) {
            SaveSysLog("[$module_name][$sub_module_name][mdb set client msg failed!]",3);
        } else {
            SaveSysLog("[$module_name][$sub_module_name][mdb set client msg ok!]",4);
        }
        /*}}}*/
        SaveSysLog("[$sub_module_name][saving branch:default group][group num:".__MONITOR_TYPE_GENERIC."]",4);
    } else { // 按照组名设置服务器cache(服务器cache用来判断down机和提取信息以检测问题)
        foreach ($cust_group_names as $cust_group_name) { // TODO 这里没必要存这么多，优化 
            // key的格式 组名|服务器名 如cust_group|cust_server1 
            /*{{{ 存服务器上传时间戳
             */
            $res = mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_LASTTIME, $serving_info->m_server), time());
            if (false===$res) {
                SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp failed!]",3);
            } else {
                SaveSysLog("[$module_name][$sub_module_name][mdb set timestamp ok!]",4);
            }
            /*}}}*/
            /*{{{ 存客户端监控信息
             */
            $res = mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, sprintf(__KEY_CLIENT_MSG, $cust_group_name, $serving_info->m_server),
                $serving_info->m_client_message);
            if (false===$res) {
                SaveSysLog("[$module_name][$sub_module_name][mdb set client msg failed!]",3);
            } else {
                SaveSysLog("[$module_name][$sub_module_name][mdb set client msg ok!]",4);
            }
            /*}}}*/
        }
        SaveSysLog("[$sub_module_name][saving branch:customize group][group name:".$cust_group_name."]",4);
    }
    /* {{{ 存主机状态(在线状态、上次上传时间) for display 
     */
    mdbSaveHostStatus($serving_info->m_server);
    /* }}} */

    /* {{{ 存即时信息表
     */
    saveMonitorInfo($serving_info, __MONITOR_TYPE_SERVING);
    /* }}} */
    /* {{{ 存历史信息表
     */
    mdbSaveMonitorHistoryInfo($serving_info, __MONITOR_TYPE_SERVING);
    /* }}} */
    /* {{{ 存上传的loginfo的累计广告数和上传广告数  // TODO 目前只支持一个log文件处理(见客户端selfservice.log)，需要支持多个
     */
    $row_key=sprintf(__KEY_SERVING_LOGINFO,$serving_info->m_server);
    // 取出上次存的累计广告数|累计有活动的广告
    try {
        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $row_key, __MDB_COL_EVENT);
    } catch (Exception $e) {
    }
    list($total_log_num,$upload_log_num,$empty_counter)=explode('#',$arr[0]->value);
    SaveSysLog("[$sub_module_name][$row_key][previous total_log_num:$total_log_num][previous upload_log_num:$upload_log_num][current total_log_num:{$serving_info->m_loginfo['total_log_num']}][current upload_log_num:{$serving_info->m_loginfo['upload_log_num']}]",4);
    if ($total_log_num==='0') { // 上次累计广告数为空存当前的 
        if (!empty($serving_info->m_loginfo['total_log_num'])) {
            SaveSysLog("[$module_name][$sub_module_name][branch_A!]",4);
            $empty_counter='0';  // 本次不空，计数器清零 
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serving_info->m_loginfo['total_log_num'].'#'.$serving_info->m_loginfo['upload_log_num'].'#'.$empty_counter);
        } else {
            $empty_counter<__MAX_COUNTER && $empty_counter++; // 本次空，计数器加1 
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serving_info->m_loginfo['total_log_num'].'#'.$serving_info->m_loginfo['upload_log_num'].'#'.$empty_counter);
        }
    } elseif (empty($total_log_num) && $total_log_num!=='0') {
        // 首次上传
        SaveSysLog("[$module_name][$sub_module_name][branch_B!]",4);
        $empty_counter='0';
        mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serving_info->m_loginfo['total_log_num'].'#'.$serving_info->m_loginfo['upload_log_num'].'#'.$empty_counter);
    } else { // 不为空看是否有变化，有变化计数器清零,没有变化计数器+1
        if ($serving_info->m_loginfo['total_log_num']!=$total_log_num) {
            SaveSysLog("[$module_name][$sub_module_name][branch_C!]",4);
            $empty_counter='0';
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serving_info->m_loginfo['total_log_num'].'#'.$serving_info->m_loginfo['upload_log_num'].'#'.$empty_counter);
        } else {
            $empty_counter<__MAX_COUNTER && $empty_counter++; // 本次空，计数器加1 
            mdb_set(__MDB_TAB_SERVER, __MDB_COL_EVENT, $row_key, $serving_info->m_loginfo['total_log_num'].'#'.$serving_info->m_loginfo['upload_log_num'].'#'.$empty_counter);
        }
    }
    /* }}} */
}
?>
