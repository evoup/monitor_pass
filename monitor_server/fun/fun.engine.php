<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.engine.php
  +----------------------------------------------------------------------+
  | Comment:监控服务端信息函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 8月 9日 星期四 16时17分00秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-10-11 16:46:01
  +----------------------------------------------------------------------+
 */

/**
 *@brief 保存服务端的procstart时间for前端界面调度
 */
function setEngineStatProcStart() {
    try {
        mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_PROCSTART, sprintf(__KEY_ENGINE_STAT,gethostname()),  time());
    } catch (Exception $e) {
        SaveSysLog('[setEngineStatProcStart][set usable][got err:'.$e->getMessage().']');
        doExit('setEngineStat procstart err');
    }
    SaveSysLog('[setEngineStatProcStart][set procstart to ok done.]');
}

/**
 *@brief 保存服务端的usable状态for前端界面调度
 */
function setEngineStatUsable() {
    // 设置服务端可用状态，默认为可用，除非在管理界面设置了禁用
    try {
        $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_ENGINE, sprintf(__KEY_ENGINE_STAT,gethostname()), __MDB_COL_SCAN_USABLE);
        $status=$arr[0]->value;
        if (empty($status)) {
            mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_USABLE, sprintf(__KEY_ENGINE_STAT,gethostname()),  __USABLE_STAT_OK);
        }
    } catch (Exception $e) {
        SaveSysLog('[setEngineStatUsable][set usable][got err:'.$e->getMessage().']');
        doExit('setEngineStat usable err');
    }
    SaveSysLog('[setEngineStatUsable][set usable to ok done.]');
}

/**
 *@brief 保存服务端的master状态for前端界面调度
 */
function setEngineStatMaster() {
    try {
        // TODO 设置其他列为非master
        $result=$GLOBALS['mdb_client']->scannerOpen(__MDB_TAB_ENGINE, '', (array)'scan:master');
        while (true) {
            $record = $GLOBALS['mdb_client']->scannerGet($result);
            if ($record == NULL) {
                break;
            }
            $recordArray = array();
            foreach($record as $TRowResult) {
                $row = $TRowResult->row;
                list(,$serverNode)=explode('|',$row);
                SaveSysLog("[server node key:{$serverNode}]");
                if (!empty($serverNode)) {
                    mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_MASTER, sprintf(__KEY_ENGINE_STAT,$serverNode), 0);
                }
            }
        }
        mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_MASTER, sprintf(__KEY_ENGINE_STAT,gethostname()), __MASTER_STAT);
        mdb_set(__MDB_TAB_ENGINE, __MDB_COL_SCAN_PID, sprintf(__KEY_ENGINE_STAT,gethostname()), posix_getpid());
        // TODO 获取进程开始时间
    } catch (Exception $e) {
        SaveSysLog('[setEngineStatMaster][got err:'.$e->getMessage().']');
        doExit('setEngineStat master err');
    }
    SaveSysLog('[setEngineStatMaster][done]');
}
?>
