<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_madn.php
  +----------------------------------------------------------------------+
  | Comment:madn信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年 7月18日 星期三 15时38分43秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-08-29 16:08:08
  +----------------------------------------------------------------------+
 */
class clsMadn {
    var $m_module_name = "";
    var $m_class_name = __CLASS__; // 类名 
    var $m_client_message = ""; // post来的消息 
    var $m_server = ""; // 服务器名字 
    var $m_url_status = NULL; 
    var $m_url_access_speed = NULL;

    /**
     * @brief php5构造 
     * @return 
     */
    function __construct($client_message){
        $this->clsMadn($client_message);
    }

    /**
     * @brief 构造 
     * @return 
     */
    function clsMadn($client_message) {
        global $module_name;
        $this->m_client_message = $client_message;
        $this->m_field = explode(__SOURCE_SPLIT_TAG4, $client_message);
        $this->m_module_name = $module_name;
    }

    /**
     * @brief 获取Server名 
     * @return 
     */
    function getServerName() {
        // 截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array = explode(__SOURCE_SPLIT_TAG1, $this->m_field[__MADN_FIELD_AVAILABILITY], 3); 
        $Ret = is_string($split_array[1]) ?$split_array[1] :false;
        $this->m_server = $Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取madn被监控URL信息 
     * @return urlname=>array(url,statuscode)二维数组 
     */
    function getUrlStatus() {
        if (!empty($this->m_field)) {
            $fieldStr=$this->m_field[__MADN_FIELD_AVAILABILITY];
            $fieldArr=explode(__SOURCE_SPLIT_TAG2,$fieldStr);
            $fieldArr[0]=substr($fieldArr[0], strrpos($fieldArr[0], __SOURCE_SPLIT_TAG1)+1); // 截掉主机名等字符串
            foreach ((array)$fieldArr as $field) {
                list($name, $url, $statusCode)=explode(__SOURCE_SPLIT_TAG3, $field);
                $retArr[$name]=array(
                    'url'=>@base64_decode($url),
                    'statusCode'=>$statusCode
                );
            }
            $this->m_url_status=$retArr;
            return $retArr;
        } else {
            return false;
        }
    }


    /**
     * @brief 获取URL访问速度信息
     */
    function getUrlAccessSpeed() {
        if ( !empty($this->m_field) ) {
            $fieldStr=$this->m_field[__MADN_FIELD_TESTSPEED];
            $fieldArr=explode(__SOURCE_SPLIT_TAG2,$fieldStr);
            foreach ( (array)$fieldArr as $field ) {
                list($name, $url, $statusCode, $size_download, $time_total)=explode('|',$field,5);
                $retArr[$name]=Array(
                    'url'=>base64_decode($url),
                    'statusCode'=>$statusCode,
                    'size_download'=>$size_download,
                    'time_total'=>$time_total
                );
            }
            $this->m_url_access_speed = $retArr;
            return $retArr;
        } else {
            return false;
        }
    }

    /**
     * @brief 获取全部信息
     * @return 类对象
     */
    function getAllInfo() {
        $Ret = (false!==$this->getServerName() && false!==$this->getUrlStatus() && false!==$this->getUrlAccessSpeed()) ?$this :false;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo fail!]");
        } else {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo ok!]");
        }
        return ($Ret);
    }
}
?>
