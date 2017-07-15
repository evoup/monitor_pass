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
define(__THRIFT_ROOT,'../thrift');
define(__MDB_HOST,'127.0.0.1');
define(__MDB_PORT,'9090');
define(__MDB_SENDTIMEOUT, '10000');  //10 seconds
define(__MDB_RECVTIMEOUT, '10000');  //10 seconds
define(__TABLE_NAME,     'monitor_testversion'); //测试version的创建
chdir(dirname(__FILE__));
include_once(__THRIFT_ROOT.'/Thrift.php');
include_once(__THRIFT_ROOT.'/transport/TSocket.php');
include_once(__THRIFT_ROOT.'/transport/TBufferedTransport.php');
include_once(__THRIFT_ROOT.'/protocol/TBinaryProtocol.php');
include_once(__THRIFT_ROOT.'/packages/Hbase/Hbase.php');
include_once("../fun/fun.mdb.php");
openMdb(__MDB_HOST,__MDB_PORT,__MDB_SENDTIMEOUT,__MDB_RECVTIMEOUT);

$all_create_tables=array(__TABLE_NAME);
/*{{{ 删除表
 */
foreach($all_create_tables as $table) {
    try{
        if ($GLOBALS['mdb_client']->isTableEnabled($table)) {
            $GLOBALS['mdb_client']->disableTable($table); //需要先disableTable
        }
        $GLOBALS['mdb_client']->deleteTable($table);
    }catch(TException $tx) {
        print "table ".$table." delete failed!\n";
    }
    print "table ".$table." deleted\n";
}
/*}}}*/

/*{{{ 创建表
 */
$table_arr=array(
    array( //表1的family 
        array(
            'column_family_name' => "generic:",  
            'table_name'         => __TABLE_NAME,
        )
    )
);
foreach($table_arr as $table) {
    foreach($table as $column_fm){
        $columns[] = 
            new ColumnDescriptor(
                array(
                    'name' => $column_fm['column_family_name'], 
                    'maxVersions' => 288 ,
                )
            );
    }
    print_r($columns);
    try{
        $GLOBALS['mdb_client']->createTable($column_fm['table_name'], $columns);
    } catch (AlreadyExists $terr) {
        $Ret=false;
        print "table ".$column_fm['table_name']." already exist!\n";
        $GLOBALS['mdb_client']->disableTable($column_fm['table_name']);
        $GLOBALS['mdb_client']->deleteTable($column_fm['table_name']);
        exit("create db failed, please try again!");
    }
    print "table ".$column_fm['table_name']." created\n";
    unset($columns);
}
/* }}} */

/* {{{ 测试添加288个版本
 */
$mutations = array(
    new Mutation( array(
        'column' => "generic:summary_load", //列summary_load
        'value'  => "1.1" 
    ) ),
    new Mutation( array(
        'column' => "generic:summary_uptime_day", //列summary_uptime_day
        'value'  => "1" 
    ) )
);

$row_key = "20110815";
try{
    $ts=time()-time()%300;
    echo date('Y-m-d H:i:s', $ts);
    $GLOBALS['mdb_client']->mutateRowTs( __TABLE_NAME, $row_key, $mutations, $ts );
    //$GLOBALS['mdb_client']->mutateRow( __TABLE_NAME, $row_key, $mutations );
}
catch(Exception $e) { //抛出异常返回false
    $err = true;
    echo $e;
}
$mutations = array(
    new Mutation( array(
        'column' => "generic:summary_load", //列summary_load
        'value'  => "1.22" 
    ) ),
    new Mutation( array(
        'column' => "generic:summary_uptime_day", //列summary_uptime_day
        'value'  => "2" 
    ) )
);
try{
    $ts=time()-time()%300;
    echo date('Y-m-d H:i:s', $ts);
    $GLOBALS['mdb_client']->mutateRowTs( __TABLE_NAME, $row_key, $mutations, $ts );
    //$GLOBALS['mdb_client']->mutateRow( __TABLE_NAME, $row_key, $mutations );
}
catch(Exception $e) { //抛出异常返回false
    $err = true;
    echo $e;
}
/* }}} */
if(!$err) {
    echo "create successfully!";
}

/* {{{ 测试获取不同版本
 */
try{
    $res = $GLOBALS['mdb_client']->getVer(__TABLE_NAME, $row_key, 'generic:summary_load', 288);
} catch (Exception $e) {
    $err = true;
    echo $e;
}
//$res = (array)$res[0]; //得到二维数组下标为row和columns
print_r($res);

/* }}} */
?>
