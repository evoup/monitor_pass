<?php
/*
  +----------------------------------------------------------------------------+
  | Name: generate_hadoop_info.m
  +----------------------------------------------------------------------------+
  | Comment: 生成hadoop信息
  +----------------------------------------------------------------------------+
  | Author: YinJia
  +----------------------------------------------------------------------------+
  | Create: 2012-10-31 14:58:29
  +----------------------------------------------------------------------------+
  | Last Modified: 2012-11-01 13:55:48
  +----------------------------------------------------------------------------+
 */
$module_name='hadoop_info';

$hadoop_str=__FLAG_HADOOP.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;
DebugInfo(3,$debug_level,"[$process_name][$module_name]::[deal_hadoop_dfs_log_file:$deal_hadoop_dfs_log_file]");

//hdfs部分
$hdfs_log_status_file=__PROC_ROOT.'/'.__STATUS_SUBPATH.'/hdfs_log.status';

/*** read status file***/
if ($fp=@fopen($hdfs_log_status_file,"rb")) {
    flock($fp,LOCK_SH);
    $last_status=trim(fread($fp,filesize($hdfs_log_status_file)));
    list($last_hdfs_ustamp,$last_hdfs_offset,$last_hdfs_inode)=explode('|',$last_status);
    fclose($fp);
    $debug_data="[$process_name]::[last_time:".date("Y-m-d H:i:s",
        $last_ustamp)."]-[last_offset:$last_offset]-[last_inode:$last_inode]";
    DebugInfo(1,$debug_level,$debug_data);
}

if ( file_exists($deal_hdfs_log_file) && 
    false!==($readlog_info=readInfo($last_hdfs_ustamp,$last_hdfs_offset,
        $last_hdfs_inode,$now,$_hdfs_log_name,$_hdfs_log_path)) 
) {
    DebugInfo(2,$debug_level,"[$process_name][$module_name]::[log:$deal_hdfs_log_file]-[read_it]");
    $read_log_file=$readlog_info['file'];
    $read_stat=$readlog_info['read'];
    $read_offset=$readlog_info['offset'];
    $read_inode=fileinode($read_log_file);

    DebugInfo(1,$debug_level,"[$process_name]::[file:$read_log_file]-[read_inode:$read_inode]"
        ."-[start_to_read:".(($read_stat===true)?$read_offset:'skip')."]");

    if ( $read_stat && file_exists($read_log_file) && $fp0=@fopen($read_log_file,"rb") ) {
        fseek($fp0,$read_offset);
        while ( !feof($fp0) ) {
            $log_content=trim( fgets($fp0,102400) ); //log line maybe ling,safelly use 102400 
            $dfsLogInfo=explode(" ",$log_content);
            switch ( $dfsLogInfo[0] ) {
            case('dfs.datanode:'):
                array_shift($dfsLogInfo);
                foreach ( $dfsLogInfo as $metricInfoArr ) {
                    list($metric,$value)=explode('=',$metricInfoArr);
                    $dfsLogData[$metric]=str_replace(',','',$value);
                }
                break;
            }
            $cur_offset=ftell($fp0);
            $read_size=$cur_offset-$read_offset;
            if ($read_size>$_max_size) {
                break;
            }
        }
        fclose($fp0);
    } else {
        $cur_offset=$last_offset;
        DebugInfo(1,$debug_level,"[$module_name]::[inode:$read_inode]-[offset:$cur_offset]-[not_read_this_time]");
    }
} else {
    DebugInfo(2,$debug_level,"[$process_name][$module_name]::[log:$deal_hdfs_log_file]-[error]");
}

//update status
$tmp_status="$now|$cur_offset|$read_inode";
if ($fp=@fopen($hdfs_log_status_file,"wb")) {
    fputs($fp,$tmp_status);
    ftruncate($fp,strlen($tmp_status));
    fclose($fp);
}

$hadoop_str.=$dfsLogData['blockChecksumOp_avg_time'].__SOURCE_SPLIT_TAG2.$dfsLogData['blockChecksumOp_num_ops']
    .__SOURCE_SPLIT_TAG2.$dfsLogData['blockReports_avg_time'].__SOURCE_SPLIT_TAG2.
    $dfsLogData['blockReports_num_ops'].__SOURCE_SPLIT_TAG2.$dfsLogData['block_verification_failures'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['blocks_read'].__SOURCE_SPLIT_TAG2.$dfsLogData['blocks_removed'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['blocks_replicated'].__SOURCE_SPLIT_TAG2.$dfsLogData['blocks_verified'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['blocks_written'].__SOURCE_SPLIT_TAG2.$dfsLogData['bytes_read'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['bytes_written'].__SOURCE_SPLIT_TAG2.$dfsLogData['copyBlockOp_avg_time'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['copyBlockOp_num_ops'].__SOURCE_SPLIT_TAG2.$dfsLogData['heartBeats_avg_time'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['heartBeats_num_ops'].__SOURCE_SPLIT_TAG2.
    $dfsLogData['readBlockOp_avg_time'].__SOURCE_SPLIT_TAG2.$dfsLogData['readBlockOp_num_ops'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['readMetadataOp_avg_time'].__SOURCE_SPLIT_TAG2.
    $dfsLogData['readMetadataOp_num_ops'].__SOURCE_SPLIT_TAG2.$dfsLogData['reads_from_local_client'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['reads_from_remote_client'].__SOURCE_SPLIT_TAG2.
    $dfsLogData['replaceBlockOp_avg_time'].__SOURCE_SPLIT_TAG2.$dfsLogData['replaceBlockOp_num_ops'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['writeBlockOp_avg_time'].__SOURCE_SPLIT_TAG2.
    $dfsLogData['writeBlockOp_num_ops'].__SOURCE_SPLIT_TAG2.$dfsLogData['writes_from_local_client'].
    __SOURCE_SPLIT_TAG2.$dfsLogData['writes_from_remote_client'];
    
if (!empty($hadoop_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$hadoop_str;
    else $upload_str=$hadoop_str;
    unset($hadoop_str,$dfsLogData);
}

?>
