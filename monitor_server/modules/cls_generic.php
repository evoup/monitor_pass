<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_generic.php
  +----------------------------------------------------------------------+
  | Comment:generic监控信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */

class clsGeneric {

    /* 定义全部数据结构，node的变量为子结构 */
    var $m_module_name = "";
    var $m_class_name = __CLASS__; // 类名 
    var $m_client_message = ""; // post来的消息 
    var $m_server = ""; // 被监控的server名字 
    var $m_field = array(); // 以下各成员变量的组成的数组 
    var $m_summary = array("load", "uptime_day", "uptime_his", "tcp_connections"); 
    var $m_cpu = array("use", "nice", "system", "interrupt", "idle");
    var $m_mem = array("active", "inact", "wired", "cache", "buf", "free");
    var $m_swap = array("total", "used", "free", "inse");
    var $m_disk = array(array("mounted" => "", "capacity" => "", "iused" => ""));
    var $m_process = array("sum", "starting", "running", "sleeping", "stopped", "zombie", "waiting", "lock");
    var $m_network = array(array("ifname" => "", "in" => "", "out" => ""));
    var $m_link = array(array("sserver" => "", "dserver" => "", "flow" => ""));
    var $m_service = array(array("name" => "", "port" => "", "status" => ""));

    /**
     * @brief 构造 
     * @return 
     */
    function __construct($client_message) {
        $this->clsGeneric($client_message);
    }

    /**
     * @brief 构造 
     * @return 
     */
    function clsGeneric($client_message) {
        $this->m_client_message = $client_message;
        $this->m_field = explode(__SOURCE_SPLIT_TAG2, $client_message);
        global $module_name;
        $this->m_module_name = $module_name;
    }

    /**
     * @brief 获取Server名 
     * @return 
     */
    function getServerName() {
        // 截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array = explode(__SOURCE_SPLIT_TAG1, $this->m_field[__SERVER_FIELD_SUMMARY], 3); 
        $Ret = is_string($split_array[1]) ?$split_array[1] :false;
        $this->m_server = $Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取Server的概要数据
     * @return m_summary类型数组 
     */
    function getSummary() {
        $temp_array = $this->getFirestFieldArray();
        $summary_str = $temp_array[2]; // 对summary field去掉flag:serverid:前缀 
        $Ret = sizeof(explode(__SOURCE_SPLIT_TAG3, $summary_str))==__SERVER_SUMMARY_FIELDS_NUM ?explode(__SOURCE_SPLIT_TAG3,$summary_str) :false;
        $this->m_summary = (false!=$Ret && is_array($Ret)) ?array_combine($this->m_summary,$Ret) :$this->m_summary;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getSummary return:$Ret]",5);
        return ($this->m_summary);
    }

    /**
     * @brief 对第一字段的数据再分成数组 
     * @return  
     */
    function getFirestFieldArray() {
        // 截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array = explode(__SOURCE_SPLIT_TAG1, $this->m_field[__SERVER_FIELD_SUMMARY], 3); 
        return ($split_array);
    }

    /**
     * @brief 获取cpu信息 
     * @return m_cpu类型数组或flase 
     */
    function getCpu() {
        $cpu_str = $this->m_field[__SERVER_FIELD_CPU]; // 取得未cpu分割字符串 
        if (empty($cpu_str)) { // 允许客户端不传递此项 
            $this->m_cpu = array_pad(array(), __SERVER_FIELD_CPU_NUM, ""); // 传空则成员为空 
            return true; 
        } else { // 如传递则检查数据合法性 
            // 取得分割后cpu一维数组
            $cpu_arr = sizeof(explode(__SOURCE_SPLIT_TAG3, $cpu_str))==__SERVER_CPU_ITEMS_NUM ?explode(__SOURCE_SPLIT_TAG3,$cpu_str) :false;
            // 对每个元素回调检查其类型
            $check_ok = (false!=$cpu_arr && is_array($cpu_arr)) ?(in_array(false, array_map("validDigital",$cpu_arr)) ?false :true) :false;
            // 成员变量赋值
            $this->m_cpu = (false!=$check_ok) ?array_combine($this->m_cpu,$cpu_arr) :$this->m_cpu;
            $Ret = $check_ok ?$this->m_cpu :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getCpu return:$Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取mem信息
     * @return mem数组 
     */
    function getMem() {
        $mem_str = $this->m_field[__SERVER_FIELD_MEM];
        if (empty($mem_str)) { // 允许客户端不传此项 
            $this->m_mem = array_pad(array(), __SERVER_FIELD_MEM_NUM,""); // 传空则成员为空 
            return true; 
        } else { // 如传递则检查数据合法性 
            $mem_arr = sizeof(explode(__SOURCE_SPLIT_TAG3, $mem_str))==__SERVER_MEM_ITEMS_NUM ?explode(__SOURCE_SPLIT_TAG3, $mem_str) :false;
            $check_ok = (false!=$mem_arr && is_array($mem_arr)) ?(in_array(false, array_map("validMem", $mem_arr)) ?false :true) :false;
            $mem_arr = array_map('convertToKb', $mem_arr); // 转换到不带单位的Kb 
            $this->m_mem = (false!=$check_ok) ?array_combine($this->m_mem, $mem_arr) :$this->m_mem;
            $Ret = $check_ok ?$this->m_mem :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getMem return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取swap信息
     * @return swap数组 
     */
    function getSwap() {
        $swap_str = $this->m_field[__SERVER_FIELD_SWAP];
        if (empty($swap_str)) { // 允许客户端不传此项 
            $this->m_swap = array_pad(array(), __SERVER_FIELD_SWAP_NUM, ""); // 传空则成员为空 
            return true; 
        } else { // 如传递则检查数据合法性 
            $swap_arr = sizeof(explode(__SOURCE_SPLIT_TAG3, $swap_str))==__SERVER_SWAP_ITEMS_NUM ?explode(__SOURCE_SPLIT_TAG3, $swap_str) :false;
            // 对swap前三位数据的有效性检查
            $check_ok = (false!=$swap_arr && is_array($swap_arr)) ?(in_array(false, array_map("validMem", array_slice($swap_arr, 0, 3))) ?false :true) :false;
            for ($i=0; $i<count($swap_arr)-1; $i++) { // 前三位去掉M或者K，转为不带单位的Kb数 
                $swap_arr[$i] = convertToKb($swap_arr[$i]); // 转换到不带单位的Kb 
            }
            // TODO 对最后一位百分比做检查,客户端尚未实现
            $this->m_swap = (false!=$check_ok) ?array_combine($this->m_swap, $swap_arr) :$this->m_swap;
            $Ret = $check_ok ?$this->m_swap :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getSwap return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取disk信息 
     * @return disk=>partition二维数组 
     */
    function getDisk() {
        $disk_str = $this->m_field[__SERVER_FIELD_DISK]; // 取得未分割disk字符串,disk下包含若干个partition 
        if (empty($disk_str)) { // 允许客户端不传此项 
            return true;
        } else { // 如传递则检查数据合法性 
            // 劈开disk为各partition明细数组
            $disk_arr = is_array(explode(__SOURCE_SPLIT_TAG3, $disk_str)) ?explode(__SOURCE_SPLIT_TAG3, $disk_str) :false; 
            // 对每个partition回调检查其类型
            $check_ok = (false!=$disk_arr) ?(in_array(false, array_map("validPartition", $disk_arr)) ?false :true) :false;
            if (false!=$check_ok) {
                foreach ($disk_arr as $disk_child) { // 遍历disk数组下所有的partition数组，存到一个临时数组
                    $temp_disk_arr[] = array_combine(array("mounted", "capacity", "iused"), array_values(explode(__SOURCE_SPLIT_TAG4, $disk_child)));
                }
            }
            $this->m_disk = (false!=$check_ok) ?$temp_disk_arr :$this->m_disk; // 返回给成员变量 
            $Ret = $check_ok ?$this->m_disk :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getDisk return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取Process信息 
     * @return process数组 
     */
    function getProcess() {
        $process_str = $this->m_field[__SERVER_FIELD_PROCESS];
        if (empty($process_str)) {
            return true; // 允许客户端不传此项 
        } else { // 如传递须检查数据合法性 
            $process_arr = sizeof(explode(__SOURCE_SPLIT_TAG3, $process_str))==__SERVER_PROCESS_ITEMS_NUM ?explode(__SOURCE_SPLIT_TAG3, $process_str) :false;
            $check_ok = (false!=$process_arr && is_array($process_arr)) ?(in_array(false, array_map("validDigital", $process_arr)) ?false :true) :false;
            $this->m_process = (false!=$check_ok) ?array_combine($this->m_process, $process_arr) :$this->m_process;
            $Ret = $check_ok ?$this->m_process :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getProcess return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取network信息 
     * @return network=>interface二维数组 
     */
    function getNetwork() {
        $network_str = $this->m_field[__SERVER_FIELD_NETWORK];
        if (empty($network_str)) {
            return true; // 允许客户端不传此项 
        } else { // 如传递则检查数据合法性 
            // 劈开network为各interface明细数组
            $network_arr = is_array(explode(__SOURCE_SPLIT_TAG3, $network_str)) ?explode(__SOURCE_SPLIT_TAG3, $network_str) :false; 
            // 对每个interface回调检查其类型
            $check_ok = (false!=$network_arr) ?(in_array(false, array_map("validNetworkIface", $network_arr)) ?false :true) :false;
            if (false!=$check_ok) {
                foreach ($network_arr as $network_child) { // 遍历network数组下所有的interface数组，存到一个临时数组
                    $temp_network_arr[] = array_combine(array("ifname", "in", "out"),array_values(explode(__SOURCE_SPLIT_TAG4, $network_child)));
                }
            }
            $this->m_network = (false!=$check_ok) ?$temp_network_arr :$this->m_network; // 返回给成员变量 
            $Ret = $check_ok ?$this->m_network :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getNetwork return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取link信息 
     * @return link=>link二维数组 
     */
    function getLink() {
        $link_str = $this->m_field[__SERVER_FIELD_LINK];
        if (empty($link_str)) {
            return true; // 允许客户端不传此项 
        } else { // 如传递则检查数据合法性 
            // 劈开link为各连接明细数组
            $link_arr = is_array(explode(__SOURCE_SPLIT_TAG3, $link_str)) ?explode(__SOURCE_SPLIT_TAG3, $link_str) :false; 
            // 对每个link回调检查其类型
            $check_ok = (false!=$link_arr) ?(in_array(false, array_map("validLink", $link_arr)) ?false :true) :false;
            if (false!=$check_ok) {
                foreach ($link_arr as $link_child) { // 遍历link数组下所有的link数组，存到一个临时数组
                    $temp_link_arr[] = array_combine(array("sserver", "dserver", "flow"), array_values(explode(__SOURCE_SPLIT_TAG4, $link_child)));
                }
            }
            $this->m_link = (false!=$check_ok) ?$temp_link_arr :$this->m_link; // 返回给成员变量 
            $Ret = $check_ok ?$this->m_link :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getLink return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取service信息
     * @return service=>service二维数组 
     */
    function getService() {
        $service_str = $this->m_field[__SERVER_FIELD_SERVICE];
        if (empty($service_str)) {
            return true; // 允许客户端不传此项 
        } else { // 如传递则检查数据合法性
            SaveSysLog("[$this->m_module_name][$this->m_class_name][service_str not empty]",5);
            // 劈开service为各连接明细数组
            $service_arr = is_array(explode(__SOURCE_SPLIT_TAG3, $service_str)) ?explode(__SOURCE_SPLIT_TAG3, $service_str) :false; 
            SaveSysLog("[$this->m_module_name][$this->m_class_name][service_arr:".join(',',$service_arr)."]",5);
            // 对每个service回调检查其类型
            $check_ok = (false!=$service_arr) ?(in_array(false, array_map("validService", $service_arr)) ?false :true) :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getService][check_ok:$check_ok]",5);
            if (false!=$check_ok) {
                foreach ($service_arr as $service_child) { // 遍历service数组下所有的service数组，存到一个临时数组
                    $temp_service_arr[] = array_combine(array("name", "port", "status"), array_values(explode(__SOURCE_SPLIT_TAG4, $service_child)));
                }
            }
            $this->m_service = (false!=$check_ok) ?$temp_service_arr :$this->m_service; // 返回给成员变量 
            $Ret = $check_ok ?$this->m_service :false;
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getService return $Ret]",5);
            return ($Ret);
        }
    }

    /**
     * @brief 获取全部信息
     * @return 类对象
     */
    function getAllInfo() {
        $Ret = (false!==$this->getServerName() && false!==$this->getSummary() && false!==$this->getCpu() && false!==$this->getMem() && false!==$this->getSwap() 
            && false!==$this->getDisk() && false!==$this->getProcess() && false!==$this->getNetwork() && false!==$this->getLink() && false!=$this->getService()) ?$this :false;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo fail!]");
        } else {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo ok!]");
        }
        return ($Ret);
    }
}
?>
