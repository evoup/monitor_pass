<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun/mdbFun.m    
  +----------------------------------------------------------------------+
  | Comment:mdb操作函数 
  +----------------------------------------------------------------------+
  | Author: Evoup evoex@126.com 
  +----------------------------------------------------------------------+
  | Created:2011-03-07 10:50:26    
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-31 13:49:53
  +----------------------------------------------------------------------+
 */

include_once(__THRIFT_ROOT.'/Thrift.php' );
include_once(__THRIFT_ROOT.'/transport/TSocket.php' );
include_once(__THRIFT_ROOT.'/transport/TBufferedTransport.php' );
include_once(__THRIFT_ROOT.'/protocol/TBinaryProtocol.php' );

# According to the thrift documentation, compiled PHP thrift libraries should
# reside under the THRIFT_ROOT/packages directory.  If these compiled libraries
# are not present in this directory, move them there from gen-php/.
include_once(__THRIFT_ROOT.'/packages/Hbase/Hbase.php' );

function openMdb() {
    $socket = new TSocket(__MDB_HOST, __MDB_PORT);
    $socket->setSendTimeout(__MDB_SENDTIMEOUT); // 2 seconds
    $socket->setRecvTimeout(__MDB_RECVTIMEOUT); // 2 seconds
    $GLOBALS['mdb_transport'] = new TBufferedTransport($socket);
    $protocol = new TBinaryProtocol($GLOBALS['mdb_transport']);
    $GLOBALS['mdb_client'] = new HbaseClient($protocol);

    $GLOBALS['mdb_transport']->open();
}

function closeMdb() {
    if (isset($GLOBALS['mdb_transport'])) {
        $GLOBALS['mdb_transport']->close();
    }
}

/**
 *@brief 设置mdb中指定表指定列的rowkey对应的value
 *@param $table 表名
 *@param $column_name 列名（格式列族:名字）
 *@param $rowkey 行键
 *@param $value 值
 */
function mdb_set($table, $column_name, $rowkey,$value) {
    $mutations=array(
        new Mutation( array(
            'column' => $column_name,
            'value'  => $value 
        ) )
    );

    try { //thrift出错直接抛出异常需要捕获 
        $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations );
        $ret = true;
    }
    catch (Exception $e) { //抛出异常返回false 
        return false;
    }
    return ($ret);
}

?>
