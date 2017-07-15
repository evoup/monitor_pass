<?php
/*
  +----------------------------------------------------------------------+
  | Name:
  +----------------------------------------------------------------------+
  | Comment:mysql监控信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-03 15:51:33
  +----------------------------------------------------------------------+
 */
class clsMysql{
    var $m_module_name="";
    var $m_class_name=__CLASS__; //类名 
    var $m_client_message=""; //post来的消息 
    var $m_server=""; //被监控的server名字 
    var $m_field=array(); //以下各成员变量的组成的数组 
    var $m_summary=array("uptime","threads_created","slow_queries","questions","connections","cur_connections");
    var $m_traffic;
    var $m_statement;
    var $m_replication;
    var $m_dbinfo;
    var $m_tableinfo;
    var $m_slave_io_running;
    var $m_slave_sql_running;
    var $m_seconds_behind_master;

    /**
     * @brief 构造
     * @return 
     */
    function __construct($client_message) {
        $this->clsMysql($client_message);
    }

    /**
     * @brief 构造
     * @return 
     */
    function clsMysql($client_message) {
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
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__MYSQL_FIELD_SUMMARY],3); 
        $Ret=is_string($split_array[1])?$split_array[1]:false;
        $this->m_server=$Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取Db的概要数据
     * @return m_summary类型数组 
     */
    function getSummary() {
        $temp_array=$this->getFirestFieldArray();
        $summary_str=$temp_array[2];
        $Ret=sizeof(explode(__SOURCE_SPLIT_TAG3,$summary_str))==__MYSQL_SUMMARY_FIELDS_NUM?explode(__SOURCE_SPLIT_TAG3,$summary_str):false;
        $this->m_summary=(false!=$Ret && is_array($Ret))?array_combine($this->m_summary,$Ret):$this->m_summary;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getSummary return:$Ret]",5);
        return ($this->m_summary);
    }

    /**
     * @brief 对summary field去掉flag:serverid:前缀
     * @return  
     */
    function getFirestFieldArray() {
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__MYSQL_FIELD_SUMMARY],3); 
        return ($split_array);
    }

    /**
     * @brief 获取traffic信息 
     * @return traffic类型数组array(in,out)，返回true
     */
    function getTraffic() {
        $traffic_str=$this->m_field[__MYSQL_FIELD_TRAFFIC];
        list($traffic_in,$traffic_out)=explode(__SOURCE_SPLIT_TAG3,$traffic_str);
        $this->m_traffic=array(intval($traffic_in),intval($traffic_out));
        return true; //转为数字后可直接返回true
    }

    /**
     * @brief 获取statement信息 
     * @return statement类型数组array(delete,insert,select,update) ,返回true
     */
    function getStatement() {
        $statement_str=$this->m_field[__MYSQL_FIELD_STATEMENT];
        list($statement_delete,$statement_insert,$statement_select,$statement_update)=explode(__SOURCE_SPLIT_TAG3,$statement_str);
        $this->m_statement=array(intval($statement_delete),intval($statement_insert),intval($statement_select),intval($statement_update));
        return true; //转为数字后可直接返回true 
    }

    /**
     *@brief 获取Replication信息
     *@return true
     */
    function getReplication() {
        $replication_str=$this->m_field[__MYSQL_FIELD_REPLICATION];    
        $this->m_replication=$replication_str=="ON"?"1":"0"; //转为直接可以存库的0/1格式 
        return true; //判断后可直接返回true 
    }

    /**
     *@brief 获取库信息
     *@return true
     */
    function getDbInfo() {
        $mysql_info_str=$this->m_field[__MYSQL_FIELD_DBINFO];
        foreach ((array)explode(__SOURCE_SPLIT_TAG3,$mysql_info_str) as $db_node) {
            list($dbname,$table_sum,$maxsizetablename,$maxsizetablesize)=explode(__SOURCE_SPLIT_TAG4,$db_node);
            if (!empty($dbname)) {
                $this->m_dbinfo[]=array($dbname,intval($table_sum),$maxsizetablename,intval($maxsizetablename));
            }
        }
        return true;
    }

    /**
     *@brief 获取表信息
     *@return true
     */
    function getTableInfo() {
        $table_info_str=$this->m_field[__MYSQL_FIELD_TABLEINFO];
        foreach ((array)explode(__SOURCE_SPLIT_TAG3,$table_info_str) as $table_node) {
            list($tablename,$dbname,$engine,$rows,$data_length,$index_length,$auto_increment,
                $update_time,$collation)=explode(__SOURCE_SPLIT_TAG4,$table_node);
            if (!empty($tablename)) {
                $this->m_tableinfo[]=array($tablename,$dbname,$engine,intval($rows),intval($data_length),
                    intval($index_length),intval($auto_increment),$update_time,$collation);
            }
        }
        return true;
    }

    /**
     *@brief 获取SlaveIORunning信息
     */
    function getSlaveIORunning() {
        $this->m_slave_io_running=strtoupper($this->m_field[__MYSQL_FIELD_SLAVE_IO_RUNNING])=="YES" ? 1 : 0;
        return true; //判断后可直接返回true 
    }

    /**
     *@brief 获取SlaveSQLRunning信息
     */
    function getSlaveSQLRunning() {
        $this->m_slave_sql_running=strtoupper($this->m_field[__MYSQL_FIELD_SLAVE_SQL_RUNNING])=="YES" ? 1 : 0;
        return true;
    }

    /**
     *@brief 获取Slave的second behind master信息
     */
    function getSecondsBehindMaster() {
        $this->m_seconds_behind_master=$this->m_field[__MYSQL_FIELD_SECONDS_BEHIND_MASTER];
        return true;
    }

    /**
     * @brief 获取全部信息
     * @return 类对象
     */
    function getAllInfo() {
        $Ret=(false!==$this->getServerName() && false!==$this->getSummary() && false!==$this->getTraffic() 
            && false!==$this->getStatement() && false!==$this->getReplication() && false!==$this->getDbInfo()
            && false!==$this->getTableInfo() && false!==$this->getSlaveIORunning() &&
            false!==$this->getSlaveSQLRunning() && false!==$this->getSecondsBehindMaster()
        )?$this:false;
        if (!$Ret) {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo fail!]");
        } else {
            SaveSysLog("[$this->m_module_name][$this->m_class_name][getAllInfo ok!]");
        }
        return ($Ret);
    }
}
?>
