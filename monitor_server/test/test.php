<?php
/*
  +----------------------------------------------------------------------+
  | Name:test.php 
  +----------------------------------------------------------------------+
  | Comment:black box测试脚本，用wireshark抓得的正式环境的包模拟客户端上传
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
define(__WIRESHARK_FIRST_FILED,         0); //wireshark导出http数据后的首字段如,这些字段开头的行不需要获取 
//"No.","Frame","Ethernet","Internet","Transmission","Hypertext","Line-based"

define(__WIRESHARK_SRC_DST_FIRST_FIELD, 2); //wireshark的source destiny数据行(也是不要获取的),格式如下
//  65290 874.990757  211.136.105.227       211.136.105.221       HTTP     POST /monitor_web/server/index.php?c=Upload&a=Post HTTP/1.1  (application/x-www-form-urlencoded)

$mcd_server="127.0.0.1";
$mcd_port=11211;
$mcd=memcache_connect($mcd_server, $mcd_port); //初始化memcache对象
$res=memcache_get($mcd,"test>".basename($_SERVER['argv'][1]));
if(!$res){
    memcache_set($mcd,"test>".basename($_SERVER['argv'][1]),"1",0,3600);
}
$start=0;
for(;;){
    if(!empty($_SERVER['argv'][1]) && is_file($_SERVER['argv'][1])){
        $fp=fopen($_SERVER['argv'][1],"r"); //获取测试脚本1，包含server类和delvivering或者serving类的post的数据信息 
    }
    while (!feof($fp)){
        $buffer=fgets($fp,4096);
        $buf_first_field=explode(" ",$buffer);
        $first_field = $buf_first_field[__WIRESHARK_FIRST_FILED];
        if(!in_array($first_field,array("No.","Frame","Ethernet","Internet","Transmission","Hypertext",
            "Line-based")) && !is_numeric($buf_first_field[__WIRESHARK_SRC_DST_FIRST_FIELD])){ //去除不需要的数据项 
                $buffer=str_replace('    [truncated] ',"",$buffer); //去掉开始可能包含的truncated字符, TODO 重新编译wireshark,以支持不截断 
                $buffer=str_replace('    ',"",$buffer); //去掉开始可能包含的4个空格
                $buffer=str_replace('\n','',$buffer);
                //仍然存在部分不需要post的数据，不过需要post的数据格式已经正确
                if(!empty($buffer)){
                    //echo $buffer; //得到要post的测试数据行
                    $data=$buffer;
                    $tmpArr=explode(':',$data);
                    $hostname=$tmpArr[1];
                    /*{{{使用curl post本行数据*/
                    $tuCurl = curl_init(); 
                    //curl_setopt($tuCurl, CURLOPT_URL, "http://127.0.0.1:8282/monitor_server2/index.php?c=Upload"); 
                    curl_setopt($tuCurl, CURLOPT_URL, "http://127.0.0.1:8080/monitor_server2r1_1/{$hostname}/m1"); 
                    curl_setopt($tuCurl, CURLOPT_PORT , 8080); 
                    curl_setopt($tuCurl, CURLOPT_POST, 1); 
                    curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data); 
                    curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-Type: text/html", "Content-length: ".strlen($data))); 
                    //usleep(2000000); //等待0.6秒再发送，太多TIME_WAIT扛不过来(压力测试测过，没问题)
                    
                    /* curl获取的header中出现HTTP/1.1 100 continue的问题 */
                    curl_setopt( $curl_handle, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
                    curl_exec($tuCurl);
                    curl_close($tuCurl);
                    $start++;
                    print "emulator monitor client req send {$start}\r\n";
                    /*}}}*/
                }
            }
    }
    fclose($fp);

}
memcache_delete($mcd,"test>".basename($_SERVER['argv'][1]),0);
?>
