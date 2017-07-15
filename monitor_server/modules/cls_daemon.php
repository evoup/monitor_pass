<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_daemon.php
  +----------------------------------------------------------------------+
  | Comment:daemon信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
class clsDaemon{

    var $m_server=""; //服务器名字 
    var $m_webserver_status; //web服务器状态 
    var $m_daemon_status; //守护进程状态 
    var $m_login_status; //Login状态  
    var $m_adserv_status; //Adserv Status状态 
    var $m_error_log_status; //Error Log状态 
    var $m_client_message; //post来的消息 
    var $m_field=array(); //以下各成员变量的组成的数组
    var $m_module_name="";
    var $m_class_name="";

    /**
     * @brief php5构造 
     * @return 
     */
    function __construct($client_message){
        $this->clsDaemon($client_message);
    }

    /**
     * @brief php4构造 
     * @return 
     */
    function clsDaemon($client_message){
        $this->m_client_message=$client_message;
        $this->m_field=explode(__SOURCE_SPLIT_TAG2,$client_message);
        global $module_name;
        $this->m_module_name=$module_name;
        $this->m_class_name=__CLASS__;
    }

    /**
     * @brief 获取Server名 
     * @return 
     */
    function getServerName(){
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__SERVER_FIELD_SUMMARY],3); 
        $Ret=is_string($split_array[1])?$split_array[1]:false;
        $this->m_server=$Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取web服务器状态,执行成功保存状态到m_webserver_status
     * @return true false 
     */
    function getWebServerStatus(){
        $webserver_status=$this->m_field;
        $temp_webserver_status=explode(__SOURCE_SPLIT_TAG1,$webserver_status[__DAEMON_FIELD_WEBSRV_STAT]);
        $Ret=sizeof($temp_webserver_status)==3?true:false; //格式如4:daemon1:1 ，是三段
        if($Ret){
            $temp_webserver_status[2]+=0;
            $Ret=is_numeric($temp_webserver_status[2]) &&
                in_array($temp_webserver_status[2],array(0,1))?true:false; //取最后一段(不用is_bool转换，因为1过不了) 
            if($Ret){
                $this->m_webserver_status=$temp_webserver_status[2];
            }
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getWebServerStatus return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取守护进程状态,执行成功保存状态到m_daemon_status
     * @return true false 
     */
    function getDaemonStatus(){
        $this->m_field[__DAEMON_FIELD_DAEMON_STAT]+=0;
        $Ret=is_numeric($this->m_field[__DAEMON_FIELD_DAEMON_STAT]) &&
            in_array($this->m_field[__DAEMON_FIELD_DAEMON_STAT],array(0,1))?true:false;
        if($Ret){
            $this->m_daemon_status=$this->m_field[__DAEMON_FIELD_DAEMON_STAT];
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getDaemonStatus return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取Login状态,执行成功保存状态到m_login_status
     * @return true false 
     */
    function getLoginStatus(){
        $this->m_field[__DAEMON_FIELD_LOGIN_STAT]+=0;
        $Ret=is_numeric($this->m_field[__DAEMON_FIELD_LOGIN_STAT])
            && in_array($this->m_field[__DAEMON_FIELD_LOGIN_STAT],array(0,1))?true:false;
        if($Ret){
            $this->m_login_status=$this->m_field[__DAEMON_FIELD_LOGIN_STAT];
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getLoginStatus return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取Adserv Satatus状态,执行成功保存状态到m_adserv_status
     * @return true false 
     */
    function getAdservStatus(){
        $this->m_field[__DAEMON_FIELD_ADSERV_STAT]+=0;
        $Ret=is_numeric($this->m_field[__DAEMON_FIELD_ADSERV_STAT]) && 
            in_array($this->m_field[__DAEMON_FIELD_ADSERV_STAT],array(0,1))?true:false;
        if($Ret){
            $this->m_adserv_status=$this->m_field[__DAEMON_FIELD_ADSERV_STAT];
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getAdservStatus return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取Error log状态,执行成功保存状态到m_error_log_status
     * @return true false 
     */
    function getErrorLogStatus(){
        $this->m_field[__DAEMON_FIELD_ERRLOG_STAT]+=0;
        $Ret=is_numeric($this->m_field[__DAEMON_FIELD_ERRLOG_STAT]) && 
            in_array($this->m_field[__DAEMON_FIELD_ERRLOG_STAT],array(0,1))?true:false;
        if($Ret){
            $this->m_error_log_status=$this->m_field[__DAEMON_FIELD_ERRLOG_STAT];
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getErrorLogStatus return:$Ret]",5);
        return ($Ret);
    } 

    /**
     * @brief 获取全部信息 
     * @return 
     */
    function getAllInfo(){
        $Ret=(false!==$this->getServerName() && false!==$this->getWebServerStatus() && false!==$this->getDaemonStatus() && 
            false!==$this->getLoginStatus() && false!==$this->getAdservStatus() && false!==$this->getErrorLogStatus())?true:false;
        return ($Ret);
    }
} 
?>
