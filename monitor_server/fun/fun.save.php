<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.save.php
  +----------------------------------------------------------------------+
  | Comment:保存监控信息
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */

/**
 *@brief 保存即时监控信息到MDB
 *@param $srv_info 服务器类型对象，可以是cls_mysql、cls_delivering、cls_daemon、cls_report、cls_generic、cls_serving、cls_madn类的对象
 *@param $mon_type 监控的类型，用来判断第一个参数的类型
 */
function saveMonitorInfo($srv_info,$mon_type) {
    global $module_name;
    dispersedlySaveInfo($srv_info,$mon_type);
    intensivelySaveInfo($srv_info,$mon_type);
}

/**
 *@brief 客户端上传信息分散存到即时信息表的各个列族(每个列族为一个监控项,for即时追踪一个监控项)
 *@param $srv_info 服务器类型对象
 *@param $mon_type 监控的类型，用来判断第一个参数的类型
 */
function dispersedlySaveInfo($srv_info,$mon_type) {
    global $module_name;
    $table=__MDB_TAB_SERVER;
    switch($mon_type) {
    case __MONITOR_TYPE_GENERIC:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                      |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)generic:                                            |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)summary_load|summary_uptime_day(此处省略若干列)       |  
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)1.75       | 127               (此处省略若干值)      .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="generic:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        list($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections)=array_values($srv_info->m_summary);
        list($cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle)=array_values($srv_info->m_cpu);
        list($mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free)=array_values($srv_info->m_mem);
        list($swap_total,$swap_used,$swap_free,$swap_inuse)=array_values($srv_info->m_swap);
        list($process_sum,$process_starting,$process_running,$process_sleeping,$proccess_stopped,$process_zombie,$process_waiting,$process_lock)=array_values($srv_info->m_process);
        $mutation_key_arr=array('summary_load','summary_uptime_day','summary_uptime_his','summary_tcp_connections',
            'cpu_use','cpu_nice','cpu_system','cpu_interrupt','cpu_idle',
            'mem_active','mem_inact','mem_wired','mem_cache','mem_buf','mem_free',
            'swap_total','swap_used','swap_free','swap_inuse',
            'process_sum','process_starting','process_running','process_sleeping','process_stopped','process_zombie','process_waiting','process_lock');
        $mutation_val_arr=array($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections,
            $cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle,
            $mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free,
            $swap_total,$swap_used,$swap_free,$swap_inuse,
            $process_sum,$process_starting,$process_running,$process_sleeping,$process_stopped,$process_zombie,$process_waiting,$process_lock);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //磁盘
        foreach((array)($srv_info->m_disk) as $disk_node){
            list($mounted,$capacity,$iused)=array_values($disk_node);
            $mutation_elements=array_merge($mutation_elements,array("disk-{$mounted}-capacity" => $capacity));
            $mutation_elements=array_merge($mutation_elements,array("disk-{$mounted}-iused"    => $iused));
        }
        //链接 
        foreach((array)($srv_info->m_link) as $link_node){
            list($sserver,$dserver,$flow)=array_values($link_node);
            //TODO: server信息的link客户端没有实现，暂不处理
        }
        //网络接口
        foreach($srv_info->m_network as $network_node){
            list($ifname,$in,$out)=array_values($network_node);
            if(!empty($ifname)){
                $mutation_elements=array_merge($mutation_elements,array("network-{$ifname}-in"  => $in));
                $mutation_elements=array_merge($mutation_elements,array("network-{$ifname}-out" => $out));
            }
        }
        //服务
        foreach($srv_info->m_service as $service_node){
            list($name,$port,$status)=array_values($service_node);
            if(!empty($name)){
                $mutation_elements=array_merge($mutation_elements,array("service-{$name}-port"   => $port));
                $mutation_elements=array_merge($mutation_elements,array("service-{$name}-status" => $status));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_MYSQL:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                             |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)mysql:                                                     |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)summary_uptime|summary_threads_created(此处省略若干列)       |  
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)    230400    |             17         (此处省略若干值) .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="mysql:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        list($summary_uptime,$summary_threads_created,$summary_slow_queries,$summary_questions,
            $summary_connections,$summary_cur_connections)=array_values($srv_info->m_summary);
        list($traffic_in,$traffic_out)=array_values($srv_info->m_traffic);
        list($statement_delete,$statement_insert,$statement_select,$statement_update)=array_values($srv_info->m_statement);
        $replication=$srv_info->m_replication;
        $slave_io_running=$srv_info->m_slave_io_running;
        $slave_sql_running=$srv_info->m_slave_sql_running;
        $seconds_behind_master=$srv_info->m_seconds_behind_master;
        $mutation_key_arr=array('summary_uptime','summary_threads_created','$summary_slow_queries','summary_questions',
            'summary_connections','summary_cur_connections','traffic_in','traffic_out',
            'statement_delete','statement_insert','statement_select','statement_update','replication',
            'slave_io_running','slave_sql_running','seconds_behind_master');
        $mutation_val_arr=array($summary_uptime,$summary_threads_created,$summary_slow_queries,
            $summary_questions,$summary_connections,$summary_cur_connections,$traffic_in,$traffic_out,
            $statement_delete,$statement_insert,$statement_select,$statement_update,$replication,
            $slave_io_running,$slave_sql_running,$seconds_behind_master);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //库讯息
        foreach((array)($srv_info->m_dbinfo) as $dbinfo_node){
            list($db_name,$table_sum,$maxsize_table_name,$maxsize_table_size)=array_values($dbinfo_node);
            if(!empty($db_name)){
                $mutation_elements=array_merge($mutation_elements,array("dbinfo-{$db_name}-table_sum"   => $table_sum));
                $mutation_elements=array_merge($mutation_elements,array("dbinfo-{$db_name}-maxsize_table_name" => $maxsize_table_name));
                $mutation_elements=array_merge($mutation_elements,array("dbinfo-{$db_name}-maxsize_table_size" => $maxsize_table_size));
            }
        }
        //表信息
        foreach((array)($srv_info->m_tableinfo) as $table_node){
            list($table_name,$db_name,$engine,$rows,$data_length,$index_length,$auto_increment,$update_time,$collation)=array_values($table_node);
            if(!empty($table_name)){
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-db_name" => $db_name));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-engine" => $engine));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-rows" => $rows));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-data_length" => $data_length));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-index_length" => $index_length));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-auto_increment" => $auto_increment));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-update_time" => $update_time));
                $mutation_elements=array_merge($mutation_elements,array("tableinfo-{$table_name}-collation" => $collation));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_SERVING:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                             |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)serving:                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)request|traffic|engine_status(此处省略若干列)                |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)   50  |1489834|  1              (此处省略若干值)         .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="serving:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array('request','traffic','engine_status');
        $mutation_val_arr=array($srv_info->m_request,$srv_info->m_traffic,$srv_info->m_enginestatus);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //adimage信息
        foreach((array)($srv_info->m_adimage) as $adimage_node){
            list($domain_id,$ad_pos_num,$ad_campaign_num,$delivering_cache_num,$pack_serialnum,$publish_role)=array_values((array)$adimage_node);
            if(!empty($pack_serialnum) && !empty($domain_id)){ //如果不传就不写入了
                $mutation_elements=array_merge($mutation_elements,array("adimage-{$domain_id}-ad_pos_num" => $ad_pos_num));
                $mutation_elements=array_merge($mutation_elements,array("adimage-{$domain_id}-ad_campaign_num" => $ad_campaign_num));
                $mutation_elements=array_merge($mutation_elements,array("adimage-{$domain_id}-delivering_cache_num" => $delivering_cache_num));
                $mutation_elements=array_merge($mutation_elements,array("adimage-{$domain_id}-pack_serialnum" => $pack_serialnum));
                $mutation_elements=array_merge($mutation_elements,array("adimage-{$domain_id}-publish_role" => $publish_role));
            }
        }
        //loginfo
        list($total_log_num,$upload_log_num,$file_name,$file_md5)=array_values($srv_info->m_loginfo);
        if(!is_null($file_name) && !is_null($file_md5)){ //如果不传就不写入了
            $mutation_elements=array_merge($mutation_elements,array("total_log_num"  => $total_log_num));
            $mutation_elements=array_merge($mutation_elements,array("upload_log_num" => $upload_log_num));
            $mutation_elements=array_merge($mutation_elements,array("file_name"      => $file_name)); 
            $mutation_elements=array_merge($mutation_elements,array("file_md5"       => $file_md5)); 
        } 
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_DAEMON:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)daemon:                                                          |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)webserver_status|daemon_status|login_status(此处省略若干列)        |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)       50       |      1      |      1     (此处省略若干值)     .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="daemon:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array('webserver_status','daemon_status','login_status','adserv_status','errorlog_status');
        $mutation_val_arr=array($srv_info->m_webserver_status,$srv_info->m_daemon_status,
            $srv_info->m_login_status,$srv_info->m_adserv_status,$srv_info->m_error_log_status);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_REPORT:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)report:                                                          |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)process_speed|wait_process_log_num                                 |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)     555     |      1489834                                     .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="report:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array('process_speed','wait_process_log_num');
        $mutation_val_arr=array($srv_info->m_process_speed,$srv_info->m_wait_process_log_num);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_MADN:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)madn:                                                            |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)url-{$urlName}-statuscode                                          |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)http三位整数状态码|监控url                                        .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="madn:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        // url状态
        foreach ((array)$srv_info->m_url_status as $urlName => $urlInfo) {
            SaveSysLog("[$module_name][urlName:$urlName][statusCode:{$urlInfo['statusCode']}][url:{$urlInfo['url']}]",4);
            if (!empty($urlName)) {
                $mutation_elements["url-{$urlName}-statuscode"] = "{$urlInfo['statusCode']}|{$urlInfo['url']}";
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table]".
                "[CF:madn][id:{$srv_info->m_server}][failed][error:".$e->getMessage()."]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_HADOOP:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)hadoop:                                                          |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)dfs.datanode.blockChecksumOp_avg_time, ...                         |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)int                                                               .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        $column_family="hadoop:";
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array('dfs.datanode.blockChecksumOp_avg_time','dfs.datanode.blockChecksumOp_num_ops',
            'dfs.datanode.blockReports_avg_time','dfs.datanode.blockReports_num_ops',
            'dfs.datanode.block_verification_failures','dfs.datanode.blocks_read','dfs.datanode.blocks_removed',
            'dfs.datanode.blocks_replicated','dfs.datanode.blocks_verified','dfs.datanode.blocks_written',
            'dfs.datanode.bytes_read','dfs.datanode.bytes_written','dfs.datanode.copyBlockOp_avg_time',
            'dfs.datanode.copyBlockOp_num_ops','dfs.datanode.heartBeats_avg_time','dfs.datanode.heartBeats_num_ops',
            'dfs.datanode.readBlockOp_avg_time','dfs.datanode.readBlockOp_num_ops',
            'dfs.datanode.readMetadataOp_avg_time','dfs.datanode.readMetadataOp_num_ops',
            'dfs.datanode.reads_from_local_client','dfs.datanode.reads_from_remote_client',
            'dfs.datanode.replaceBlockOp_avg_time','dfs.datanode.replaceBlockOp_num_ops',
            'dfs.datanode.writeBlockOp_avg_time','dfs.datanode.writeBlockOp_num_ops',
            'dfs.datanode.writes_from_local_client','dfs.datanode.writes_from_remote_client');
        $hInfo=$srv_info->m_hdfsMetric;
        $mutation_elements=array($hInfo['blockChecksumOp_avg_time'], $hInfo['blockChecksumOp_num_ops'],
            $hInfo['blockReports_avg_time'],$hInfo['blockReports_num_ops'],$hInfo['block_verification_failures'],
            $hInfo['blocks_read'],$hInfo['blocks_removed'],$hInfo['blocks_replicated'],$hInfo['blocks_verified'],
            $hInfo['blocks_written'],$hInfo['bytes_read'],$hInfo['bytes_written'],$hInfo['copyBlockOp_avg_time'],
            $hInfo['copyBlockOp_num_ops'],$hInfo['heartBeats_avg_time'],$hInfo['heartBeats_num_ops'],
            $hInfo['readBlockOp_avg_time'],$hInfo['readBlockOp_num_ops'],$hInfo['readMetadataOp_avg_time'],
            $hInfo['readMetadataOp_num_ops'],$hInfo['reads_from_local_client'],$hInfo['reads_from_remote_client'],
            $hInfo['replaceBlockOp_avg_time'],$hInfo['replaceBlockOp_num_ops'],$hInfo['writeBlockOp_avg_time'],
            $hInfo['writeBlockOp_num_ops'],$hInfo['writes_from_local_client'],$hInfo['writes_from_remote_client']);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        $rowkey = $srv_info->m_server;
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table]".
                "[CF:hadoop][id:{$srv_info->m_server}][failed][error:".$e->getMessage()."]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase dispersedlySaveInfo data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    }
}

/**
 *@brief 将客户端上传信息集中存储为即时信息表的一个列族(for提高分页效率，因为分页要遍历服务器，再一个一个CF去get效率不免低下,一个get越0.1秒！)
 *@param $srv_info 服务器类型对象
 *@param $mon_type 监控的类型，用来判断第一个参数的类型
 */
function intensivelySaveInfo($srv_info,$mon_type) {
    global $module_name;
    $table = __MDB_TAB_SERVER;
    $column_family = "info:";
    $rowkey = $srv_info->m_server;
    switch($mon_type) {
    case __MONITOR_TYPE_GENERIC:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                              |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                       |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)generic_summary_load|generic_summary_uptime_day(此处省略若干列)               |  
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)1.75                |127   (此处省略若干值)                                 .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        list($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections)=array_values($srv_info->m_summary);
        list($cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle)=array_values($srv_info->m_cpu);
        list($mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free)=array_values($srv_info->m_mem);
        list($swap_total,$swap_used,$swap_free,$swap_inuse)=array_values($srv_info->m_swap);
        list($process_sum,$process_starting,$process_running,$process_sleeping,$proccess_stopped,$process_zombie,$process_waiting,$process_lock)=array_values($srv_info->m_process);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_GENERIC.'summary_load',
            __PREFIX_COLUMN_GENERIC.'summary_uptime_day',
            __PREFIX_COLUMN_GENERIC.'summary_uptime_his',
            __PREFIX_COLUMN_GENERIC.'summary_tcp_connections',
            __PREFIX_COLUMN_GENERIC.'cpu_use',
            __PREFIX_COLUMN_GENERIC.'cpu_nice',
            __PREFIX_COLUMN_GENERIC.'cpu_system',
            __PREFIX_COLUMN_GENERIC.'cpu_interrupt',
            __PREFIX_COLUMN_GENERIC.'cpu_idle',
            __PREFIX_COLUMN_GENERIC.'mem_active',
            __PREFIX_COLUMN_GENERIC.'mem_inact',
            __PREFIX_COLUMN_GENERIC.'mem_wired',
            __PREFIX_COLUMN_GENERIC.'mem_cache',
            __PREFIX_COLUMN_GENERIC.'mem_buf',
            __PREFIX_COLUMN_GENERIC.'mem_free',
            __PREFIX_COLUMN_GENERIC.'swap_total',
            __PREFIX_COLUMN_GENERIC.'swap_used',
            __PREFIX_COLUMN_GENERIC.'swap_free',
            __PREFIX_COLUMN_GENERIC.'swap_inuse',
            __PREFIX_COLUMN_GENERIC.'process_sum',
            __PREFIX_COLUMN_GENERIC.'process_starting',
            __PREFIX_COLUMN_GENERIC.'process_running',
            __PREFIX_COLUMN_GENERIC.'process_sleeping',
            __PREFIX_COLUMN_GENERIC.'process_stopped',
            __PREFIX_COLUMN_GENERIC.'process_zombie',
            __PREFIX_COLUMN_GENERIC.'process_waiting',
            __PREFIX_COLUMN_GENERIC.'process_lock'
        );
        $mutation_val_arr=array($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections,
            $cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle,
            $mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free,
            $swap_total,$swap_used,$swap_free,$swap_inuse,
            $process_sum,$process_starting,$process_running,$process_sleeping,$process_stopped,$process_zombie,$process_waiting,$process_lock);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //磁盘
        foreach((array)($srv_info->m_disk) as $disk_node){
            list($mounted,$capacity,$iused)=array_values($disk_node);
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."disk-{$mounted}-capacity" => $capacity));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."disk-{$mounted}-iused"    => $iused));
        }
        //链接 
        foreach((array)($srv_info->m_link) as $link_node){
            list($sserver,$dserver,$flow)=array_values($link_node);
            //TODO: server信息的link客户端没有实现，暂不处理
        }
        //网络接口
        foreach($srv_info->m_network as $network_node){
            list($ifname,$in,$out)=array_values($network_node);
            if(!empty($ifname)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."network-{$ifname}-in"  => $in));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."network-{$ifname}-out" => $out));
            }
        }
        //服务
        foreach($srv_info->m_service as $service_node){
            list($name,$port,$status)=array_values($service_node);
            if(!empty($name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."service-{$name}-port" => $port));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."service-{$name}-status" => $status));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try {
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase  intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_MYSQL:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                                 |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                          |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)mysql_summary_uptime|mysql_summary_threads_created(此处省略若干列)               |  
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)22334               |17     (此处省略若干值)                                   .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        list($summary_uptime,$summary_threads_created,$summary_slow_queries,$summary_questions,
            $summary_connections,$summary_cur_connections)=array_values($srv_info->m_summary);
        list($traffic_in,$traffic_out)=array_values($srv_info->m_traffic);
        list($statement_delete,$statement_insert,$statement_select,$statement_update)=array_values($srv_info->m_statement);
        $replication=$srv_info->m_replication;
        $slave_io_running=$srv_info->m_slave_io_running;
        $slave_sql_running=$srv_info->m_slave_sql_running;
        $seconds_behind_master=$srv_info->m_seconds_behind_master;
        $mutation_key_arr=array(
            __PREFIX_COLUMN_MYSQL.'summary_uptime',
            __PREFIX_COLUMN_MYSQL.'summary_threads_created',
            __PREFIX_COLUMN_MYSQL.'summary_slow_queries',
            __PREFIX_COLUMN_MYSQL.'summary_questions',
            __PREFIX_COLUMN_MYSQL.'summary_connections',
            __PREFIX_COLUMN_MYSQL.'summary_cur_connections',
            __PREFIX_COLUMN_MYSQL.'traffic_in',
            __PREFIX_COLUMN_MYSQL.'traffic_out',
            __PREFIX_COLUMN_MYSQL.'statement_delete',
            __PREFIX_COLUMN_MYSQL.'statement_insert',
            __PREFIX_COLUMN_MYSQL.'statement_select',
            __PREFIX_COLUMN_MYSQL.'statement_update',
            __PREFIX_COLUMN_MYSQL.'replication',
            __PREFIX_COLUMN_MYSQL.'slave_io_running',
            __PREFIX_COLUMN_MYSQL.'slave_sql_running',
            __PREFIX_COLUMN_MYSQL.'seconds_behind_master'
        );
        $mutation_val_arr=array($summary_uptime,$summary_threads_created,$summary_slow_queries,
            $summary_questions,$summary_connections,$summary_cur_connections,$traffic_in,$traffic_out,
            $statement_delete,$statement_insert,$statement_select,$statement_update,$replication,
            $slave_io_running,$slave_sql_running,$seconds_behind_master);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //库讯息
        foreach((array)($srv_info->m_dbinfo) as $dbinfo_node){
            list($db_name,$table_sum,$maxsize_table_name,$maxsize_table_size)=array_values($dbinfo_node);
            if(!empty($db_name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-table_sum" => $table_sum));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-maxsize_table_name" => $maxsize_table_name));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-maxsize_table_size" => $maxsize_table_size));
            }
        }
        //表信息
        foreach((array)($srv_info->m_tableinfo) as $table_node){
            list($table_name,$db_name,$engine,$rows,$data_length,$index_length,$auto_increment,$update_time,$collation)=array_values($table_node);
            if(!empty($table_name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-db_name" => $db_name));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-engine" => $engine));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-rows" => $rows));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-data_length" => $data_length));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-index_length" => $index_length));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-auto_increment" => $auto_increment));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-update_time" => $update_time));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-collation" => $collation));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_SERVING:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                                  |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                           |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)serving_request|serving_traffic|serving_engine_status(此处省略若干列)             |
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)43             |1489834        |1  (此处省略若干值)                              .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        $mutation_key_arr=array(__PREFIX_COLUMN_SERVING.'request',__PREFIX_COLUMN_SERVING.'traffic',__PREFIX_COLUMN_SERVING.'engine_status');
        $mutation_val_arr=array($srv_info->m_request,$srv_info->m_traffic,$srv_info->m_enginestatus);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //adimage信息
        foreach((array)($srv_info->m_adimage) as $adimage_node){
            list($domain_id,$ad_pos_num,$ad_campaign_num,$delivering_cache_num,$pack_serialnum,$publish_role)=array_values((array)$adimage_node);
            if(!empty($pack_serialnum) && !empty($domain_id)){ //如果不传就不写入了
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-ad_pos_num" => $ad_pos_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-ad_campaign_num" => $ad_campaign_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-delivering_cache_num" => $delivering_cache_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-pack_serialnum" => $pack_serialnum));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-publish_role" => $publish_role));
            }
        }
        //loginfo
        list($total_log_num,$upload_log_num,$file_name,$file_md5)=array_values($srv_info->m_loginfo);
        if(!is_null($file_name) && !is_null($file_md5)){ //如果不传就不写入了
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."total_log_num"  => $total_log_num));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."upload_log_num" => $upload_log_num));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."file_name"      => $file_name)); 
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."file_md5"       => $file_md5)); 
        } 
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_DAEMON:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(表)__MDB_TAB_SERVER                                                                                |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(列族)info:                                                                                         |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(列)daemon_webserver_status|daemon_daemon_status|daemon_login_status(此处省略若干列)                |
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(值)51                     |0                   |0  (此处省略若干值)                              .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_DAEMON.'webserver_status',
            __PREFIX_COLUMN_DAEMON.'daemon_status',
            __PREFIX_COLUMN_DAEMON.'login_status',
            __PREFIX_COLUMN_DAEMON.'adserv_status',
            __PREFIX_COLUMN_DAEMON.'errorlog_status'
        );
        $mutation_val_arr=array($srv_info->m_webserver_status,$srv_info->m_daemon_status,
            $srv_info->m_login_status,$srv_info->m_adserv_status,$srv_info->m_error_log_status);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_REPORT:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                            |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)report_process_speed|report_wait_process_log_num                   |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)434                 |12434                                       .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_REPORT.'process_speed',
            __PREFIX_COLUMN_REPORT.'wait_process_log_num'
        );
        $mutation_val_arr=array($srv_info->m_process_speed,$srv_info->m_wait_process_log_num);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_MADN:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                   |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                            |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)madn_url-{$urlName}-statuscode                                     |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)http三位整数状态码|监控url                                        .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        // url状态
        foreach ((array)$srv_info->m_url_status as $urlName => $urlInfo) {
            SaveSysLog("[$module_name][urlName:$urlName][statusCode:{$urlInfo['statusCode']}][url:{$urlInfo['url']}]",4);
            if (!empty($urlName)) {
                $mutation_elements[__PREFIX_COLUMN_MADN."url-{$urlName}-statuscode"] = "{$urlInfo['statusCode']}|{$urlInfo['url']}";
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    case __MONITOR_TYPE_HADOOP:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER                                                  |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                           |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)hadoop_dfs.datanode.blockChecksumOp_avg_time,...
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)int                                                              .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockChecksumOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockChecksumOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockReports_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockReports_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.block_verification_failures',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_read',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_removed',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_replicated',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_verified',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_written',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.bytes_read',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.bytes_written',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.copyBlockOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.copyBlockOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.heartBeats_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.heartBeats_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readBlockOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readBlockOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readMetadataOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readMetadataOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.reads_from_local_client',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.reads_from_remote_client',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.replaceBlockOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.replaceBlockOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writeBlockOp_avg_time',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writeBlockOp_num_ops',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writes_from_local_client',
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writes_from_remote_client'
        );
        $hInfo=$srv_info->m_hdfsMetric;
        $mutation_val_arr=array($hInfo['blockChecksumOp_avg_time'],$hInfo['blockChecksumOp_num_ops'],
            $hInfo['blockReports_avg_time'],$hInfo['blockReports_num_ops'],
            $hInfo['block_verification_failures'],$hInfo['blocks_read'],$hInfo['blocks_removed'],
            $hInfo['blocks_replicated'],$hInfo['blocks_verified'],$hInfo['blocks_written'],
            $hInfo['bytes_read'],$hInfo['bytes_written'],$hInfo['copyBlockOp_avg_time'],
            $hInfo['copyBlockOp_num_ops'],$hInfo['heartBeats_avg_time'],$hInfo['heartBeats_num_ops'],
            $hInfo['readBlockOp_avg_time'],$hInfo['readBlockOp_num_ops'],$hInfo['readMetadataOp_avg_time'],
            $hInfo['readMetadataOp_num_ops'],$hInfo['reads_from_local_client'],
            $hInfo['reads_from_remote_client'],$hInfo['replaceBlockOp_avg_time'],
            $hInfo['replaceBlockOp_num_ops'],$hInfo['writeBlockOp_avg_time'],$hInfo['writeBlockOp_num_ops'],
            $hInfo['writes_from_local_client'],$hInfo['writes_from_remote_client']);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRow( $table, $rowkey, $mutations ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase intensivelySaveInfo data][table:$table][id:{$srv_info->m_server}][mon_type:$mon_type][ok]",4);
        break;
    }

}

/**
 *@brief 保存历史监控信息到MDB
 *@param $srv_info 服务器类型对象
 *@param $mon_type 监控的类型，用来判断第一个参数的类型
 */
function mdbSaveMonitorHistoryInfo($srv_info,$mon_type) {
    global $module_name;
    $table = __MDB_TAB_SERVER_HISTORY;
    $column_family = "info:";
    $rowkey = $srv_info->m_server;
    $ts = date('ymd', time()); //格式为固定6位数的日期，如110901 
    $ver_ts = time()-time()%300; //每个整5分的秒数时间戳 
    switch($mon_type) {
    case __MONITOR_TYPE_GENERIC:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                                                      |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                       |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)generic_summary_load{ts}|generic_summary_uptime_day{ts}(此处省略若干列)       |  
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)ver1=>1.75 ver2=>1.76...|ver1=>127 ver2=>127   (此处省略若干值)              .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        list($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections)=array_values($srv_info->m_summary);
        list($cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle)=array_values($srv_info->m_cpu);
        list($mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free)=array_values($srv_info->m_mem);
        list($swap_total,$swap_used,$swap_free,$swap_inuse)=array_values($srv_info->m_swap);
        list($process_sum,$process_starting,$process_running,$process_sleeping,$proccess_stopped,$process_zombie,$process_waiting,$process_lock)=array_values($srv_info->m_process);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_GENERIC.'summary_load'.$ts,
            __PREFIX_COLUMN_GENERIC.'summary_uptime_day'.$ts,
            __PREFIX_COLUMN_GENERIC.'summary_uptime_his'.$ts,
            __PREFIX_COLUMN_GENERIC.'summary_tcp_connections'.$ts,
            __PREFIX_COLUMN_GENERIC.'cpu_use'.$ts,
            __PREFIX_COLUMN_GENERIC.'cpu_nice'.$ts,
            __PREFIX_COLUMN_GENERIC.'cpu_system'.$ts,
            __PREFIX_COLUMN_GENERIC.'cpu_interrupt'.$ts,
            __PREFIX_COLUMN_GENERIC.'cpu_idle'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_active'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_inact'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_wired'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_cache'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_buf'.$ts,
            __PREFIX_COLUMN_GENERIC.'mem_free'.$ts,
            __PREFIX_COLUMN_GENERIC.'swap_total'.$ts,
            __PREFIX_COLUMN_GENERIC.'swap_used'.$ts,
            __PREFIX_COLUMN_GENERIC.'swap_free'.$ts,
            __PREFIX_COLUMN_GENERIC.'swap_inuse'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_sum'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_starting'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_running'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_sleeping'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_stopped'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_zombie'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_waiting'.$ts,
            __PREFIX_COLUMN_GENERIC.'process_lock'.$ts
        );
        $mutation_val_arr=array($summary_load,$summary_uptime_day,$summary_uptime_his,$summary_tcp_connections,
            $cpu_use,$cpu_nice,$cpu_system,$cpu_interrupt,$cpu_idle,
            $mem_active,$mem_inact,$mem_wired,$mem_cache,$mem_buf,$mem_free,
            $swap_total,$swap_used,$swap_free,$swap_inuse,
            $process_sum,$process_starting,$process_running,$process_sleeping,$process_stopped,$process_zombie,$process_waiting,$process_lock);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //磁盘
        foreach((array)($srv_info->m_disk) as $disk_node){
            list($mounted,$capacity,$iused)=array_values($disk_node);
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."disk-{$mounted}-capacity".$ts => $capacity));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."disk-{$mounted}-iused".$ts    => $iused));
        }
        //链接 
        foreach((array)($srv_info->m_link) as $link_node){
            list($sserver,$dserver,$flow)=array_values($link_node);
            //TODO: server信息的link客户端没有实现，暂不处理
        }
        //网络接口
        foreach($srv_info->m_network as $network_node){
            list($ifname,$in,$out)=array_values($network_node);
            if(!empty($ifname)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."network-{$ifname}-in".$ts  => $in));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."network-{$ifname}-out".$ts => $out));
            }
        }
        //服务
        foreach($srv_info->m_service as $service_node){
            list($name,$port,$status)=array_values($service_node);
            if(!empty($name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."service-{$name}-port".$ts => $port));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_GENERIC."service-{$name}-status".$ts => $status));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try {
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_MYSQL:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                                                         |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                          |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)mysql_summary_uptime{ts}|mysql_summary_threads_created{ts}(此处省略若干列)       |  
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)ver1=>230400 ver2=>22334| ver1=>15 ver2=>17     (此处省略若干值)                .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        list($summary_uptime,$summary_threads_created,$summary_slow_queries,$summary_questions,
            $summary_connections,$summary_cur_connections)=array_values($srv_info->m_summary);
        list($traffic_in,$traffic_out)=array_values($srv_info->m_traffic);
        list($statement_delete,$statement_insert,$statement_select,$statement_update)=array_values($srv_info->m_statement);
        $replication=$srv_info->m_replication;
        $slave_io_running=$srv_info->m_slave_io_running;
        $slave_sql_running=$srv_info->m_slave_sql_running;
        $seconds_behind_master=$srv_info->m_seconds_behind_master;
        $mutation_key_arr=array(
            __PREFIX_COLUMN_MYSQL.'summary_uptime'.$ts,
            __PREFIX_COLUMN_MYSQL.'summary_threads_created'.$ts,
            __PREFIX_COLUMN_MYSQL.'summary_slow_queries'.$ts,
            __PREFIX_COLUMN_MYSQL.'summary_questions'.$ts,
            __PREFIX_COLUMN_MYSQL.'summary_connections'.$ts,
            __PREFIX_COLUMN_MYSQL.'summary_cur_connections'.$ts,
            __PREFIX_COLUMN_MYSQL.'traffic_in'.$ts,
            __PREFIX_COLUMN_MYSQL.'traffic_out'.$ts,
            __PREFIX_COLUMN_MYSQL.'statement_delete'.$ts,
            __PREFIX_COLUMN_MYSQL.'statement_insert'.$ts,
            __PREFIX_COLUMN_MYSQL.'statement_select'.$ts,
            __PREFIX_COLUMN_MYSQL.'statement_update'.$ts,
            __PREFIX_COLUMN_MYSQL.'replication'.$ts,
            __PREFIX_COLUMN_MYSQL.'slave_io_running'.$ts,
            __PREFIX_COLUMN_MYSQL.'slave_sql_running'.$ts,
            __PREFIX_COLUMN_MYSQL.'seconds_behind_master'.$ts
        );
        $mutation_val_arr=array($summary_uptime,$summary_threads_created,$summary_slow_queries,
            $summary_questions,$summary_connections,$summary_cur_connections,$traffic_in,$traffic_out,
            $statement_delete,$statement_insert,$statement_select,$statement_update,$replication,
            $slave_io_running,$slave_sql_running,$seconds_behind_master);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //库讯息
        foreach((array)($srv_info->m_dbinfo) as $dbinfo_node){
            list($db_name,$table_sum,$maxsize_table_name,$maxsize_table_size)=array_values($dbinfo_node);
            if(!empty($db_name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-table_sum".$ts => $table_sum));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-maxsize_table_name".$ts => $maxsize_table_name));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."dbinfo-{$db_name}-maxsize_table_size".$ts => $maxsize_table_size));
            }
        }
        //表信息
        foreach((array)($srv_info->m_tableinfo) as $table_node){
            list($table_name,$db_name,$engine,$rows,$data_length,$index_length,$auto_increment,$update_time,$collation)=array_values($table_node);
            if(!empty($table_name)){
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-db_name".$ts => $db_name));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-engine".$ts => $engine));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-rows".$ts => $rows));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-data_length".$ts => $data_length));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-index_length".$ts => $index_length));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-auto_increment".$ts => $auto_increment));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-update_time".$ts => $update_time));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_MYSQL."tableinfo-{$table_name}-collation".$ts => $collation));
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_SERVING:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                                                          |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                                           |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)serving_request{ts}|serving_traffic{ts}|serving_engine_status{ts}(此处省略若干列) |
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)ver1=>50 ver2=>43  |ver1=>1489834      |ver1=>1 ver2=>1  (此处省略若干值)        .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array(__PREFIX_COLUMN_SERVING.'request'.$ts,__PREFIX_COLUMN_SERVING.'traffic'.$ts,__PREFIX_COLUMN_SERVING.'engine_status'.$ts);
        $mutation_val_arr=array($srv_info->m_request,$srv_info->m_traffic,$srv_info->m_enginestatus);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        //adimage信息
        foreach((array)($srv_info->m_adimage) as $adimage_node){
            list($domain_id,$ad_pos_num,$ad_campaign_num,$delivering_cache_num,$pack_serialnum,$publish_role)=array_values((array)$adimage_node);
            if(!empty($pack_serialnum) && !empty($domain_id)){ //如果不传就不写入了
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-ad_pos_num".$ts => $ad_pos_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-ad_campaign_num".$ts => $ad_campaign_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-delivering_cache_num".$ts => $delivering_cache_num));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-pack_serialnum".$ts => $pack_serialnum));
                $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."adimage-{$domain_id}-publish_role".$ts => $publish_role));
            }
        }
        //loginfo
        list($total_log_num,$upload_log_num,$file_name,$file_md5)=array_values($srv_info->m_loginfo);
        if(!is_null($file_name) && !is_null($file_md5)){ //如果不传就不写入了
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."total_log_num".$ts  => $total_log_num));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."upload_log_num".$ts => $upload_log_num));
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."file_name".$ts      => $file_name)); 
            $mutation_elements=array_merge($mutation_elements,array(__PREFIX_COLUMN_SERVING."file_md5".$ts       => $file_md5)); 
        } 
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_DAEMON:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(表)__MDB_TAB_SERVER_HISTORY                                                                        |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(列族)info:                                                                                         |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(列)daemon_webserver_status{ts}|daemon_daemon_status{ts}|daemon_login_status{ts}(此处省略若干列)    |
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |(值)ver1=>50 ver2=>51          |ver1=>1 ver2=>0         |ver1=>1 ver2=>0  (此处省略若干值)        .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_DAEMON.'webserver_status'.$ts,
            __PREFIX_COLUMN_DAEMON.'daemon_status'.$ts,
            __PREFIX_COLUMN_DAEMON.'login_status'.$ts,
            __PREFIX_COLUMN_DAEMON.'adserv_status'.$ts,
            __PREFIX_COLUMN_DAEMON.'errorlog_status'.$ts
        );
        $mutation_val_arr=array($srv_info->m_webserver_status,$srv_info->m_daemon_status,
            $srv_info->m_login_status,$srv_info->m_adserv_status,$srv_info->m_error_log_status);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_REPORT:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                                           |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                            |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)report_process_speed{ts}|report_wait_process_log_num{ts}           |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)ver1=>555 ver2=>434     |ver1=>14898 ver2=>12434                  .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_REPORT.'process_speed'.$ts,
            __PREFIX_COLUMN_REPORT.'wait_process_log_num'.$ts
        );
        $mutation_val_arr=array($srv_info->m_process_speed,$srv_info->m_wait_process_log_num);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_MADN:
        /**
         * ,'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                                           |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                                            |
         * |'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)madn_url-{$urlName}-statuscode{ts}                                 |
         * ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)http三位整数状态码|监控url                                        .....rowkey:<服务器id>
         * `''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        // url状态
        foreach ((array)$srv_info->m_url_status as $urlName => $urlInfo) {
            SaveSysLog("[$module_name][urlName:$urlName][statusCode:{$urlInfo['statusCode']}][url:{$urlInfo['url']}]",4);
            if (!empty($urlName)) {
                $mutation_elements[__PREFIX_COLUMN_MADN."url-{$urlName}-statuscode"] = "{$urlInfo['statusCode']}|{$urlInfo['url']}";
            }
        }
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    case __MONITOR_TYPE_HADOOP:
        /**
         * ,''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (表)__MDB_TAB_SERVER_HISTORY                      |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列族)info:                                       |
         * |''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (列)dfs.datanode.blockChecksumOp_avg_time{ts}     |
         * '''''''''''''''''''''''''''''''''''''''''''''''''''''|
         * |  (值)ver1=>1 ver2=>2                             .....rowkey:<服务器id>
         * `'''''''''''''''''''''''''''''''''''''''''''''''''''''
         */
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}]",4);
        $mutation_key_arr=array(
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockChecksumOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockChecksumOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockReports_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blockReports_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.block_verification_failures'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_read'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_removed'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_replicated'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_verified'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.blocks_written'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.bytes_read'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.bytes_written'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.copyBlockOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.copyBlockOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.heartBeats_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.heartBeats_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readBlockOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readBlockOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readMetadataOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.readMetadataOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.reads_from_local_client'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.reads_from_remote_client'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.replaceBlockOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.replaceBlockOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writeBlockOp_avg_time'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writeBlockOp_num_ops'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writes_from_local_client'.$ts,
            __PREFIX_COLUMN_HADOOP.'dfs.datanode.writes_from_remote_client'.$ts
        );
        $hInfo=$srv_info->m_hdfsMetric;
        $mutation_val_arr=array($hInfo['blockChecksumOp_avg_time'],$hInfo['blockChecksumOp_num_ops'],
            $hInfo['blockReports_avg_time'],$hInfo['blockReports_num_ops'],
            $hInfo['block_verification_failures'],$hInfo['blocks_read'],$hInfo['blocks_removed'],
            $hInfo['blocks_replicated'],$hInfo['blocks_verified'],$hInfo['blocks_written'],
            $hInfo['bytes_read'],$hInfo['bytes_written'],$hInfo['copyBlockOp_avg_time'],
            $hInfo['copyBlockOp_num_ops'],$hInfo['heartBeats_avg_time'],$hInfo['heartBeats_num_ops'],
            $hInfo['readBlockOp_avg_time'],$hInfo['readBlockOp_num_ops'],$hInfo['readMetadataOp_avg_time'],
            $hInfo['readMetadataOp_num_ops'],$hInfo['reads_from_local_client'],
            $hInfo['reads_from_remote_client'],$hInfo['replaceBlockOp_avg_time'],
            $hInfo['replaceBlockOp_num_ops'],$hInfo['writeBlockOp_avg_time'],$hInfo['writeBlockOp_num_ops'],
            $hInfo['writes_from_local_client'],$hInfo['writes_from_remote_client']);
        $mutation_elements=array_combine($mutation_key_arr,$mutation_val_arr);
        foreach($mutation_elements as $column => $value){
            $mutations[]=new Mutation(array('column'=>"{$column_family}{$column}",'value'=>$value));
        }
        try{
            $GLOBALS['mdb_client']->mutateRowTs( $table, $rowkey, $mutations, $ver_ts ); 
        } catch (Exception $e) {
            SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][failed]",4);
            doExit();
        }
        SaveSysLog("[$module_name][write hbase history data][table:$table][id:{$srv_info->m_server}][ok]",4);
        break;
    }
}
?>
