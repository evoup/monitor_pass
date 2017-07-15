<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_zk.php
  +----------------------------------------------------------------------+
  | Comment:zookeeper客户端进程函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 6月 8日 星期五 18时05分30秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-07-24 16:55:56
  +----------------------------------------------------------------------+
 */
class zookeeper_instance extends Zookeeper { 

    function connect_cb($type, $event, $string)
    {
        if ($event == Zookeeper::CONNECTED_STATE)
        {
            $acl=array(
                "perms"=>0x1f,
                "scheme"=>"world",
                "id"=>"anyone"
            );
            $this->create('/monitor_server','',array(0=>$acl),NULL); // 创建永久性一级节点代表监控服务端
            $znode=$this->getChildren('/monitor_server'); // 获取节点下的服务器
            if ((is_array($znode) && !empty($znode) && $znode[0]!=__ZOOKEEPER_NODENAME) || count($znode)>1) { // 如果有不是本机的服务器主动退
                SaveSysLog("[$module_name][class.zookeeper_instance][server node existed,won`t scan and exit]",3);
                exit();
            } else { // 否则创建监控节点 
                include_once('zk_create.php');
                SaveSysLog("[$module_name][class.zookeeper_instance][create new server node and start scan]",3);
            }
        }
    }
}
?>
