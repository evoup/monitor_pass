#!/usr/local/php5_new/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | Name:shell_server.php
  +----------------------------------------------------------------------+
  | Comment:仿memcache协议的hbase表操作shell服务器
  +----------------------------------------------------------------------+
  | Others:简化hshell复杂操作，便于开发时调试
  +----------------------------------------------------------------------+
  | usage:set $TABLE=表名|set COLUMN=列名|tet rowkey名
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
error_reporting(0);
set_time_limit(0); //长连接 
define(__SERVER_IP, '127.0.0.1'); //服务器IP 
define(__PORT, $argv[1]); //端口 
define(__MDB_HOST, $argv[2]);
define(__MDB_PORT, $argv[3]);
define(__WELCOME, "welcome hbase telnet shell！\n>>");
define(__SHELL_ARROW, '>');
define(__THRIFT_ROOT, '../GPL/thrift');
define(__MDB_SENDTIMEOUT, '2000');  //2 seconds
define(__MDB_RECVTIMEOUT, '2000');  //2 seconds
chdir(dirname(__FILE__));
include_once(__THRIFT_ROOT.'/Thrift.php');
include_once(__THRIFT_ROOT.'/transport/TSocket.php');
include_once(__THRIFT_ROOT.'/transport/TBufferedTransport.php');
include_once(__THRIFT_ROOT.'/protocol/TBinaryProtocol.php');
include_once(__THRIFT_ROOT.'/packages/Hbase/Hbase.php');
/*{{{准备工作
 */
if(in_array(0,array_map('strlen', array($argv[0],$argv[1],$argv[2],$argv[3])))) {
    die("hbase telnet server: illegal option\n\tusuage: ./shell_server.php [server port] [thrift ip] [thrift port]");
}
daemonize(); // 作为daemon运行
// 设置IP和端口号  
$address = __SERVER_IP;  
$port    = __PORT;     // 调试的时候，可以多换端口来测试程序！  

// 创建一个SOCKET  
if (($listen_sock=socket_create(AF_INET,SOCK_STREAM,SOL_TCP))<0) {  
    echo "socket_create() failed due:".socket_strerror($listen_sock)."<br>";  
}  

// 绑定到socket端口  
if (($ret=socket_bind($listen_sock,$address,$port))<0) {  
    echo "socket_bind() failed due:".socket_strerror($ret)."<br>";  
}  

// 开始监听  
if (($ret=socket_listen($listen_sock,4))<0) {  
    echo "socket_listen() failed due:".socket_strerror($ret)."<br>";  
}  

while(true) {
    /*{{{主shell交互循环
     */
    if (($msgsock = socket_accept($listen_sock)) < 0) {  
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";  
        break;  
    }  
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        // we are the parent
        //pcntl_wait($status); //Protect against Zombie children?? //TODO 
        socket_close($msgsock);
        continue;
    } else {
        // we are the child
        // 产生的子进程
        $SayedWelcome = false;
        openMdb(__MDB_HOST,__MDB_PORT,__MDB_SENDTIMEOUT,__MDB_RECVTIMEOUT);
        do {  
            if (!$msgsock || !is_resource($msgsock)) exit(); // 客户端断开连接或者连接失败退出 

            $msg = $SayedWelcome? __SHELL_ARROW: __WELCOME;

            $SayedWelcome = true;

            // 发到客户端  
            $res = socket_write($msgsock, $msg, strlen($msg));  
            //file_put_contents('/tmp/1',socket_strerror(socket_last_error()));

            $buf = socket_read($msgsock, 8192); // 读取客户端的shell操作 

            $res = socket_write($msgsock, $msg, strlen($msg));  
            if (!$res) {
                break;
            }

            if (0===strncasecmp($buf,"SET",3)) { /* 设置表名 */ 
                if (0===strncasecmp($buf,'SET $TABLE=',11)) {
                    $table=substr($buf,11,strlen($buf)-11);
                    $msg = "use table:{$table}END\n";
                    @socket_write($msgsock, $msg, strlen($msg));  
                    $GLOBALS['table']=$table;
                    $SayedWelcome=true;
                    continue;
                } elseif (0===strncasecmp($buf,'SET $COLUMN=',12)) { /* 设置列名 */ 
                    $column_name=substr($buf,12,strlen($buf)-12);
                    if (!checkSetColumn($column_name)) {
                        $msg = "ERROR:COLUMN FORMAT MUSTBE COLUMN_FORMAT:COLUMN";
                        @socket_write($msgsock, $msg, strlen($msg));  
                        continue;
                    }
                    $msg = "select column:{$column_name}";
                    @socket_write($msgsock, $msg, strlen($msg));  
                }
            } elseif (0===strncasecmp($buf,"GET",3)) { /* 获取指定rowkey的列值 */ 
                $GLOBALS['rowkey'] = substr($buf,4,strlen($buf)-4);
                $table = str_replace("\r\n","",$table);
                $GLOBALS['rowkey'] = str_replace("\r\n", "", str_pad($GLOBALS['rowkey'],5,"0",STR_PAD_LEFT)); //去掉回车换行 
                $GLOBALS['column'] = str_replace("\r\n","",$column_name);
                try {
                    $scanner = $GLOBALS['mdb_client']->scannerOpenWithStop( $table, "{$GLOBALS['rowkey']}", "{$GLOBALS['rowkey']}",  array($GLOBALS['column']) );
                }
                catch (Exception $e) { //处理异常 
                    $msg="ERROR\n";
                    @socket_write($msgsock, $msg, strlen($msg));  
                    continue;
                }
                while (true) {
                    $record = $GLOBALS['mdb_client']->scannerGet($scanner);
                    if ($record == NULL) {
                        break;
                    }
                    $recordArray = array();
                    foreach($record as $TRowResult) {
                        $row = $TRowResult->row;
                        $column = $TRowResult->columns;
                        foreach($column as $family_column=>$cell) {
                            $recordArray[$family_column] = $cell;
                            $rs[]=$cell->value;
                        }
                    }
                }
                $msg=$rs[0]."\nEND\n"; //获取查询到的value 
                @socket_write($msgsock, $msg, strlen($msg));  
                unset($rs);
            } elseif (0===strncasecmp($buf,"QUIT",4) || 0===strncasecmp($buf,"EXIT",4) ||
                0===strncasecmp($buf,"BYE",3)) { //退出 
                    exit;
                } else { //协议错误 
                    $msg = "ERROR\n";
                    @socket_write($msgsock, $msg, strlen($msg));  
                }

            continue;
        } while (true);  
    socket_close($msgsock); //客户端退出了，关掉消息描述符 
    socket_close($listen_sock);
    closeMdb();
    exit(0);
    }
    /*}}}*/
}
/*}}}*/
socket_close($listen_sock);

/**
 *@brief 检查column是否输入正确
 *@param $str column字符串，column的格式  列族:列
 *@return 
 */
function checkSetColumn($str) {
    if (in_array(strpos($str,':'), array(false,0,strlen($str)))) {
        return false;
    }
    return true;
}

function daemonize() {
    if ($pid = pcntl_fork() > 0) {
        //parent process
        pcntl_wait($pid, $status); //avoid zombie process 
        exit(0);
    }
    posix_setsid();
    /*{{{处理信号，以防莫名退出*/
    pcntl_signal(SIGTERM, SIG_IGN);
    pcntl_signal(SIGHUP,  SIG_IGN);
    pcntl_signal(SIGUSR1, SIG_IGN);
    pcntl_signal(SIGPIPE, SIG_IGN);
    /*}}}*/
    if ($pid = pcntl_fork() > 0) {
        exit(0);
    }
    /*{{{切断任何联系*/
    chdir('/');
    umask(0);
    for ($i=0; $i<64; $i++) {
        fclose($i);
    }
    clearstatcache();
    /*}}}*/
}

/**
 *@brief 打开Mdb连接 
 *@return 
 */
function openMdb($host, $port, $sendtimeout, $recvtimeout) {
    global $module_name;
    try {
        $socket = new TSocket($host, $port);
        $socket->setSendTimeout($sendtimeout); // 2 seconds
        $socket->setRecvTimeout($recvtimeout); // 2 seconds
        $GLOBALS['mdb_transport'] = new TBufferedTransport($socket);
        $protocol = new TBinaryProtocol($GLOBALS['mdb_transport']);
        $GLOBALS['mdb_client'] = new HbaseClient($protocol);
        $GLOBALS['mdb_transport']->open();
    } catch (Exception $e) {
        SaveSysLog("[$module_name][open mdb error,check mdb server addr and whether mdb table integrity!]",2);
        doExit("open mdb");
    }
}

/**
 *@brief 关闭Mdb连接 
 *@return 
 */
function closeMdb() {
    if (isset($GLOBALS['mdb_transport'])) {
        $GLOBALS['mdb_transport']->close();
        unset($GLOBALS['mdb_client']);
        unset($GLOBALS['mdb_transport']);
    }
}
?>
