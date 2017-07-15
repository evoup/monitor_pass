<?php
/*
  +----------------------------------------------------------------------+
  | Name:testspeed_update.php
  +----------------------------------------------------------------------+
  | Comment:测速配置的模块
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2012年 9月 3日 星期一 10时10分26秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-09-18 13:28:46
  +----------------------------------------------------------------------+
 */
$module_name = 'testspeed_update';
SaveSysLog("[$module_name][debug_level:$debug_level]",4);
// 查询所有enable的站点，下发配置文件
try {
    $result = $GLOBALS['mdb_client']->scannerOpen( __MDB_TAB_TESTSPEED, '', array('info:enable') );
    while (true) {
        $record = $GLOBALS['mdb_client']->scannerGet($result);
        if ($record == NULL) {
            break;
        }
        $site=$record[0]->row;
        $column=$record[0]->columns;
        $enable=$column['info:enable']->value;
        if ( $enable ) {
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED,$site,'info:url');
            $url=$arr[0]->value;
            if (! empty($site) && !empty($url) ) {
                $allSite[$site]=$url;
            }
        }
    }
} catch ( Exception $e ) {
    SaveSysLog("[$module_name][err:".$e->getMessage()."]",2);
    return;
}
foreach ( $allSite as $site=>$url ) {
    $url=base64_encode( $url );
    $siteUrl="{$site}|$url";
    if ( !in_array($siteUrl, $outStr) ) {
        $outStr[]=$siteUrl;
    }
}
echo join( '#', (array)$outStr ); // pcc的序列化可函数能有问题，少用 
?>
