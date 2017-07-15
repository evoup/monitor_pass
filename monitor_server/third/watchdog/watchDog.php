<?php
/*
  +----------------------------------------------------------------------+
  | Name:watchdogClient.php
  +----------------------------------------------------------------------+
  | Comment:监控引擎watchDog CGI,检测能否达到监控服务端
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com
  +----------------------------------------------------------------------+
  | Create:2011年11月25日 星期五 16时54分44秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-25 16:55:01
  +----------------------------------------------------------------------+
 */

/*
 *看守类似这个Url,返回状态字符串
 *"http://211.136.107.44/monitor_server2r1_0/m1"
 */
error_reporting(0);

if (false!=watchDog()) {
    print "status:access monitor server ok";
} else {
    print "status:access monitor server fail";
}

/**
 *@brief 监控服务端的监护者函数，提供到服务端连接状态的反馈给监控服务端
 *@return 如果可以到达返回true,否则返回false
 */
function watchDog() {
    $conf=parse_ini_file(dirname(__FILE__).'/conf/conf.ini');
    $fp = fsockopen($conf['ip'], $conf['port'], $errno, $errstr, 2);
    $time=time(); // 防止死循环
    if ($fp) {
        $out = "GET ".$conf['requri']." HTTP/1.0\r\n";
        $out .="Host: ".$conf['host']."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        while (!feof($fp) && time()-$time<5) {
            $line=fgets($fp, 128);
            if ($line=='HTTP/1.1 200 OK'."\r\n") {
                $ret=true;
            }
            break;
        }
    }
    fclose($fp);
    return $ret;
}
?>
