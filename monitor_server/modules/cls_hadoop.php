<?php
/*
  +----------------------------------------------------------------------+
  | Name:cls_hadoop.php
  +----------------------------------------------------------------------+
  | Comment:hadoop信息类
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:2012年11月 2日 星期五 10时51分47秒 CST
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-02 18:32:08
  +----------------------------------------------------------------------+
 */
class clsHadoop{
    var $m_server=""; //服务器名字 
    var $m_client_message; //post来的消息 
    var $m_field=array(); //以下各成员变量的组成的数组
    var $m_module_name="";
    var $m_class_name="";
    /*
     *var $m_dn_blockChecksumOp_avg_time; //datanode块平均校验时间
     *var $m_dn_blockChecksumOp_num_ops; //datanode块检验次数
     *var $m_dn_blockReports_avg_time; //datanode块报告平均时间
     *var $m_dn_blockReports_num_ops; //datanode块报告次数  
     *var $m_dn_block_verification_failures; //datanode块 
     *var $m_dn_blocks_read; //datanode从硬盘读块总次数 
     *var $m_dn_blocks_removed; //datanode删除块数目
     *var $m_dn_blocks_replicated; //datanode块复制总次数 
     *var $m_dn_blocks_verified; //datanode块验证总次数 
     *var $m_dn_blocks_written; //datanode向硬盘写块总次数
     *var $m_dn_bytes_read; //datanode读出总字节包含crc验证文件字节数 
     *var $m_dn_bytes_written; //datanode写入总字节数
     *var $m_dn_copyBlockOp_avg_time; //datanode复制块平均时间
     *var $m_dn_copyBlockOp_num_ops; //datanode复制块次数 
     *var $m_dn_heartBeats_avg_time; //datanode向namenode汇报平均时间 
     *var $m_dn_heartBeats_num_ops; //datanode向namenode汇报总次数
     *var $m_dn_readBlockOp_avg_time; //datanode读块平均时间（单位ms）
     *var $m_dn_readBlockOp_num_ops; //datanode读块总次数
     *var $m_dn_readMetadataOp_avg_time; //datanode读取metadata平均时间 
     *var $m_dn_readMetadataOp_num_ops; //datanode读取metadata次数 
     *var $m_dn_reads_from_local_client; //datanode从本地读入块次数
     *var $m_dn_reads_from_remote_client; //datanode从远程读入块次数
     *var $m_dn_replaceBlockOp_avg_time; //datanode替换块平均时间
     *var $m_dn_replaceBlockOp_num_ops; //datanode替换块平均时间 
     *var $m_dn_writeBlockOp_avg_time; //datanode写块平均时间
     *var $m_dn_writeBlockOp_num_ops; //datanode写块总次数
     *var $m_dn_writes_from_local_client; //datanode写本地次数
     *var $m_dn_writes_from_remote_client; //datanode写远程次数 
     */
    var $m_hdfsMetric; //hdfs所有指标 

    /**
     * @brief php5构造 
     * @return 
     */
    function __construct($client_message) {
        $this->clsHadoop($client_message);
    }

    /**
     * @brief php4构造 
     * @return 
     */
    function clsHadoop($client_message) {
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
    function getServerName() {
        //截取前3段，后面也包含:不可以截，是uptime_his的数据 
        $split_array=explode(__SOURCE_SPLIT_TAG1,$this->m_field[__SERVER_FIELD_SUMMARY],3); 
        $Ret=is_string($split_array[1])?$split_array[1]:false;
        $this->m_server=$Ret;
        SaveSysLog("[$this->m_module_name][$this->m_class_name][getServerName return:$Ret]",5);
        return ($Ret);
    }

    /**
     * @brief 获取hdfs度量数据
     * @return 
     */
    function getDfsMetrics() {
        list($blockChecksumOp_avg_time,$blockChecksumOp_num_ops,
            $blockReports_avg_time,$blockReports_num_ops,
            $block_verification_failures,$blocks_read,$blocks_removed,
            $blocks_replicated,$blocks_verified,$blocks_written,
            $bytes_read,$bytes_written,$copyBlockOp_avg_time,
            $copyBlockOp_num_ops,$heartBeats_avg_time,$heartBeats_num_ops,
            $readBlockOp_avg_time,$readBlockOp_num_ops,$readMetadataOp_avg_time,
            $readMetadataOp_num_ops,$reads_from_local_client,
            $reads_from_remote_client,$replaceBlockOp_avg_time,
            $replaceBlockOp_num_ops,$writeBlockOp_avg_time,
            $writeBlockOp_num_ops,$writes_from_local_client,
            $writes_from_remote_client)=$this->m_field;
        $blockChecksumOp_avg_time=end(explode(':',$blockChecksumOp_avg_time));
        $this->m_hdfsMetric=array(
            'blockChecksumOp_avg_time'=>$blockChecksumOp_avg_time,
            'blockChecksumOp_num_ops'=>$blockChecksumOp_num_ops,
            'blockChecksumOp_avg_time'=>$blockReports_avg_time,
            'blockReports_num_ops'=>$blockReports_num_ops,
            'block_verification_failures'=>$block_verification_failures,
            'blocks_read'=>$blocks_read,
            'blocks_removed'=>$blocks_removed,
            'blocks_replicated'=>$blocks_replicated,
            'blocks_verified'=>$blocks_verified,
            'blocks_written'=>$blocks_written,
            'bytes_read'=>$bytes_read,
            'bytes_written'=>$bytes_written,
            'copyBlockOp_avg_time'=>$copyBlockOp_avg_time,
            'copyBlockOp_num_ops'=>$copyBlockOp_num_ops,
            'heartBeats_avg_time'=>$heartBeats_avg_time,
            'heartBeats_num_ops'=>$heartBeats_num_ops,
            'readBlockOp_avg_time'=>$readBlockOp_avg_time,
            'readBlockOp_num_ops'=>$readBlockOp_num_ops,
            'readMetadataOp_avg_time'=>$readMetadataOp_avg_time,
            'readMetadataOp_num_ops'=>$readMetadataOp_num_ops0,
            'reads_from_local_client'=>$reads_from_local_client,
            'reads_from_remote_client'=>$reads_from_remote_client,
            'replaceBlockOp_avg_time'=>$replaceBlockOp_avg_time,
            'replaceBlockOp_num_ops'=>$replaceBlockOp_num_ops,
            'writeBlockOp_avg_time'=>$writeBlockOp_avg_time,
            'writeBlockOp_num_ops'=>$writeBlockOp_num_ops,
            'writes_from_local_client'=>$writes_from_local_client,
            'writes_from_remote_client'=>$writes_from_remote_client
        );
        $hasValue=false;
        foreach ($this->m_hdfsMetric as $metric=>$value) { // 客户端没有收集数据则作废！ 
            strlen($value)!=0 && $hasValue=1;  
        }
        return $hasValue?$this->m_hdfsMetric:false;
    }

    /**
     * @brief 获取全部信息 
     * @return 
     */
    function getAllInfo() {
        $Ret=(false!==$this->getServerName() && false!==$this->getDfsMetrics())?true:false;
        return ($Ret);
    }
}
?>
