<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_delivering.php
  +----------------------------------------------------------------------+
  | Comment:delivering监控信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-03 16:12:13
  +----------------------------------------------------------------------+
 */
class clsDelivering{

    /* 定义全部数据结构，node的变量为子结构 */
    var $m_module_name="";
    var $m_class_name=__CLASS__; //类名 
    var $m_client_message=""; //post来的消息 
    var $m_server=""; //被监控的server名字 
    var $m_field=array(); //以下各成员变量的组成的数组 
    var $m_request=""; //每秒请求数 
    var $m_adimage=array(array("domain_id"=>"","ad_pos_num"=>"","ad_campaign_num"=>"","delivering_cache_num"=>"","pack_serialnum"=>"","publish_role"=>"")); //广告发布 
    var $m_loginfo=array("total_log_num","upload_log_num","file_name","file_md5"); //loginfo 
    var $m_traffic=""; //带宽 
    var $m_enginestatus=""; //引擎状态(0不正常,1正常)

    //request#adimage#loginfo#traffic#enginestatus
    /**
     * @brief php5构造 
     * @return 
     */
    function __construct($client_message) {
        $this->clsDelivering($client_message);
    }

    /**
     * @brief php4构造 
     * @return 
     */
    function clsDelivering($client_message) {
        $this->m_client_message=$client_message;
        $this->m_field=explode(__SOURCE_SPLIT_TAG2,$client_message);
        global $module_name;
        $this->m_module_name=$module_name;
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
     * @brief 对第一字段的数据再分成数组 
     * @return  
     */
    function getFirestFieldArray() {
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__SERVING_FIELD_SUMMARY],3); 
        return ($split_array);
    }

    /**
     * @brief 获取单台负荷每秒请求数 
     * @return 
     */
    function getRequest() {
        $temp_array=$this->getFirestFieldArray();
        $request_str=$temp_array[2];
        SaveSysLog("[$this->m_module_name][$this->m_class_name][request_str:$request_str]",5);
        $request_str+=0; //手动转换为数字 
        $this->m_request=$request_str;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getRequest return:1]",5);
        return ($this->m_request);
    }

    /**
     * @brief 获取AdImage信息 
     * @return adImage=>domaininfo二维数组 
     */
    function getAdImage() {
        global $module_name;
        $fun_name=__FUNCTION__;
        $adimage_str=$this->m_field[__SERVING_FILED_ADIMAGE];
        if (empty($adimage_str)) { //允许客户端不传此项 
            $this->m_adimage=array_pad(array(),__SERVING_FILED_ADIMAGE_NUM,""); //传空则成员为空 
            return true;
        } else { //如传递则检查数据合法性 
            $domaininfo_arr=is_array(explode(__SOURCE_SPLIT_TAG3,$adimage_str))?explode(__SOURCE_SPLIT_TAG3,$adimage_str):false; 
            //对每个domaininfo回调检查其类型
            $check_ok=(false!=$domaininfo_arr)?(in_array(false,array_map("validDomainInfo",$domaininfo_arr))?false:true):false;
            SaveSysLog("[$module_name][$fun_name][check_ok:$check_ok]",5);
        }
        if (false!=$check_ok) {
            foreach ($domaininfo_arr as $domaininfo_child) { // 遍历domaininfo数组下所有domaininfo数组元素
                $temp_domaininfo_arr[]=array_combine(array("domain_id","ad_pos_num","ad_campaign_num","delivering_cache_num","pack_serialnum","publish_role"),
                    array_pad(array_values(explode(__SOURCE_SPLIT_TAG4,$domaininfo_child)), 6 ,""));  //对可能出现的客户端少传数据域自动补足空数据值
            }
        }
        $this->m_adimage=(false!=$check_ok)?$temp_domaininfo_arr:$this->m_adimage; //返回给成员变量 
        $Ret=$check_ok?$this->m_adimage:false;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getAdImage return $Ret]",5);
        return ($Ret); 
    }

    /**
     * @brief 获取loginfo信息 
     * @return 
     */
    function getLogInfo() {
        $loginfo_str=$this->m_field[__SERVING_FILED_LOGINFO];
        if (empty($loginfo_str)) { //允许客户端不传此项 
            $this->m_loginfo=array_pad(array(),__SERVING_FILED_LOGINFO_NUM,''); //传空则成员为空 
            return true;
        } else { //如果传则检查数据合法性 
            $loginfo_arr=is_array(explode(__SOURCE_SPLIT_TAG3,$loginfo_str))?explode(__SOURCE_SPLIT_TAG3,$loginfo_str):false; 
            list($total_log_num,$upload_log_num,$file_name,$file_md5)=$loginfo_arr; //允许全部传空或者少传 
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getLogInfo][total_log_num:$total_log_num upload_log_num:$upload_log_num file_name:$file_name file_md5:$file_md5]",5);
            $total_log_num+=0; //保证为空的情况下也是数字 
            $upload_log_num+=0;
            $totallognum_ok=(is_numeric($total_log_num) && $total_log_num>=0)?true:false; //检查产生log条目数 
            $uploadlognum_ok=(is_numeric($upload_log_num) && $upload_log_num>=0)?true:false; //检查上传log条目数 
            $filename_ok=is_string($file_name) || empty($file_name)?true:false;
            //$filemd5_ok=md5($file_name)==$file_md5 || empty($file_md5)?true:false;
            $filemd5_ok=true; // md5就取前8位 
            $Ret=($totallognum_ok && $uploadlognum_ok && $filename_ok && $filemd5_ok)?true:false;
        }
        //成员变量赋值
        $this->m_loginfo=(false!=$Ret)?array_combine($this->m_loginfo,array($total_log_num,$upload_log_num,$file_name,$file_md5)):$this->m_loginfo;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][totallognum_ok:$totallognum_ok][uploadlognum_ok:$uploadlognum_ok][filename_ok:$filename_ok][filemd5_ok:$filemd5_ok]",5);
        }
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getLogInfo return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取traffic信息
     * @return 
     */
    function getTraffic() {
        $traffic_str=$this->m_field[__SERVING_FILED_TRAFFIC];
        SaveSysLog("[$this->m_module_name][$this->m_class_name][traffic_str:$traffic_str]",5);
        $traffic_str+=0; //保证为空的情况下也是数字 
        $Ret=(is_numeric($traffic_str) || is_float($traffic_str)) && $traffic_str>=0?true:false; 
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getTraffic return:$Ret]",5);
        $this->m_traffic=$Ret?$traffic_str:$this->m_traffic;
        return ($Ret);
    } 

    /**
     * @brief 获取引擎状态
     * @return 
     */
    function getEngineStatus() {
        $engine_stat_str=$this->m_field[__SERVING_FILED_ENGINESTAT];
        $Ret=(in_array($engine_stat_str,array(0,1)) || empty($engine_stat_str))?true:false;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getEngineStatus return:$Ret]",5);
        $this->m_enginestatus=$Ret?$engine_stat_str:$this->m_enginestatus;
        return ($Ret);

    }

    /**
     * @brief 获取全部信息 
     * @return 类对象
     */
    function getAllInfo() {
        $Ret=(false!==$this->getServerName() && false!==$this->getRequest() && false!==$this->getAdImage() &&
            false!==$this->getLogInfo() && false!==$this->getTraffic() && false!==$this->getEngineStatus())?$this:false;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo fail!]");
        } else {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo ok!]");
        }
        return ($Ret);
    }
}
?>
