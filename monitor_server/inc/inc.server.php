<?php
/*
  +----------------------------------------------------------------------+
  | Name:inc.server.php
  +----------------------------------------------------------------------+
  | Comment:定义常量
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-08 16:33:15
  +----------------------------------------------------------------------+
 */

/* 版本号 */
define(__VERSION,            '20121108.r1389'); // 小版本号,subversion的版本号

/* 配置 */
define(__ADDON_ROOT,         '/services/monitor_server'); // 主要存放一些配置的路径 
define(__CONF_SUBPATH,       'conf'); // 配置文件的子路径 
define(__CNAME,              'c'); // 控制器的名字 
define(__CONF_NAME,          'monitor_server'); // 配置文件的名字 
define(__CONF_MEMCACHE_PORT, 11211); // memcached端口 
define(__CONF_FILE,          __ADDON_ROOT.'/'.__CONF_SUBPATH.'/monitor_server.ini'); // 配置文件  
define(__CONF_FILE2,         __ADDON_ROOT.'/'.__CONF_SUBPATH.'/local.ini'); // 配置文件  

/* tag */
define(__SOURCE_SPLIT_TAG1,  ':');
define(__SOURCE_SPLIT_TAG2,  '#');
define(__SOURCE_SPLIT_TAG3,  '|');
define(__SOURCE_SPLIT_TAG4,  ','); 

/* 监控消息标记 */
define(__MONITOR_TYPES_NUM,        7 ); // 总计监控种类数 
define(__MONITOR_TYPE_GENERIC,     1 ); // 监控服务器通用类信息 
define(__MONITOR_TYPE_MYSQL,       2 ); // 监控mysql数据库类信息
define(__MONITOR_TYPE_SERVING,     3 ); // 监控SERVING类信息
define(__MONITOR_TYPE_DELIVERING,  3 ); // 监控投放类信息 
define(__MONITOR_TYPE_DAEMON,      4 ); // 守护进程类信息 
define(__MONITOR_TYPE_REPORT,      5 ); // 监控报表类信息 
define(__MONITOR_TYPE_MADN,        6 ); // 监控MADN类信息
define(__MONITOR_TYPE_HADOOP,      7 ); // 监控HADOOP类信息
define(__MONITOR_TYPE_CUST,        3 ); // 自定义组的监控标记
define(__MONITOR_TYPE_SECURITY,   11 ); // 安全的监控标记

/* 各监控信息数组大小和下标 */
// generic监控类型
define(__SERVER_FIELDS_NUM,              9 ); // server的各大指标总数
define(__SERVER_FIELD_SUMMARY,           0 ); // server信息数组的概要下标 
define(__SERVER_FIELD_CPU,               1 ); // server信息数组的CPU下标 
define(__SERVER_FIELD_CPU_NUM,           5 ); // server信息数组的CPU数组元素个数
define(__SERVER_FIELD_MEM,               2 ); // server信息数组的内存下标 
define(__SERVER_FIELD_MEM_NUM,           6 ); // server信息数组的MEM数组元素个数
define(__SERVER_FIELD_SWAP,              3 ); // server信息数组的SWAP下标
define(__SERVER_FIELD_SWAP_NUM,          4 ); // server信息数组的SWAP数组元素个数
define(__SERVER_FIELD_DISK,              4 ); // server信息数组的磁盘下标 
define(__SERVER_FIELD_PROCESS,           5 ); // server信息数组的进程下标 
define(__SERVER_FIELD_NETWORK,           6 ); // server信息数组的网卡下标 
define(__SERVER_FIELD_LINK,              7 ); // server信息数组的LINK下标 
define(__SERVER_FIELD_SERVICE,           8 ); // server信息数组的服务下标 
define(__SERVER_SUMMARY_ITEMS_NUM,       4 ); // 概要数组大小 
define(__SERVER_CPU_ITEMS_NUM,           5 ); // CPU数组大小 
define(__SERVER_MEM_ITEMS_NUM,           6 ); // 内存数组大小 
define(__SERVER_DISK_ITEMS_NUM,          3 ); // 磁盘输出大小 
define(__SERVER_SWAP_ITEMS_NUM,          4 ); // SWAP数组大小 
define(__SERVER_PARTITION_ITEMS_NUM,     3 ); // 分区数组大小 
define(__SERVER_PARTITION_ITEM_MOUNTED,  0 ); // 分区数组mounted下标 
define(__SERVER_PARTITION_ITEM_CAPACITY, 1 ); // 分区数组capacity下标 
define(__SERVER_PARTITION_ITEM_IUSED,    2 ); // 分区数组iused下标 
define(__SERVER_PROCESS_ITEMS_NUM,       8 ); // 进程数组大小 
define(__SERVER_NETWORK_ITEMS_NUM,       3 ); // 网络数组大小 
define(__SERVER_NETWORK_ITEM_IFNAME,     0 ); // 网络数组ifname下标 
define(__SERVER_NETWORK_ITEM_IN,         1 ); // 网络数组in下标 
define(__SERVER_NETWORK_ITEM_OUT,        2 ); // 网络数组out下标 
define(__SERVER_LINK_ITEMS_NUM,          3 ); // Link数组大小 
define(__SERVER_LINK_ITEM_SSERVER,       0 ); // Link数组sserver下标 
define(__SERVER_LINK_ITEM_DSERVER,       1 ); // Link数组dserver下标 
define(__SERVER_LINK_ITEM_FLOW,          2 ); // Link数组flow下标 
define(__SERVER_SERVICE_ITEMS_NUM,       3 ); // 服务数组大小 
define(__SERVER_SERVICE_ITEM_NAME,       0 ); // 服务数组name下标 
define(__SERVER_SERVICE_ITEM_PORT,       1 ); // 服务数组port下标 
define(__SERVER_SERVICE_ITEM_STATUS,     2 ); // 服务数组status下标 
define(__SERVER_SUMMARY_FIELDS_NUM,      4 ); // server类型summary字段总数
// mysql监控类型
define(__MYSQL_FIELD_SUMMARY,               0 ); // mysql信息数组的概要下标 
define(__MYSQL_SUMMARY_FIELDS_NUM,          6 ); // mysql信息数组summary下字段总数
define(__MYSQL_FIELD_TRAFFIC,               1 ); // mysql信息数组traffic下标
define(__MYSQL_FIELD_STATEMENT,             2 ); // mysql信息数组statement下标
define(__MYSQL_FIELD_REPLICATION,           3 ); // mysql信息数组replication下标
define(__MYSQL_FIELD_DBINFO,                4 ); // mysql信息数组dbinfo下标
define(__MYSQL_FIELD_TABLEINFO,             5 ); // mysql信息数组tableinfo下标
define(__MYSQL_FIELD_MASTERSLAVE,           6 ); // mysql信息数组masterslave下标
define(__MYSQL_FIELD_SLAVE_IO_RUNNING,      7 ); // mysql信息数组Slave_IO_Running下标
define(__MYSQL_FIELD_SLAVE_SQL_RUNNING,     8 ); // mysql信息数组Slave_SQL_Running下标
define(__MYSQL_FIELD_SECONDS_BEHIND_MASTER, 9 ); // mysql信息数组Seconds_Behind_Master下标
// serving监控类型
define(__SERVING_FIELDS_NUM,          5 ); // serving的各大指标总数
define(__SERVING_FIELD_SUMMARY,       0 ); // serving信息数组的概要下标 
define(__SERVING_FILED_ADIMAGE,       1 ); // serving信息数组的adimage下标 
define(__SERVING_FILED_ADIMAGE_NUM,   6 ); // serving信息数组的adimage数组元素个数 
define(__SERVING_FILED_LOGINFO,       2 ); // serving信息数组的loginfo下标 
define(__SERVING_FILED_LOGINFO_NUM,   4 ); // serving信息数组的loginfo数组元素个数 
define(__SERVING_FILED_TRAFFIC,       3 ); // serving信息数组的traffic下标 
define(__SERVING_FILED_ENGINESTAT,    4 ); // serving信息数组的enginestatus下标 
define(__SERVING_DOMAININFO_ITEMS_NUM,6 ); // domaininfo数组大小 
// daemon监控类型
define(__DAEMON_FIELDS_NUM,         5 ); // daemon的各大指标总数
define(__DAEMON_FIELD_WEBSRV_STAT,  0 ); // daemon信息数组的web服务器状态下标 
define(__DAEMON_FIELD_DAEMON_STAT,  1 ); // daemon信息数组的守护进程状态下标 
define(__DAEMON_FIELD_LOGIN_STAT,   2 ); // daemon信息数组的LOGIN状态下标 
define(__DAEMON_FIELD_ADSERV_STAT,  3 ); // daemon信息数组的ADSERV状态下标 
define(__DAEMON_FIELD_ERRLOG_STAT,  4 ); // daemon信息数组的ERROR LOG状态下标 
// report监控类型
define(__REPORT_FIELDS_NUM,              3 ); // report的各大指标总数 
define(__REPORT_FIELD_LOG_PROCESS_STAT,  0 ); // report信息数组的日志处理情况下标 
define(__REPORT_FIELD_PROCESS_SPEED,     1 ); // report信息数组的处理速度下标
define(__REPORT_FIELD_WAIT_PROCESS_LOG_NUM, 2); // report信息数组的待处理log数下标
define(__MADN_FIELD_AVAILABILITY,        0); // madn信息数组的URL可用性下标
define(__MADN_FIELD_TESTSPEED,           1); // madn信息数组的测速下标
// security监控类型
define(__SECURITY_FIELDS_NUM, 7); // security的各大指标总数
define(__SECURITY_FIELDS_SHELLMD5, 0); // security信息数组的常用shell的md5状态下标
define(__SECURITY_FIELDS_SSH,      1); // security信息数组的ssh登录状态下标
define(__SECURITY_FIELDS_SOFTVER,  2); // security信息数组的software version下标
define(__SECURITY_FIELDS_USER,     3); // security信息数组的user下标
define(__SECURITY_FIELDS_ROOTPROC, 4); // security信息数组的以root用户运行的进程下标
define(__SECURITY_FIELDS_SYN,      5); // security信息数组的SYN半连接数下标
define(__SECURITY_FIELDS_SNIFFER,  6); // security信息数组的嗅探状态下标

/* 组类型 */
define(__GROUP_TYPE_DEFAULT,   0); // 默认组
define(__GROUP_TYPE_CUSTOMIZE, 1); // 自定义组 


/* 事件代码 */
//watchdog (TODO 能否集成在客户端)
define(__EVCODE996W,   '996w'); // 通过远端的CGI脚本来请求本服务器，证明客户端无法上传
//generic
define(__EVCODE000N,   '0n'); // 磁盘可用空间正常
define(__EVCODE000C,   '0c'); // 磁盘可用空间黄色警报
define(__EVCODE000W,   '0w'); // 磁盘可用空间红色警报
define(__EVCODE001N,   '1n'); // inode可用空间正常
define(__EVCODE001C,   '1c'); // inode可用空间黄色警报
define(__EVCODE001W,   '1w'); // inode可用空间红色警报
define(__EVCODE002N,   '2n'); // load average正常
define(__EVCODE002C,   '2c'); // load average黄色警报
define(__EVCODE002W,   '2w'); // load average红色警报
define(__EVCODE003N,   '3n'); // 内存使用率正常
define(__EVCODE003C,   '3c'); // 内存使用率黄色警报
define(__EVCODE003W,   '3w'); // 内存使用率红色警报(unused)
define(__EVCODE004N,   '4n'); // 系统中运行进程数量正常
define(__EVCODE004C,   '4c'); // 系统中运行进程数量黄色警报
define(__EVCODE004W,   '4w'); // 系统中运行进程数量红色警报
define(__EVCODE005N,   '5n'); // Cpu占用率正常(unused)
define(__EVCODE005C,   '5c'); // Cpu占用率黄色警报(unused)
define(__EVCODE005W,   '5w'); // Cpu占用率红色警报(unused)
define(__EVCODE006N,   '6n'); // TCP/IP端口监控正常
define(__EVCODE006C,   '6c'); // TCP/IP端口监控黄色警报
define(__EVCODE006W,   '6w'); // TCP/IP端口监控红色警报(unused)
define(__EVCODE007N,   '7n'); // TCP/IP连接数正常
define(__EVCODE007C,   '7c'); // TCP/IP连接数黄色警报
define(__EVCODE007W,   '7w'); // TCP/IP连接数红色警报
define(__EVCODE008N,   '8n'); // 网卡流量正常
define(__EVCODE008C,   '8c'); // 网卡流量黄色警报
define(__EVCODE008W,   '8w'); // 网卡流量红色警报(unused)
define(__EVCODE997W, '997w'); // 单个监控点down机或无法收到监上传信息
define(__EVCODE998W, '998w'); // 监控组down机或无法收到监控点上传信息
define(__EVCODE999W, '999w'); // 全部监控点down机或无法收到监上传信息
//serving
define(__EVCODE009N,   '9n'); // serving单台负荷正常
define(__EVCODE009C,   '9c'); // serving单台负荷黄色警报
define(__EVCODE009W,   '9w'); // serving单台负荷红色警报(unused)
define(__EVCODE010N,  '10n'); // serving工作节点数量正常(unused)
define(__EVCODE010C,  '10c'); // serving工作节点数量黄色警报(unused)
define(__EVCODE010W,  '10w'); // serving工作节点数量红色警报(unused)
define(__EVCODE011N,  '11n'); // serving广告发布正常
define(__EVCODE011C,  '11c'); // serving广告发布黄色警报 
define(__EVCODE011W,  '11w'); // serving广告发布红色警报
define(__EVCODE023N,  '23n'); // serving日志生成正常
define(__EVCODE023C,  '23c'); // serving日志生成黄色警报
define(__EVCODE023W,  '23w'); // serving日志生成红色警报
define(__EVCODE024N,  '24n'); // serving单台填充率正常
define(__EVCODE024C,  '24c'); // serving单台填充率黄色报警
define(__EVCODE024W,  '24w'); // serving单台填充率红色报警
define(__EVCODE025C,  '25c'); // madn可用性黄色报警
define(__EVCODE025W,  '25w'); // madn可用性红色报警
//daemon
define(__EVCODE012N,  '12n'); // daemon web服务器正常 
define(__EVCODE012C,  '12c'); // daemon web服务器黄色警报(unused)
define(__EVCODE012W,  '12w'); // daemon web服务器红色警报 
define(__EVCODE013N,  '13n'); // daemon 后台daemon正常 
define(__EVCODE013C,  '13c'); // daemon 后台daemon黄色警报(unused) 
define(__EVCODE013W,  '13w'); // daemon 后台daemon红色警报
define(__EVCODE014N,  '14n'); // daemon login正常 
define(__EVCODE014C,  '14c'); // daemon login黄色警报(unused)
define(__EVCODE014W,  '14w'); // daemon login红色警报
define(__EVCODE015N,  '15n'); // daemon 广告投放正常 
define(__EVCODE015C,  '15c'); // daemon 广告投放黄色警报(unused)
define(__EVCODE015W,  '15w'); // daemon 广告投放红色警报(unused)
define(__EVCODE016N,  '16n'); // daemon error log正常 
define(__EVCODE016C,  '16c'); // daemon error log黄色警报 
define(__EVCODE016W,  '16w'); // daemon error log红色警报
//mysql
define(__EVCODE017N,  '17n'); // Mysql Database Server 数据库连接数量正常
define(__EVCODE017C,  '17c'); // Mysql Database Server 数据库连接数量黄色警报
define(__EVCODE017W,  '17w'); // Mysql Database Server 数据库连接数量红色警报
define(__EVCODE018N,  '18n'); // Mysql Database Server 单表最大尺寸正常
define(__EVCODE018C,  '18c'); // Mysql Database Server 单表最大尺寸黄色警报
define(__EVCODE018W,  '18w'); // Mysql Database Server 单表最大尺寸红色警报
define(__EVCODE019N,  '19n'); // Mysql Database Server threads线程数量正常
define(__EVCODE019C,  '19c'); // Mysql Database Server threads线程数量黄色警报
define(__EVCODE019W,  '19w'); // Mysql Database Server threads线程数量红色警报
define(__EVCODE020N,  '20n'); // Mysql Database Server Master/Slave状态正常
define(__EVCODE020C,  '20c'); // Mysql Database Server Master/Slave状态黄色警报
define(__EVCODE020W,  '20w'); // Mysql Database Server Master/Slave状态红色警报(unused)
define(__EVCODE021N,  '21n'); // Mysql Database Server 关键表控制正常 
define(__EVCODE021C,  '21c'); // Mysql Database Server 关键表控制黄色警报
define(__EVCODE021W,  '21w'); // Mysql Database Server 关键表控制红色警报(unused)
define(__EVCODE029N,  '29n'); // Mysql Database Server Seconds_Behind_Master状态正常
define(__EVCODE029C,  '29c'); // Mysql Database Server Seconds_Behind_Master状态黄色警报
define(__EVCODE029W,  '29w'); // Mysql Database Server Seconds_Behind_Master状态红色警报
//report
define(__EVCODE022N,  '22n'); // report 待处理log数量正常
define(__EVCODE022C,  '22c'); // report 待处理log数量黄色警报
define(__EVCODE022W,  '22w'); // report 待处理log数量红色警报(unused)
//hadoop
define(__EVCODE026C,  '26c'); // hadoop dfs.datanode.copyBlockOp_avg_time时间黄色警报
define(__EVCODE026W,  '26w'); // hadoop dfs.datanode.copyBlockOp_avg_time时间红色警报(unused)
define(__EVCODE027C,  '27c'); // hadoop dfs.datanode.heartBeats_avg_time时间黄色警报
define(__EVCODE027W,  '27w'); // hadoop dfs.datanode.heartBeats_avg_time时间红色警报(unused)
define(__EVCODE028C,  '28c'); // hadoop dfs.datanode.readBlockOp_avg_time时间黄色报警(unused)
define(__EVCODE028W,  '28w'); // hadoop dfs.datanode.readBlockOp_avg_time时间红色警报(unused)


//事件配置文件对照表
$_EventConfArr=array(
    str_pad(__EVCODE000C, 4, "0", STR_PAD_LEFT)=>'disk_range',
    str_pad(__EVCODE000W, 4, "0", STR_PAD_LEFT)=>'disk_range',
    str_pad(__EVCODE001C, 4, "0", STR_PAD_LEFT)=>'disk_inode',
    str_pad(__EVCODE001W, 4, "0", STR_PAD_LEFT)=>'disk_inode',
    str_pad(__EVCODE002C, 4, "0", STR_PAD_LEFT)=>'load_average',
    str_pad(__EVCODE002W, 4, "0", STR_PAD_LEFT)=>'load_average',
    str_pad(__EVCODE003C, 4, "0", STR_PAD_LEFT)=>'memory_usage_percent',
    str_pad(__EVCODE003W, 4, "0", STR_PAD_LEFT)=>'memory_usage_percent',
    str_pad(__EVCODE004C, 4, "0", STR_PAD_LEFT)=>'running_process_num',
    str_pad(__EVCODE004W, 4, "0", STR_PAD_LEFT)=>'running_process_num',
    str_pad(__EVCODE005C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE005W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE006C, 4, "0", STR_PAD_LEFT)=>'tcpip_service',
    str_pad(__EVCODE006W, 4, "0", STR_PAD_LEFT)=>'tcpip_service',
    str_pad(__EVCODE007C, 4, "0", STR_PAD_LEFT)=>'tcpip_connections',
    str_pad(__EVCODE007W, 4, "0", STR_PAD_LEFT)=>'tcpip_connections',
    str_pad(__EVCODE008C, 4, "0", STR_PAD_LEFT)=>'network_flow',
    str_pad(__EVCODE008W, 4, "0", STR_PAD_LEFT)=>'network_flow',
    str_pad(__EVCODE009C, 4, "0", STR_PAD_LEFT)=>'serving_request',
    str_pad(__EVCODE009W, 4, "0", STR_PAD_LEFT)=>'serving_request',
    str_pad(__EVCODE010C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE010W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE011C, 4, "0", STR_PAD_LEFT)=>'serving_deliver',
    str_pad(__EVCODE011W, 4, "0", STR_PAD_LEFT)=>'serving_deliver',
    str_pad(__EVCODE012C, 4, "0", STR_PAD_LEFT)=>'daemon_webserver',
    str_pad(__EVCODE012W, 4, "0", STR_PAD_LEFT)=>'daemon_webserver',
    str_pad(__EVCODE013C, 4, "0", STR_PAD_LEFT)=>'daemon_daemon',
    str_pad(__EVCODE013W, 4, "0", STR_PAD_LEFT)=>'daemon_daemon',
    str_pad(__EVCODE014C, 4, "0", STR_PAD_LEFT)=>'daemon_login',
    str_pad(__EVCODE014W, 4, "0", STR_PAD_LEFT)=>'daemon_login',
    str_pad(__EVCODE015C, 4, "0", STR_PAD_LEFT)=>'daemon_adserv',
    str_pad(__EVCODE015W, 4, "0", STR_PAD_LEFT)=>'daemon_adserv',
    str_pad(__EVCODE016C, 4, "0", STR_PAD_LEFT)=>'daemon_errorlog',
    str_pad(__EVCODE016W, 4, "0", STR_PAD_LEFT)=>'daemon_errorlog',
    str_pad(__EVCODE017C, 4, "0", STR_PAD_LEFT)=>'mysql_db_connections',
    str_pad(__EVCODE017W, 4, "0", STR_PAD_LEFT)=>'mysql_db_connections',
    str_pad(__EVCODE018C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE018W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE019C, 4, "0", STR_PAD_LEFT)=>'mysql_db_threads',
    str_pad(__EVCODE019W, 4, "0", STR_PAD_LEFT)=>'mysql_db_threads',
    str_pad(__EVCODE020C, 4, "0", STR_PAD_LEFT)=>'mysql_master_slave',
    str_pad(__EVCODE020W, 4, "0", STR_PAD_LEFT)=>'mysql_master_slave',
    str_pad(__EVCODE021C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE021W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE022C, 4, "0", STR_PAD_LEFT)=>'report_wait_process_log_num',
    str_pad(__EVCODE022W, 4, "0", STR_PAD_LEFT)=>'report_wait_process_log_num',
    str_pad(__EVCODE996W, 4, "0", STR_PAD_LEFT)=>'', // 待定
    str_pad(__EVCODE997W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE998W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE999W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE023C, 4, "0", STR_PAD_LEFT)=>'serving_loginfo',
    str_pad(__EVCODE023W, 4, "0", STR_PAD_LEFT)=>'serving_loginfo',
    str_pad(__EVCODE024C, 4, "0", STR_PAD_LEFT)=>'serving_fillrate',
    str_pad(__EVCODE024W, 4, "0", STR_PAD_LEFT)=>'serving_fillrate',
    str_pad(__EVCODE025C, 4, "0", STR_PAD_LEFT)=>'madn_availability',
    str_pad(__EVCODE025W, 4, "0", STR_PAD_LEFT)=>'madn_availability',
    str_pad(__EVCODE026C, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.copyBlockOp_avg_time',
    str_pad(__EVCODE026W, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.copyBlockOp_avg_time',
    str_pad(__EVCODE027C, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.heartBeats_avg_time',
    str_pad(__EVCODE027W, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.heartBeats_avg_time',
    str_pad(__EVCODE028C, 4, "0", STR_PAD_LEFT)=>'', // 待定 
    str_pad(__EVCODE028W, 4, "0", STR_PAD_LEFT)=>'', // 待定 
    str_pad(__EVCODE029C, 4, "0", STR_PAD_LEFT)=>'mysql_seconds_behind_master',
    str_pad(__EVCODE029W, 4, "0", STR_PAD_LEFT)=>'mysql_seconds_behind_master'
);

//事件描述对照表 // for 事件解决文字 
$_EventDescArr=array(
    str_pad(__EVCODE000C, 4, "0", STR_PAD_LEFT)=>'disk capacity',
    str_pad(__EVCODE000W, 4, "0", STR_PAD_LEFT)=>'disk capacity',
    str_pad(__EVCODE001C, 4, "0", STR_PAD_LEFT)=>'disk inode',
    str_pad(__EVCODE001W, 4, "0", STR_PAD_LEFT)=>'disk inode',
    str_pad(__EVCODE002C, 4, "0", STR_PAD_LEFT)=>'load average',
    str_pad(__EVCODE002W, 4, "0", STR_PAD_LEFT)=>'load average',
    str_pad(__EVCODE003C, 4, "0", STR_PAD_LEFT)=>'memory usage percent',
    str_pad(__EVCODE003W, 4, "0", STR_PAD_LEFT)=>'memory usage percent',
    str_pad(__EVCODE004C, 4, "0", STR_PAD_LEFT)=>'running process num',
    str_pad(__EVCODE004W, 4, "0", STR_PAD_LEFT)=>'running process num',
    str_pad(__EVCODE005C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE005W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE006C, 4, "0", STR_PAD_LEFT)=>'tcpip service',
    str_pad(__EVCODE006W, 4, "0", STR_PAD_LEFT)=>'tcpip service',
    str_pad(__EVCODE007C, 4, "0", STR_PAD_LEFT)=>'tcpip connections',
    str_pad(__EVCODE007W, 4, "0", STR_PAD_LEFT)=>'tcpip connections',
    str_pad(__EVCODE008C, 4, "0", STR_PAD_LEFT)=>'network flow',
    str_pad(__EVCODE008W, 4, "0", STR_PAD_LEFT)=>'network flow',
    str_pad(__EVCODE009C, 4, "0", STR_PAD_LEFT)=>'serving request',
    str_pad(__EVCODE009W, 4, "0", STR_PAD_LEFT)=>'serving request',
    str_pad(__EVCODE010C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE010W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE011C, 4, "0", STR_PAD_LEFT)=>'serving deliver', // engine status 
    str_pad(__EVCODE011W, 4, "0", STR_PAD_LEFT)=>'serving deliver',
    str_pad(__EVCODE012C, 4, "0", STR_PAD_LEFT)=>'daemon webserver status',
    str_pad(__EVCODE012W, 4, "0", STR_PAD_LEFT)=>'daemon webserver status',
    str_pad(__EVCODE013C, 4, "0", STR_PAD_LEFT)=>'daemon daemon status',
    str_pad(__EVCODE013W, 4, "0", STR_PAD_LEFT)=>'daemon daemon',
    str_pad(__EVCODE014C, 4, "0", STR_PAD_LEFT)=>'daemon login status',
    str_pad(__EVCODE014W, 4, "0", STR_PAD_LEFT)=>'daemon login status',
    str_pad(__EVCODE015C, 4, "0", STR_PAD_LEFT)=>'daemon adserv status',
    str_pad(__EVCODE015W, 4, "0", STR_PAD_LEFT)=>'daemon adserv status',
    str_pad(__EVCODE016C, 4, "0", STR_PAD_LEFT)=>'daemon errorlog status',
    str_pad(__EVCODE016W, 4, "0", STR_PAD_LEFT)=>'daemon errorlog status',
    str_pad(__EVCODE017C, 4, "0", STR_PAD_LEFT)=>'mysql db connections',
    str_pad(__EVCODE017W, 4, "0", STR_PAD_LEFT)=>'mysql db connections',
    str_pad(__EVCODE018C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE018W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE019C, 4, "0", STR_PAD_LEFT)=>'mysql db threads',
    str_pad(__EVCODE019W, 4, "0", STR_PAD_LEFT)=>'mysql db threads',
    str_pad(__EVCODE020C, 4, "0", STR_PAD_LEFT)=>'mysql replication',
    str_pad(__EVCODE020W, 4, "0", STR_PAD_LEFT)=>'mysql replication',
    str_pad(__EVCODE021C, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE021W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE022C, 4, "0", STR_PAD_LEFT)=>'report wait process log num',
    str_pad(__EVCODE022W, 4, "0", STR_PAD_LEFT)=>'report wait process log num',
    str_pad(__EVCODE996W, 4, "0", STR_PAD_LEFT)=>'', // 待定
    str_pad(__EVCODE997W, 4, "0", STR_PAD_LEFT)=>'host down',
    str_pad(__EVCODE998W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE999W, 4, "0", STR_PAD_LEFT)=>'',
    str_pad(__EVCODE023C, 4, "0", STR_PAD_LEFT)=>'serving loginfo',
    str_pad(__EVCODE023W, 4, "0", STR_PAD_LEFT)=>'serving loginfo',
    str_pad(__EVCODE024C, 4, "0", STR_PAD_LEFT)=>'serving fillrate',
    str_pad(__EVCODE024W, 4, "0", STR_PAD_LEFT)=>'serving fillrate',
    str_pad(__EVCODE025C, 4, "0", STR_PAD_LEFT)=>'madn availability',
    str_pad(__EVCODE025W, 4, "0", STR_PAD_LEFT)=>'madn availability',
    str_pad(__EVCODE026C, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.copyBlockOp_avg_time',
    str_pad(__EVCODE026W, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.copyBlockOp_avg_time',
    str_pad(__EVCODE027C, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.heartBeats_avg_time',
    str_pad(__EVCODE027W, 4, "0", STR_PAD_LEFT)=>'dfs.datanode.heartBeats_avg_time',
    str_pad(__EVCODE028C, 4, "0", STR_PAD_LEFT)=>'', // 待定 
    str_pad(__EVCODE028W, 4, "0", STR_PAD_LEFT)=>'', // 待定 
    str_pad(__EVCODE029C, 4, "0", STR_PAD_LEFT)=>'mysql seconds behind master',
    str_pad(__EVCODE029W, 4, "0", STR_PAD_LEFT)=>'mysql seconds behind master'
);


/* 全局服务器列表的key */
define(__KEY_ALLSRV, "all");  // column为servername:all,table为__MDB_TAB_SERVERNAME

/* 报警间隔的key */
//(存memcache的发送时间间隔的key,value为间隔多少时间发送警报)
define(__KEY_INTERVAL_DEFAULT_GP_ALLDOWN, "ta|default|999w"); // 各个默认组全down 
define(__KEY_INTERVAL_CUST_GP_ALLDOWN,    "ta|cust|999w");    // 各个自定义组down 
define(__KEY_INTERVAL_DEFAULT_GP_ONEDOWN, "tg|%s|998w");      // 单个默认组down
define(__KEY_INTERVAL_CUST_GP_ONEDOWN,    "tg|%s|998w");      // 单个自定义组down
define(__KEY_INTERVAL_SERVER_EVENT,       "t|%s|%s");         // 单台服务器单个事件
define(__KEY_SOLVED,                      "slv|%s|%s");       // 发送恢复邮件Flag
define(__KEY_KEEPWATCH,                   "kpwc|%s|%s");      // 观望事件的key 

/* 上次获取上传的时间戳的key(new) */
define(__KEY_LASTTIME,   "lt|%s");   // 供客户端上传监控信息以判断其在线,存时间戳

/* 服务器存活列表的key*/
define(__KEY_ALIVESRV,   "servtype%s"); // 默认组为servtype1~servtype7，自定义组为servtype自定义名字

/* 客户端监控消息的key */
define(__KEY_CLIENT_MSG, "%s|%s");      // |左面为1~5或者自定义组，右面为服务器名

/* 客户端各类检查需要存一定时间来判断的key */
define(__KEY_SERVING_LOGINFO, __MONITOR_TYPE_SERVING."|%s|loginfos"); // 存创建日志的状态信息

/* 待处理列表的key */
define(__KEY_NEEDFIX,    "needfix");    // 查询以得到全部有问题的事件代码 

/* 服务器事件的key */
define(__KEY_NF,         "nf%s");       // 查询以得到发生这个服务器事件的服务器是哪个（些）

/* 第一次生成配置文件默认项用到的数组 */
$array_conf=Array( // 生成构造配置文件的一个数组 
    'version'             => __VERSION,
    'mcd_port'            => __CONF_MEMCACHE_PORT,
    'memcache_persistant' => __CONF_MEMCACHE_PERSISTANT,
);

define(__RUN_SUBPATH, 'run'); // pid文件夹
define(__PROCESS_NAME,'monitorSrv');
define(__PROCESS2_NAME,'zookeeperClient');

define(__THRIFT_ROOT, 'GPL/thrift'); // thrift路径 

// hbase表
define(__MDB_TAB_SERVER,         'monitor_server'); // 服务器即时信息表
define(__MDB_TAB_SERVER_HISTORY, 'monitor_server_history'); // 服务器历史信息表
define(__MDB_TAB_SERVERNAME,     'monitor_servername'); // 服务器名表  TODO 该表和即时表整合 
define(__MDB_TAB_HOST,           'monitor_host'); // 主机表 TODO 该表和即时表整合 
define(__MDB_TAB_ENGINE,         'monitor_engine'); // 监控引擎表 
define(__MDB_TAB_USER,           'monitor_user'); // 用户表 
define(__MDB_TAB_TESTSPEED,      'monitor_testspeed'); // 统计测速表
define(__MDB_TAB_TESTSPEED_HISTORY, 'monitor_testspeed_history'); // 统计测速历史表(用来快速出报表)

// hbase列
define(__MDB_COL_EVENT,          'event:item'); // 事件的column,table为__MDB_TAB_SERVER 
define(__MDB_COL_SERVERNAME_ALL, 'servername:all'); // 全局服务器列表的column ,table为__MDB_TAB_SERVERNAME
define(__MDB_COL_CONFIG_INI,     'config:ini'); // 存ini格式配置文件,table为__MDB_TAB_SERVER
define(__MDB_COL_CONFIG_CLIENT,  'config:client'); // 存客户端的自有配置文件，格式为版本号|配置文本
define(__KEY_INIDATA,            'inidata'); // INI数据的key,column为__MDB_COL_CONFIG_INI，table为__MDB_TAB_SERVER
define(__KEY_TODELETE_SERVERS,   'todelete_servers'); // 待删除服务器的key
define(__MDB_COL_DAILY_MAIL,     'daily:mail'); // 存是否已经发送日常邮件的标记 
define(__MDB_COL_KEEPWATCH,      'scan:keepwatch'); // 观望事件的column，table为__MDB_TAB_ENGINE 
define(__MDB_COL_SCAN_DURATION,  'scan:duration'); // 扫描间隔时间的column， table为__MDB_TAB_ENGINE
define(__KEY_SCAN_DURATION,      'durationtime'); // 扫描持续时间的key
define(__MDB_COL_SCAN_USABLE,    'scan:usable'); // 服务端是否设置为启用 
define(__MDB_COL_SCAN_PROCSTART, 'scan:procstart'); // 服务端主要进程开始时间
define(__MDB_COL_SCAN_MASTER,    'scan:master'); // 服务端是否做为master运行中
define(__MDB_COL_SCAN_PID,       'scan:pid'); // 服务端pid
define(__KEY_ENGINE_STAT,        'monitorengine|%s'); // 服务端状态的key
define(__USABLE_STAT_OK,         '1'); // 服务端启用
define(__USABLE_STAT_DENY,       '-1'); // 服务端禁用
define(__MASTER_STAT,            '1'); // master 

// 历史信息表的列前缀
define(__PREFIX_COLUMN_GENERIC, 'generic_');
define(__PREFIX_COLUMN_MYSQL, 'mysql_');
define(__PREFIX_COLUMN_SERVING, 'serving_');
define(__PREFIX_COLUMN_DAEMON, 'daemon_');
define(__PREFIX_COLUMN_REPORT, 'report_');
define(__PREFIX_COLUMN_MADN, 'madn_');
define(__PREFIX_COLUMN_HADOOP, 'hadoop_');

/**
 * 存自动配置和界面的服务器组列表的key,column为__MDB_COL_SERVERNAME_ALL ,table为__MDB_TAB_SERVERNAME
 * 本key实际结合sprintf一起使用，最后可以是servgroup1,servgroup2,servgroup3,servgroup4,servgroup5,servgroup??,??为自定义组
 */
define(__KEY_SERVGROUP, 'servgroup%s'); 

// mail
define(__MAIL_USE_SENDMAIL, 0); // 使用sendmail 
define(__MAIL_USE_SMTP,     1); // 使用smtp 

// 本地配置文件的工作模式
define(__INI_WORK_MODE_LOCAL, 0); // 完全本地配置 
define(__INI_WORK_MODE_MDB,   1); // 仅读取MDB设置，剩余去MDB远程获取 

// php5.2之前的parse_ini_string实现用到的常数 
define(__INI_TMP, "monitorserver2_tmp_ini");

// 主机状态的常量
define(__HOST_STATUS_UP,  '1'); // 主机在线
define(__HOST_STATUS_DOWN, '0'); // 主机宕机
define(__HOST_STATUS_NEWADD, '2'); // 新增主机 

//事件标记
define(__EVENT_ACTIVE, '1'); // 事件激活
define(__EVENT_FIX,    '0'); // 事件解决（已经存在的事件，重新set一下标记，代表解决）
define(__NUM_EVENT_VALUE, 2); // event:{eventCode}存的值，其中以|连接，分为为多少个部分，这里为2个部分，如1|disk /usr partition 99%,前面为事件激活，后面为事件描述

// user的报警设置
define(__MAILTYPE_USER_NOSEND,  1); // 不发邮件 
define(__MAILTYPE_USER_CAUTION, 2); // 注意的事件则发送
define(__MAILTYPE_USER_WARNING, 3); // 严重的事件则发送
define(__MAILTYPE_USER_BOTH,    4); // 所有事件都发送
define(__MAILTYPE_USE_BY_GROUP, 5); // 按照用户的用户组所在服务器组的设置发送

// 报警的目标
define(__ALARM_TARGET_SERVER,      0); // 服务器
define(__ALARM_TARGET_SERVERGROUP, 1); // 服务器组

// servergroup报警设置
define(__SRVGRP_ALARM_TYPE_NOSEND,  1); // 不报 
define(__SRVGRP_ALARM_TYPE_CAUTION, 2); // 注意的事件报 
define(__SRVGRP_ALARM_TYPE_WARNING, 3); // 严重的事件报 
define(__SRVGRP_ALARM_TYPE_BOTH,    4); // 所有事件都报 

// 日常邮件发送的状态
define(__DAILY_MAILED_YES, 'sended'); // 当日已经发送过日常邮件

// 扫描间隔秒数
define(__SCAN_INTERVAL, 3);

// max counter
define(__MAX_COUNTER, 99999);

// loginfo 连续上传空认为创建日志失败的次数
define(__LOGININFO_RETRY_EMPTY_TIME, 20);

// mysql的master slave状态
define(__MYSQL_MASTERSLAVE_M, 1); // mysql状态master正常 
define(__MYSQL_MASTERSLAVE_S, 2); // mysql状态slave正常 

// 给智能路由的消息队列的key
define(__MQ_KEY, 'edgeServerList');

// 消息队列key的前缀，操作符
define(__MQ_PREFIX_OPT_NEWADD, 'ADD'); // 新增服务器消息
define(__MQ_PREFIX_OPT_STATUSCHANGE, 'SC'); // 服务器状态改变消息
define(__MQ_TABLE, 'edgeServerStatus'); // 存消息队列的表名(redis) 
define(__TESTSPEED_TABLE, 'monitorTestSpeed'); // 存监控系统测得的数据(redis)

// 进程的名字，需要支持proctitle
define(__PROCNAME_FATHER, 'monitorSrv master proc');
define(__PROCNAME_ZKCLI,  'monitorSrv zkcli proc');

/*{{{infrastructure报警相关*/
// 存在memcache的，基础环境依赖报警的key
define(__INFRASTRUCTURE_ALARM_KEY, 'infrastructure_alarm');
// 存在memcache的，基础环境依赖恢复报警的key
define(__INFRASTRUCTURE_RECOVERY_KEY, 'infrastructure_recovery');

// 存在memcache的，基础环境依赖的状态的key
define(__INFRASTRUCTURE_STATUS, 'infrastructureStatus');
define(__INFRASTRUCTURE_STATUS_ERR, '1'); // infrastructure故障 
define(__INFRASTRUCTURE_STATUS_OK, '0'); // infrastruction正常 

// 存在memcache的，检查到基础环境有问题的那一刻的时间戳
define(__INFRASTRUCTURE_ERR_TS, 'infrastructureErrTs');

// 等待若干秒数以从基础设施错误恢复过来后暂时不进行扫描，等待客户端上传
define(__INFRASTRUCTURE_RECOVERY_LATENCY, 500);
/*}}}*/

// 监控类别项目对照,for配置文件节省byte数
$monitor_item_arr = array( //全部监控项数组 
    array( // generic 
        '000',          // Disk Capacity
        '001',          // Inode Capacity
        '002',          // Load Average
        '003',          // Memory Usuage
        '004',          // Process Number
        '005',          // Cpu Usuage
        '006',          // TCP/IP Service
        '007',          // TCP/IP Connections
        '008',          // Network Flow
    ),
    array( // mysql 
        '017',          // Mysql Connections
        '018',          // Mysql Single Table Size
        '019',          // Mysql Created Threads
        '020',          // Mysql Master/Slave
        '021',          // Mysql Crucial Table
        '029'           // Mysql Seconds Behind Master
    ),
    array( // serving 
        '009',          // Request Number
        '011',          // Advt Publish
        '023',          // Log Creation
        '024'           // Advt Fillrate
    ),
    array( // daemon 
        '012',          // Web Server
        '013',          // Backend Daemon
        '014',          // Login
        '015',          // Advt Deliver
        '016'           // Error Log
    ),
    array( // report 
        '022'           // Wait Process Log Num
    ),
    array( // mdn 
        '025'           // Madn Availability
    ),
    array( // hadoop 
        '026',          // hdfs块平均复制时间dfs.datanode.copyBlockOp_avg_time 
        '027',          // datanode向namenode汇报的平均时间dfs.datanode.heartBeats_avg_time 
        '028'           // datanode读块平均时间dfs.datanode.readBlockOp_avg_time 
    ),
    array( // jail 
    ),
    array( // mdb 
    ),
    array( // gslb 
    ),
    array( // security 
    ),
    array( // monitor
    )
);
?>
