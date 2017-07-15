<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_report.php
  +----------------------------------------------------------------------+
  | Comment:report信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */
class clsReport{

    var $m_server=""; //服务器名字 
    var $m_client_message; //post来的消息 
    var $m_field=array(); //以下各成员变量的组成的数组
    var $m_module_name="";
    var $m_log_process_stat;  //log process状态 
    var $m_process_speed; //处理速度 
    var $m_wait_process_log_num;  //待处理log数 

    /**
     * @brief 构造 
     * @return 
     */
    function __construct($client_message){
        $this->clsReport($client_message);
    }

    /**
     * @brief 构造 
     * @return 
     */
    function clsReport($client_message){
        global $module_name;
        $this->m_client_message=$client_message;
        $this->m_field=explode(__SOURCE_SPLIT_TAG2,$client_message);
        $this->m_module_name=$module_name;
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
     * @brief 获取日志处理情况
     * @return 
     */
    function getLogProcessStatus(){
        $process_stat=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__REPORT_FIELD_LOG_PROCESS_STAT]); //取得日志处理情况
        $Ret=sizeof($process_stat)==3?true:false; //格式如5report:1 ，是三段
        if($Ret){
            $Ret=true; //TODO 客户端数据格式没有实现，暂时不验证
            $Ret && $this->m_log_process_stat=$process_stat[2]; //取最后一段是为日志处理情况
            return ($Ret); 
        }
    } 

    /**
     * @brief 获取处理速度
     * @return 
     */
    function getProcessSpeed(){
        $this->m_field[__REPORT_FIELD_PROCESS_SPEED]+=0; 
        $this->m_process_speed=$this->m_field[__REPORT_FIELD_PROCESS_SPEED]; //取得处理速度 
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getProcessSpeed return:".$this->m_process_speed."]",5);
        return true; //转换为数字后数据有效，直接返回true 
    } 

    /**
     * @brief 获取待处理log数
     * @return 
     */
    function getWaitProcessLogNum(){
        $this->m_field[__REPORT_FIELD_WAIT_PROCESS_LOG_NUM]+=0;
        $this->m_wait_process_log_num=$this->m_field[__REPORT_FIELD_WAIT_PROCESS_LOG_NUM]; //取得待处理log数 
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getWaitProcessLogNum return:".$this->m_wait_process_log_num."]",5);
        return true; //转换为数字数据有效，直接返回true 
    } 

    /**
     * @brief 获取所有信息 
     * @return 
     */
    function getAllInfo(){
        $Ret=(false!==$this->getServerName() && false!==$this->getLogProcessStatus() && false!==$this->getProcessSpeed() && false!==$this->getWaitProcessLogNum())?true:false;
        return ($Ret);
    }
}
?>
