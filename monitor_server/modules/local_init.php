<?php
/*
  +----------------------------------------------------------------------+
  | Name:加载守护进程额外的一些变量
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 6月28日 星期四 13时33分55秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-22 11:08:02
  +----------------------------------------------------------------------+
 */

$module_name='local_init';

if (!file_exists(__CONF_FILE2)) {
    $iniStr=<<<EOT
zookeeper_host="127.0.0.1:2181"
redis_host="127.0.0.1:6379"
api_url_prefix="http://domain"
ui_url_prefix="http://domain"
memcache_host="127.0.0.1:11211"
client_fpm_stop="/usr/local/etc/rc.d/phpfpm_monitor_client stop"
client_fpm_start="/usr/local/etc/rc.d/phpfpm_monitor_client start"
EOT;
    file_put_contents(__CONF_FILE2,$iniStr);
    echo "please edit ".__CONF_FILE2."\n";
    doExit("please edit ".__CONF_FILE2);
} else {
    $localIniSetting=parse_ini_string(file_get_contents(__CONF_FILE2));
    list($mcd_ip,$mcd_port)=explode(':',$localIniSetting['memcache_host']);
    list($redis_ip,$redis_port)=explode(':',$localIniSetting['redis_host']);
    $api_url_prefix=$localIniSetting['api_url_prefix'];
    $snapshot_url_prefix=$localIniSetting['ui_url_prefix'];
    $client_fpm_stop_shell=$localIniSetting['client_fpm_stop'];
    $client_fpm_start_shell=$localIniSetting['client_fpm_start'];
    SaveSysLog("[$module_name][redis_ip:$redis_ip][redis_port:$redis_port]",2); 
    SaveSysLog("[$module_name][snapshot_url_prefix:$snapshot_url_prefix]",2); 
    if ($snapshot_url_prefix=='http://domain') {
        echo "please edit ".__CONF_FILE2."\n";
        doExit("please edit ".__CONF_FILE2);
    }
}

// redis
$single_redis_server = array(
    'host'     => $redis_ip,
    'port'     => $redis_port,
    'database' => 15
);

$GLOBALS['redis_client'] = new Predis_Client($single_redis_server);
$GLOBALS['redis_client']->select(__MQ_TABLE);
$GLOBALS['zookeeper_host']=$localIniSetting['zookeeper_host'];
define(__ZOOKEEPER_NODENAME, gethostname());
/* {{{ 系统信号
 */
/** 系统信号
 * 'kill -l' gives you a list of signals available on your UNIX.
 * Eg. Ubuntu:
 *
 *  1) SIGHUP      2) SIGINT      3) SIGQUIT      4) SIGILL
 *  5) SIGTRAP      6) SIGABRT      7) SIGBUS      8) SIGFPE
 *  9) SIGKILL    10) SIGUSR1    11) SIGSEGV    12) SIGUSR2
 * 13) SIGPIPE    14) SIGALRM    15) SIGTERM    17) SIGCHLD
 * 18) SIGCONT    19) SIGSTOP    20) SIGTSTP    21) SIGTTIN
 * 22) SIGTTOU    23) SIGURG      24) SIGXCPU    25) SIGXFSZ
 * 26) SIGVTALRM  27) SIGPROF    28) SIGWINCH    29) SIGIO
 * 30) SIGPWR      31) SIGSYS      33) SIGRTMIN    34) SIGRTMIN+1
 * 35) SIGRTMIN+2  36) SIGRTMIN+3  37) SIGRTMIN+4  38) SIGRTMIN+5
 * 39) SIGRTMIN+6  40) SIGRTMIN+7  41) SIGRTMIN+8  42) SIGRTMIN+9
 * 43) SIGRTMIN+10 44) SIGRTMIN+11 45) SIGRTMIN+12 46) SIGRTMIN+13
 * 47) SIGRTMIN+14 48) SIGRTMIN+15 49) SIGRTMAX-15 50) SIGRTMAX-14
 * 51) SIGRTMAX-13 52) SIGRTMAX-12 53) SIGRTMAX-11 54) SIGRTMAX-10
 * 55) SIGRTMAX-9  56) SIGRTMAX-8  57) SIGRTMAX-7  58) SIGRTMAX-6
 * 59) SIGRTMAX-5  60) SIGRTMAX-4  61) SIGRTMAX-3  62) SIGRTMAX-2
 * 63) SIGRTMAX-1  64) SIGRTMAX
 *
 * SIG_IGN, SIG_DFL, SIG_ERR are no real signals
 *
 */
$GLOBALS['_daemon']['signalsName'] = array(
    SIGHUP    => 'SIGHUP',
    SIGINT    => 'SIGINT',
    SIGQUIT   => 'SIGQUIT',
    SIGILL    => 'SIGILL',
    SIGTRAP   => 'SIGTRAP',
    SIGABRT   => 'SIGABRT',
    7         => 'SIGEMT',
    SIGFPE    => 'SIGFPE',
    SIGKILL   => 'SIGKILL',
    SIGBUS    => 'SIGBUS',
    SIGSEGV   => 'SIGSEGV',
    SIGSYS    => 'SIGSYS',
    SIGPIPE   => 'SIGPIPE',
    SIGALRM   => 'SIGALRM',
    SIGTERM   => 'SIGTERM',
    SIGURG    => 'SIGURG',
    SIGSTOP   => 'SIGSTOP',
    SIGTSTP   => 'SIGTSTP',
    SIGCONT   => 'SIGCONT',
    SIGCHLD   => 'SIGCHLD',
    SIGTTIN   => 'SIGTTIN',
    SIGTTOU   => 'SIGTTOU',
    SIGIO     => 'SIGIO',
    SIGXCPU   => 'SIGXCPU',
    SIGXFSZ   => 'SIGXFSZ',
    SIGVTALRM => 'SIGVTALRM',
    SIGPROF   => 'SIGPROF',
    SIGWINCH  => 'SIGWINCH',
    28        => 'SIGINFO',
    SIGUSR1   => 'SIGUSR1',
    SIGUSR2   => 'SIGUSR2',
);
/* }}} */
?>
