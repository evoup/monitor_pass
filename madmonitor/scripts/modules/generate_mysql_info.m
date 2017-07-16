<?php
/*
  +----------------------------------------------------------------------+
  | Name: modules/generate_mysql_info.m                                  |
  +----------------------------------------------------------------------+
  | Comment: 收集mysql db信息                                            |
  +----------------------------------------------------------------------+
  | Author: Odin                                                         |
  +----------------------------------------------------------------------+
  | Create: 2009-09-18 10:35:15                                          |
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-11-14 14:29:03                                   |
  +----------------------------------------------------------------------+
*/
$module_name='mysql_info';

//总体信息
$array_dbinfo=array();
$array_tableinfo=array();
$command_minfo="$_mysqladmin -u$mysql_user -p$mysql_pass -h$mysql_host extended-status | $_grep -E 'Bytes_received|Bytes_sent|Connections|Uptime|Slow_queries|Questions|Com_insert|Com_select|Com_update|Com_delete|Threads_created|Threads_connected|Slave_running' | $_grep -vE 'Uptime_since_flush_status|Com_update_multi|Com_insert_select|Com_delete_multi' | $_awk '{print $2 \"|\" $4}'";
DebugInfo(3,$debug_level,"[$module_name]::[command_minfo:$command_minfo]");
@exec($command_minfo,$m_info,$m_stat);
if ($m_stat!=0) {
    DebugInfo(1,$debug_level,"[$module_name]::[$command_minfo]-[run_failed]");
} else {
    foreach ($m_info as $mysql_status) {
        list($status_name,$status_value)=explode('|',$mysql_status);
        if ($status_name=='Bytes_received') {
            $Bytes_received=$status_value;
        } elseif ($status_name=='Bytes_sent') {
            $Bytes_sent=$status_value;
        } elseif ($status_name=='Connections') {
            $Connections=$status_value;
        } elseif ($status_name=='Slow_queries') {
            $Slow_queries=$status_value;
        } elseif ($status_name=='Questions') {
            $Questions=$status_value;
        } elseif ($status_name=='Com_insert') {
            $Com_insert=$status_value;
        } elseif ($status_name=='Com_select') {
            $Com_select=$status_value;
        } elseif ($status_name=='Com_update') {
            $Com_update=$status_value;
        } elseif ($status_name=='Com_delete') {
            $Com_delete=$status_value;
        } elseif ($status_name=='Threads_created') {
            $Threads_created=$status_value;
        } elseif ($status_name=='Threads_connected') {
            $Threads_connected=$status_value;
        } elseif ($status_name=='Uptime') {
            $Uptime=$status_value;
        } elseif ($status_name=='Slave_running') {
            $Slave_running=$status_value;
        }
        DebugInfo(2,$debug_level,"[$module_name]::[Bytes_received:$Bytes_received]-[Bytes_sent:$Bytes_sent]-[Connections:$Connections]-[Slow_queries:$Slow_queries]-[Questions:$Questions]-[Com_insert:$Com_insert]-[Com_select:$Com_select]-[Com_update:$Com_update]-[Com_delete:$Com_delete]-[Threads_created:$Threads_created]-[Threads_connected:$Threads_connected]-[Uptime:$Uptime]-[Slave_running:$Slave_running]");
    }
    if ($last_mysql['Uptime']>0 && 0<($mysql_duration=$Uptime-$last_mysql['Uptime'])) {
        if (0<($received_byte=$Bytes_received-$last_mysql['Bytes_received'])) {
            $mysql_received=ceil($received_byte/$mysql_duration);
        }
        if (0<($sent_byte=$Bytes_sent-$last_mysql['Bytes_sent'])) {
            $mysql_sent=ceil($sent_byte/$mysql_duration);
        }
        if (0<($connections=$Connections-$last_mysql['Connections'])) {
            $mysql_connections=ceil($connections/$mysql_duration);
        }
        if (0<($slowqueries=$Slow_queries-$last_mysql['Slow_queries'])) {
            $mysql_slowquery=ceil($slowqueries/$mysql_duration);
        }
        if (0<($queries=$Questions-$last_mysql['Questions'])) {
            $mysql_query=ceil($queries/$mysql_duration);
        }
        if (0<($insert=$Com_insert-$last_mysql['Com_insert'])) {
            $mysql_insert=ceil($insert/$mysql_duration);
        }
        if (0<($select=$Com_select-$last_mysql['Com_select'])) {
            $mysql_select=ceil($select/$mysql_duration);
        }
        if (0<($update=$Com_update-$last_mysql['Com_update'])) {
            $mysql_update=ceil($update/$mysql_duration);
        }
        if (0<($delete=$Com_delete-$last_mysql['Com_delete'])) {
            $mysql_delete=ceil($connections/$mysql_duration);
        }
        if (0<($thread=$Threads_created-$last_mysql['Threads_created'])) {
            $mysql_thread=ceil($thread/$mysql_duration);
        }
    }
    $last_mysql['Uptime']=$Uptime;
    $last_mysql['Bytes_received']=$Bytes_received;
    $last_mysql['Bytes_sent']=$Bytes_sent;
    $last_mysql['Connections']=$Connections;
    $last_mysql['Slow_queries']=$Slow_queries;
    $last_mysql['Questions']=$Questions;
    $last_mysql['Com_insert']=$Com_insert;
    $last_mysql['Com_select']=$Com_select;
    $last_mysql['Com_update']=$Com_update;
    $last_mysql['Com_delete']=$Com_delete;
    $last_mysql['Threads_created']=$Threads_created;

    $mysql_summary_str="$Uptime|$mysql_thread|$mysql_slowquery|$mysql_query|$mysql_connections|$Threads_connected";
    $mysql_traffic_str="$mysql_received|$mysql_sent";
    $mysql_statment_str="$mysql_delete|$mysql_insert|$mysql_select|$mysql_update";
    $mysql_replication_str="$Slave_running";

    //具体库以及表的监控
    if (!empty($array_mysql_monitor)) {
        foreach ($array_mysql_monitor as $db_name=>$array_table) {
            $max_tb_size=0; $table_sum=0;
            $command_db="$_mysqlshow -u$mysql_user -p$mysql_pass -h$mysql_host -i $db_name |$_grep '|'";
            DebugInfo(3,$debug_level,"[$module_name]::[command_upload:$command_db]");
            @exec($command_db,$db_info,$sb_stat);
            if ($sb_stat==0) {
                foreach ($db_info as $tb_detail) {
                    $tb_status=stringToArray($tb_detail);
                    //echo implode('|',$tb_status)."\n";
                    if (@in_array('Create_options',$tb_status)) continue;
                    $table_sum++;
                    $table_name=$tb_status[1];
                    $table_engine=$tb_status[2];
                    $table_rows=$tb_status[5];
                    $table_length=$tb_status[7];
                    $table_ilength=$tb_status[9];
                    $table_autoincr=$tb_status[11];
                    $table_updatetime=$tb_status[13];
                    $table_collation=$tb_status[15];

                    //max size
                    if ($table_length>$max_tb_size) {
                        $max_tb_size=$table_length;
                        $max_tb_name=$table_name;
                    }
                    if (@in_array($table_name,$array_table)) {
                        //需要监控的表
                        $array_tableinfo[]="$table_name,$db_name,$table_engine,$table_rows,$table_length,$table_ilength,$table_autoincr,$table_updatetime,$table_collation";
                        DebugInfo(2,$debug_level,"[$module_name]::[table_name:$table_name]-[db_name:$db_name]-[table_engine:$table_engine]-[table_rows:$table_rows]-[table_length:$table_length]-[table_ilength:$table_ilength]-[table_autoincr:$table_autoincr]-[table_updatetime:$table_updatetime]-[table_collation:$table_collation]");
                    }
                }
                $array_dbinfo[]="$db_name,$table_sum,$max_tb_name,$max_tb_size";
                DebugInfo(2,$debug_level,"[$module_name]::[db_name:$db_name]-[table_sum:$table_sum]-[max_tb_name:$max_tb_name]-[max_tb_size:$max_tb_size]");
            } else {
                DebugInfo(1,$debug_level,"[$module_name]::[$command_df]-[run_failed]");
            }
            unset($db_info);
        }
    }
}
unset($m_info);

/* mysql status */
if ($mysql_running==0) {
    $mysql_status_str='0';
} else {
    $mysql_status_str=($array_conf['run_slave']==='1')?2:1;
}

/* slave replication speed */
$mysql_slowslave_str=0;
$query='show slave status;';
$link=mysql_connect($mysql_host, $mysql_user, $mysql_pass);
$result=mysql_query($query,$link);
$numRows=mysql_num_rows($result);
if ( empty($numRows) ) {
    $Slave_IO_Running='NO';
    $Slave_SQL_Running='NO';
    $Seconds_Behind_Master='0';
} else {
    $row=mysql_fetch_array($result);
    $Slave_IO_Running=$row['Slave_IO_Running']; //yes no 
    $Slave_SQL_Running=$row['Slave_SQL_Running'];
    $Seconds_Behind_Master=$row['Seconds_Behind_Master'];
}
mysql_close($link);

$mysql_str=__FLAG_MYSQL.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1.$mysql_summary_str.__SOURCE_SPLIT_TAG2.$mysql_traffic_str.__SOURCE_SPLIT_TAG2.$mysql_statment_str.__SOURCE_SPLIT_TAG2.$mysql_replication_str.__SOURCE_SPLIT_TAG2.implode('|',$array_dbinfo).__SOURCE_SPLIT_TAG2.implode(',',$array_tableinfo).__SOURCE_SPLIT_TAG2.$mysql_status_str.__SOURCE_SPLIT_TAG2.$Slave_IO_Running.__SOURCE_SPLIT_TAG2.$Slave_SQL_Running.__SOURCE_SPLIT_TAG2.$Seconds_Behind_Master;

if (!empty($mysql_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$mysql_str;
    else $upload_str=$mysql_str;
    unset($mysql_str);
}
?>
