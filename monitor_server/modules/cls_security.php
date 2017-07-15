<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_security.php
  +----------------------------------------------------------------------+
  | Comment:监控安全类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-03 16:16:41
  +----------------------------------------------------------------------+
 */
class clsSecurity{
    var $m_module_name="";
    var $m_class_name=__CLASS__; // 类名 
    var $m_client_message=""; // post来的消息 
    var $m_server=""; // 被监控的server名字 
    var $m_field=array(); // 以下各成员变量的组成的数组 
    var $m_shell_md5;
    var $m_ssh_status;
    var $m_software_version;
    var $m_user_status;
    var $m_root_proc;
    var $m_syn_status;
    var $m_network_safe;
    var $m_permit_shells;

    /**
     * 消息格式
     * Shell md5#Ssh status#Software version#User status#Root process#Syn status#Sniffer detect
     */

    /**
     * @brief php5构造 
     * @return 
     */
    function __construct($client_message) {
        $this->clsSecurity($client_message);
    }

    /**
     * @brief php4构造 
     * @return 
     */
    function clsSecurity($client_message) {
        $this->m_client_message=$client_message;
        $this->m_field=explode(__SOURCE_SPLIT_TAG2,$client_message);
        global $module_name;
        $this->m_module_name=$module_name;
        $this->m_permit_shells = array(
            'ls',
            'netstat',
            'ps',
            'strings',
            'top',
            'login',
            'su',
            'init',
            'sysctl',
            'find',
            'passwd',
            'cat',
            'chmod',
            'chown',
            'df',
            'egrep',
            'fgrep',
            'grep',
            'kill',
            'more',
            'ifconfig',
            'du',
            'file',
            'killall',
            'locate',
            'md5',
            'size',
            'sort',
            'touch',
            'w',
            'whatis',
            'whereis',
            'which',
            'who',
            'cron',
            'inetd',
            'tcpd',
            'adduser',
            'vipw',
            'date',
            'stat',
            'users',
            'watch',
            'whoami',
            'id',
            'env',
            'groups',
            'mount',
            'vmstat',
            'head',
            'tail',
            'ln',
            'mkdir',
            'echo',
            'sleep',
            'unlink',
            'mv',
            'hostname'
        );
    }

    /**
     * @brief 获取Server名 
     * @return 
     */
    function getServerName() {
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__SERVING_FIELD_SUMMARY],3); 
        $Ret=is_string($split_array[1])?$split_array[1]:false;
        $this->m_server=$Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 对第一个field去掉flag:serverid:前缀
     * @return  
     */
    function getFirestFieldArray() {
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__SECURITY_FIELDS_SHELLMD5],3); 
        return ($split_array);
    }

    /**
     *@brief 获取常用shell的md5值
     */
    function getShellMd5() {
        $temp_array=$this->getFirestFieldArray();
        $shell_md5_str=$temp_array[2];
        $shell_md5_arr = explode(__SOURCE_SPLIT_TAG3, $shell_md5_str);
        $shell_md5_arr = array_unique($shell_md5_arr);
        foreach ($shell_md5_arr as $tmpStr) {
            list($cmd, $md5hash) = explode(',', $tmpStr);
            if (in_array($cmd, $this->m_permit_shells) && strlen($md5hash)==32) {
                $res_cmd[$cmd]=$md5hash;
            }
            SaveSysLog("[$this->m_module_name][$this->m_class_name][cmd:$cmd][md5hash:$md5hash]",5);
        }
        $md5Str = json_encode($res_cmd);
        echo $md5Str;
    }

    /**
     * @brief 获取全部信息
     * @return 类对象
     */
    function getAllInfo() {
        //print_r($this->m_field);
        $Ret=(false!==$this->getServerName() && false!==$this->getShellMd5())?$this:false;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo fail!]");
        } else {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo ok!]");
        }
        return ($Ret);
    }
}
?>
