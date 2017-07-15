<?php
/*
  +----------------------------------------------------------------------+
  | Name:speed_count.php
  +----------------------------------------------------------------------+
  | Comment:测速统计模块
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 8月29日 星期三 20时01分42秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-11 13:05:52
  +----------------------------------------------------------------------+
 */
$module_name='speed count';
// 获取全部测速站点名
try {
    $result = $GLOBALS['mdb_client']->scannerOpen(__MDB_TAB_TESTSPEED, '', array('info:'));
    while (true) {
        $record = $GLOBALS['mdb_client']->scannerGet($result);
        if ($record == NULL) {
            break;
        }
        $recordArray = array();
        foreach ( $record as $TRowResult ) {
            $row = $TRowResult->row;
            $column=$TRowResult->columns;
            $deleted=$column['info:delete']->value;
            if ( $deleted ) {
                $GLOBALS['mdb_client']->deleteAll(__MDB_TAB_TESTSPEED, $row,'info');
                continue;
            }
            SaveSysLog("[$module_name][row:$row]",4); 
            $allSite[$row]=1;
        }
    }
} catch ( Exception $e ) {
    SaveSysLog("[$module_name][get all site error:".$e->getMessage()."]",4); 
    return;
}
$dateTs=strtotime(date('Y-m-d'));
SaveSysLog("[$module_name][dateTs:{$dateTs}]",4);
foreach ( array_keys($allSite) as $site ) {
    SaveSysLog("[$module_name][process site][site:$site]",4);
    // 遍历各站点，先找出平均速度
    $column = "info:testspeed_url-{$site}-speed{$dateTs}";
    SaveSysLog("[$module_name][column:{$column}]",4);
    try {
        $result = $GLOBALS['mdb_client']->scannerOpen( __MDB_TAB_SERVER_HISTORY, '', array($column) );
        while (true) {
            $record = $GLOBALS['mdb_client']->scannerGet( $result );
            if ($record == NULL) {
                break;
            }
            $recordArray = array();
            foreach ( $record as $TRowResult ) {
                $testNode = $TRowResult->row;
                $columns=$TRowResult->columns;
                $speed=$columns[$column]->value+0;
                SaveSysLog("[$module_name][testNode:$testNode][averageSpeed:$speed]",4);
                if ( !empty($speed) )
                    $siteSpeed[$site][$testNode]['lspeed'] = $speed; // 以平均速度作为最慢速度 
            }
        }
    } catch ( Exception $e ) {
        SaveSysLog("[$module_name][get site:{$site} error:".$e->getMessage()."]",3); 
        return;
    }
    // 再找出最高速度
    $column = "info:testspeed_url-{$site}-hspeed{$dateTs}";
    SaveSysLog("[$module_name][column:{$column}]",4);
    try {
        $result = $GLOBALS['mdb_client']->scannerOpen( __MDB_TAB_SERVER_HISTORY, '', array($column) );
        while (true) {
            $record = $GLOBALS['mdb_client']->scannerGet( $result );
            if ($record == NULL) {
                break;
            }
            $recordArray = array();
            foreach ( $record as $TRowResult ) {
                $testNode = $TRowResult->row;
                $columns=$TRowResult->columns;
                $hspeed=$columns[$column]->value+0;
                SaveSysLog("[$module_name][testNode:$testNode][highestSpeed:$hspeed]",4);
                if ( !empty($hspeed) )
                    $siteSpeed[$site][$testNode]['hspeed'] = $hspeed;
            }
        }
    } catch ( Exception $e ) {
        SaveSysLog("[$module_name][get site:{$site} error:".$e->getMessage()."]",3); 
        return;
    }
}

// 对各站点的平均速度和最高速度，在统筹一遍各监测点的平均速度
foreach ($siteSpeed as $site => $siteInfo) {
    $siteSpeedForAllNode=array();
    foreach ($siteInfo as $testNode => $testNodeInfo) {
        foreach ($testNodeInfo as $speedType => $speed) {
            SaveSysLog("[$module_name][site:$site][testNode:$testNode][speedType:$speedType][speed:$speed]",3); 
            $siteSpeedForAllNode[$speedType][]=$speed;
        }
    }
    $siteSpeedTotalAverage['lspeed']=array_sum($siteSpeedForAllNode['lspeed'])/count($siteSpeedForAllNode['lspeed']);
    $siteSpeedTotalAverage['lspeed']=sprintf('%.3f',$siteSpeedTotalAverage['lspeed']);
    $siteSpeedTotalAverage['hspeed']=array_sum($siteSpeedForAllNode['hspeed'])/count($siteSpeedForAllNode['hspeed']);
    $siteSpeedTotalAverage['hspeed']=sprintf('%.3f',$siteSpeedTotalAverage['hspeed']);
    SaveSysLog("[$module_name][site:$site][averageLowestSpeed:{$siteSpeedTotalAverage['lspeed']}]",4);
    SaveSysLog("[$module_name][site:$site][averageHighestSpeed:{$siteSpeedTotalAverage['hspeed']}]",4);
    $dateTs=strtotime(date('Y-m-d'));
    try {
        $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED,$site,'info:enable');
        $enable=$arr[0]->value;
        if ( $enable ) { //已经禁用的不要再存数据 
            mdb_set(__MDB_TAB_TESTSPEED, 'info:lspeed' . $dateTs, $site, $siteSpeedTotalAverage['lspeed']); // 保存平均最慢速度
            mdb_set(__MDB_TAB_TESTSPEED, 'info:hspeed' . $dateTs, $site, $siteSpeedTotalAverage['hspeed']); // 保存平均最快速度
        }
    } catch ( Exception $e ) {
        SaveSysLog("[$module_name][site:$site][get delete status & enable status err,cause:".$e->getMessage()."]",4);
        return;
    }
}
unset( $resultArray, $allSite, $siteSpeed, $siteSpeedForAllNode, $siteSpeedTotalAverage );

// 将当日的统计结果计算完成存到info:total字段
$date=date('Y-m-d',time());
$day=strtotime($date);

SaveSysLog("[$module_name][start count {$date} (dayTs:$day)...]",4);
try {
    $result = $GLOBALS['mdb_client']->scannerOpen(__MDB_TAB_TESTSPEED, '', array("info:lspeed{$day}"));
    while (true) {
        $record = $GLOBALS['mdb_client']->scannerGet($result);
        if ($record == NULL) {
            break;
        }
        foreach ( $record as $TRowResult ) {
            $site = $TRowResult->row;
            SaveSysLog("[$module_name][site:$site]",3); 
            $column=$TRowResult->columns;
            $allSiteSpeed[date('Y-m-d',$day)][$site]['lspeed']=floatval($column["info:lspeed{$day}"]->value);
        }
    }
    $result = $GLOBALS['mdb_client']->scannerOpen(__MDB_TAB_TESTSPEED, '', array("info:hspeed{$day}"));
    while (true) {
        $record = $GLOBALS['mdb_client']->scannerGet($result);
        if ($record == NULL) {
            break;
        }
        foreach ( $record as $TRowResult ) {
            $site = $TRowResult->row;
            SaveSysLog("[$module_name][site:$site]",3); 
            $column=$TRowResult->columns;
            $allSiteSpeed[date('Y-m-d',$day)][$site]['hspeed']=floatval($column["info:hspeed{$day}"]->value);
        }
    }
} catch ( Exception $e ) {
    SaveSysLog("[$module_name][get all site error:".$e->getMessage()."]",4); 
}
$total_json=json_encode($allSiteSpeed);
// store to database
$mutations = array(
    new Mutation( array(
        'column' => "info:count",
        'value'  => "{$total_json}" 
    ) )
);
try {
    $GLOBALS['mdb_client']->mutateRow( __MDB_TAB_TESTSPEED_HISTORY, $day, $mutations );
} catch ( Exception $e ) {
    SaveSysLog("[$module_name][save speed history to hbase error]",2);
}
try {
    if ( !is_object($GLOBALS['redis_client']) ) {
        $GLOBALS['redis_client'] = new Predis_Client($single_redis_server);
    }
    $GLOBALS['redis_client']->select(__TESTSPEED_TABLE);
    $GLOBALS['redis_client']->set($day,$total_json);
} catch ( Exception $e ) {
    SaveSysLog("[$module_name][save speed history to redis error]",2);
}
unset($mutations,$allSiteSpeed,$total_json);

?>
