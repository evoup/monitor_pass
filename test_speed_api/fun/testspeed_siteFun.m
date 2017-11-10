<?php
/*
  +----------------------------------------------------------------------+
  | Name:testspeed_siteFun.m
  +----------------------------------------------------------------------+
  | Comment:测速站点的函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create: 2012年 8月31日 星期五 19时47分08秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-31 20:10:29
  +----------------------------------------------------------------------+
 */
$module_name='testspeed_siteFun';
$GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; // 默认返回400 
header("Content-type: application/json; charset=utf-8");
$err=false;
switch ( $GLOBALS['selector'] ) {
case( __SELECTOR_SINGLE ):
    /*{{{添加监控站点*/
    $valid_key = Array('site','url', 'type');
    if ($GLOBALS['operation'] == __OPERATION_CREATE) { 
        if (isset($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') { // 要求上传数据非空和请求类型 
            // 检查必要的参数
            foreach ($valid_key as $needed) {
                if ( !in_array($needed, array_keys($_POST)) ) { // 对少传判断为非法
                    $err = true;
                }
            }
            if ( !$err ) // 站点名不能为空 
                $err=empty($_POST['site']) ? true : false;
            if ( !$err ) //  安全检查for后端CURL的重定向漏洞 
                $err=strstr($_POST['url'],'FILE://') || strstr($_POST['url'],'scp://') ? true : false;
            if ( !$err ) // 类型必须1链接或者2下载  
                $err=in_array($_POST['type'],array('1','2')) ? false : true;
            if ( !$err ) {
                DebugInfo("[$module_name][site:{$_POST['site']}][url:{$_POST['url']}][type:{$_POST['type']}]",2);
            }
            // 是否已存在
            if ( !$err && siteIsExist($_POST['site']) ) {
                $GLOBALS['httpStatus'] = __HTTPSTATUS_METHOD_CONFILICT;
                $err=true;
            }
            if ( !$err ) {
                // store to database
                $mutations = array(
                    new Mutation( array(
                        'column' => 'info:enable',
                        'value'  => '1' 
                    ) ),
                    new Mutation( array(
                        'column' => 'info:url',
                        'value'  => $_POST['url'] 
                    ) ),
                    new Mutation( array(
                        'column' => 'info:linktype',
                        'value'  => $_POST['type'] 
                    ) ),
                );
                try {
                    $GLOBALS['mdb_client']->mutateRow( __MDB_TAB_TESTSPEED, $_POST['site'], $mutations );
                } catch ( Exception $e ) {
                    DebugInfo("[$module_name][create site err:".$e->getMessage()."]",2);
                    $GLOBALS['httpStatus'] = __HTTPSTATUS_INTERNAL_SERVER_ERROR;
                    return;
                }
                $retArr=Array(
                    'site'   => "{$_POST['site']}",
                    'url'    => "{$_POST['url']}",
                    'type'   => "{$_POST['type']}",
                    'enable' => "1"
                );
                $GLOBALS['httpStatus'] = __HTTPSTATUS_CREATED;
                echo json_encode( $retArr );
            }
        }
    }
    /*}}}*/
    /*{{{删除站点*/
    elseif ( $GLOBALS['operation'] == __OPERATION_DELETE ) {
        if ( !empty($_POST['site']) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if ( siteIsExist($_POST['site']) ) {
                DebugInfo("[$module_name][delete site:{$_POST['site']} successfullly.]",2);
                if ( mdb_set(__MDB_TAB_TESTSPEED,'info:delete',$_POST['site'],'1') ) {
                    $GLOBALS['httpStatus'] = __HTTPSTATUS_OK;
                } else {
                    $GLOBALS['httpStatus'] = __HTTPSTATUS_INTERNAL_SERVER_ERROR;
                }
                return;
            } else {
                DebugInfo("[$module_name][site not existed]",2);
                $GLOBALS['httpStatus']=__HTTPSTATUS_FORBIDDEN;
                return;
            }
        }
    }
    /*}}}*/
    /*{{{修改站点*/
    elseif ( $GLOBALS['operation'] == __OPERATION_UPDATE ) {
        if ( siteIsExist($_POST['site']) ) {
            $valid_key=Array('site','url','type');
            // 检查必要的参数
            foreach ($valid_key as $needed) {
                if ( !in_array($needed, array_keys($_POST)) ) { // 对少传判断为非法
                    $err = true;
                }
            }
            if ( !$err ) // 站点名不能为空
                $err=empty($_POST['site']) ? true : false;
            if ( !$err ) // 安全检查for后端CURL的重定向漏洞 
                $err=strstr($_POST['url'],'FILE://') || strstr($_POST['url'],'scp://') ? true : false;
            if ( !$err ) // 类型必须1链接或者2下载  
                $err=in_array($_POST['type'],array('1','2')) ? false : true;
            if ( !$err && isset($_POST['enable'])) {
                $err=in_array($_POST['enable'],array('0','1')) ? false : true;
            }
            if ( !$err ) {
                @DebugInfo("[$module_name][site:{$_POST['site']}][url:{$_POST['url']}][type:{$_POST['type']}][enable:{$_POST['enable']}]",2);
            }
            if ( !$err ) {
                // store to database
                $mutations = array(
                    new Mutation( array(
                        'column' => 'info:url',
                        'value'  => $_POST['url'] 
                    ) ),
                    new Mutation( array(
                        'column' => 'info:linktype',
                        'value'  => $_POST['type'] 
                    ) )
                );
                if ( isset($_POST['enable']) ) {
                    $mutations[]=new Mutation(
                        array(
                            'column' => 'info:enable',
                            'value'  => $_POST['enable']
                        )
                    );
                }
                if ( !$err ) {
                    try {
                        $GLOBALS['mdb_client']->mutateRow( __MDB_TAB_TESTSPEED, $_POST['site'], $mutations );
                    } catch ( Exception $e ) {
                        DebugInfo("[$module_name][create site err:".$e->getMessage()."]",2);
                        $GLOBALS['httpStatus'] = __HTTPSTATUS_INTERNAL_SERVER_ERROR;
                        return;
                    }
                    $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED, $_POST['site'], 'info:enable');
                    $enable=$arr[0]->value;
                    $retArr=Array(
                        'site'   => "{$_POST['site']}",
                        'url'    => "{$_POST['url']}",
                        'type'   => "{$_POST['type']}",
                        'enable' => "$enable"
                    );
                    $GLOBALS['httpStatus']=__HTTPSTATUS_RESET_CONTENT;
                    DebugInfo("[$module_name][update site:{$GLOBALS['rowKey']} successfullly.]",2);
                    echo json_encode( $retArr );
                }
                return;
            }
        } else {
            DebugInfo("[$module_name][site not existed]",2);
            $GLOBALS['httpStatus']=__HTTPSTATUS_FORBIDDEN;
            return;
        }
    }
    /*}}}*/
    /*{{{获取单个站点信息*/
    elseif ( $GLOBALS['operation'] == __OPERATION_READ ) {
        $err = empty($_POST['site']) ? true : false;
        DebugInfo("[$module_name][get site:{$_POST['site']}]",2);
        if ( !$err ) {
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED, $_POST['site'], 'info:url');
            $url=$arr[0]->value;
            if ( empty($url) || !siteIsExist($_POST['site']) ) {
                $GLOBALS['httpStatus']=__HTTPSTATUS_NO_CONTENT;
                return;
            }
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED, $_POST['site'], 'info:linktype');
            $linktype=$arr[0]->value;
            $arr=$GLOBALS['mdb_client']->get(__MDB_TAB_TESTSPEED, $_POST['site'], 'info:enable');
            $enable=$arr[0]->value;
            $retArr=Array(
                "{$_POST['site']}"=>Array(
                    'url'    => "{$url}", 
                    'type'   => "{$linktype}",
                    'enable' => "$enable"
                )
            );
            $GLOBALS['httpStatus']=__HTTPSTATUS_OK;
            echo json_encode($retArr);
        }
        return;
    }
    /*}}}*/
    break;
case( __SELECTOR_MASS ):
    /*{{{获取全部站点信息*/
    if ( $GLOBALS['operation'] == __OPERATION_READ ) {
        try {
            $result = $GLOBALS['mdb_client']->scannerOpen( __MDB_TAB_TESTSPEED, '', array("info:") );
            while (true) {
                $record = $GLOBALS['mdb_client']->scannerGet($result);
                if ($record == NULL) {
                    break;
                }
                foreach ($record as $siteInfo) {
                    $siteName=$siteInfo->row;
                    $column=$siteInfo->columns;
                    $deleted=$column['info:delete']->value;
                    if ( !$deleted ) { // 标记为删除的就不要再显示了 
                        $allSites[$siteName]['url']=$column['info:url']->value;
                        $allSites[$siteName]['type']=$column['info:linktype']->value;
                        $allSites[$siteName]['enable']=$column['info:enable']->value;
                    }
                }
            }
        } catch ( Exception $e ) {
            DebugInfo("[$module_name][err:".$e->getMessage()."]",2);
            return;
        }
        $GLOBALS['httpStatus']=__HTTPSTATUS_OK;
        echo json_encode( $allSites );
    }
    /*}}}*/
default:
    break;
}

/**
 *@brief 站点是否存在
 *@param $sitename 站点名
 *@return true存在 false不存在
 */
function siteIsExist($sitename) {
    global $module_name;
    $hasRecord = false;
    try {
        $result = $GLOBALS['mdb_client']->scannerOpen( __MDB_TAB_TESTSPEED, $sitename, array("info:") );
        while (true) {
            $record = $GLOBALS['mdb_client']->scannerGet($result);
            if ($record == NULL) {
                break;
            }
            if ( $record[0]->row === $sitename ) {
                $arr = $GLOBALS['mdb_client']->get( __MDB_TAB_TESTSPEED, $sitename, 'info:delete');
                $deleted = $arr[0]->value;
                if ( !$deleted ) { // 没有删除标记的认为存在(删除操作会在服务端统计的时候再删掉) 
                    $hasRecord = true;
                    DebugInfo("[$module_name][hasRecord:$hasRecord]",2);
                    break;
                }
            }
        }
    } catch ( Exception $e ) {
        DebugInfo("[$module_name][err:".$e->getMessage()."]",2);
        return;
    }
    if ($hasRecord) {
        return true;
    }
}
?>
