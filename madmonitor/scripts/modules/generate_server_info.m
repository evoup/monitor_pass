<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/generate_server_info.m                                  |
  +----------------------------------------------------------------------+
  | Comment:收集服务器信息                                               |
  +----------------------------------------------------------------------+
  | Author:Odin,Modified:Rhinux,Yinjia                                   |
  +----------------------------------------------------------------------+
  | Create:2009-09-15 14:34:50,modified: 2011-09-07                      |
  +----------------------------------------------------------------------+
  | Last-Modified: 2012-12-21 14:56:44
  +----------------------------------------------------------------------+
*/
$module_name='server_info';

$total_traffic=0;

//disk
$tmp_array_partition=array();
$array_partition=array();
if ($_monitor_linux) {
    // 计算磁盘占用率
    $command_df="$_df | $_awk '{print $5\" \"$6}'";
    @exec($command_df,$df_info,$df_stat);
    if ($df_stat==0) {
        array_shift($df_info);
        foreach ($df_info as $partition_info) {
            if (empty($partition_info) || sizeof(explode(" ", $partition_info))<2) {
                continue;
            }
            list($per_capacity,$partition)=explode(' ',$partition_info);
            $capacity=str_replace('%','',$per_capacity);
            $tmp_array_partition[$partition]['capacity']=$capacity;
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[partition:$partition]-[capacity:$capacity]");
        }
    } else {
        $debug_data="[$process_name][$module_name]::[$command_df]-[run_failed]";
        DebugInfo(1,$debug_level,$debug_data);
    }
    unset($df_info);
    // 计算磁盘Inode占用率
    $command_df="$_df -i | $_awk '{print $5\" \"$6}'";
    @exec($command_df,$df_info,$df_stat);
    if ($df_stat==0) {
        array_shift($df_info);
        foreach ($df_info as $partition_info) {
            if (empty($partition_info) || sizeof(explode(" ", $partition_info))<2) {
                continue;
            }
            list($per_iused,$partition)=explode(' ',$partition_info);
            $iused=str_replace('%','',$per_iused);
            $tmp_array_partition[$partition]['iused']=$iused;
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[partition:$partition]-[iused:$iused]");
        }
    } else {
        $debug_data="[$process_name][$module_name]::[$command_df]-[run_failed]";
        DebugInfo(1,$debug_level,$debug_data);
    }
    foreach (array_keys($tmp_array_partition) as $single_partition) {
        $array_partition[]="$single_partition,{$tmp_array_partition[$single_partition]['capacity']},{$tmp_array_partition[$single_partition]['iused']}";
    }
    unset($df_info,$tmp_array_partition);
} else {
    $command_df="$_df -aHit nonfs,nullfs,devfs | $_grep -v \"^Filesystem\" | $_awk '{print $5 \" \" $8 \" \" $9 }'";
    @exec($command_df,$df_info,$df_stat);
    if ($df_stat==0) {
        //print_r($df_info);
        foreach ($df_info as $partition_info) {
            list($per_capacity,$per_iused,$partition)=explode(' ',$partition_info);
            $capacity=str_replace('%','',$per_capacity);
            $iused=str_replace('%','',$per_iused);
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[partition:$partition]-[capacity:$capacity]-[iused:$iused]");
            $array_partition[]="$partition,$capacity,$iused";
        }
    } else {
        $debug_data="[$process_name][$module_name]::[$command_df]-[run_failed]";
        DebugInfo(1,$debug_level,$debug_data);
    }
    unset($df_info);
}

//tcp connections
$command_tcp="$_netstat -an | $_grep -i 'established' | grep -i 'tcp' | $_wc -l";
@exec($command_tcp,$tcp_info,$tcp_stat);
if ($tcp_stat==0) {
    $tcp_connections=(int)trim($tcp_info[0]);
} else {
    $debug_data="[$process_name][$module_name]::[$command_tcp]-[run_failed]";
    DebugInfo(1,$debug_level,$debug_data);
}
DebugInfo(2,$debug_level,"[$process_name][$module_name]::[tcp_connections:$tcp_connections]");
unset($tcp_info);

//service
$service=array();
foreach ($_service_table as $service_name=>$service_info) {
    list($service_port,$service_host)=explode(',',$service_info);
    $service_status=checkService($service_port,$service_host,$service_name)?1:0;
    if ($service_name=='www' || $service_port=='80') {
        //这就是web server
        $web_server_status=$service_status;
    } elseif ($service_name=='mysql' || $service_port=='3306') {
        $mysql_running=$service_status;
    } 
    DebugInfo(2,$debug_level,"[$process_name][$module_name]::[service:$service_name]-[port:$service_port]-[status:$service_status]");
    $service[]="$service_name,$service_port,$service_status";
}

//link
$link=array();
foreach ($_link_table as $server_name=>$rule_number) {
    $link_total_byte=0;$link_rate=0;
    $command_ipfw="$_ipfw -T show $rule_number | $_awk '{print $3 \" \" $4}'";
    @exec($command_ipfw,$ipfw_info,$ipfw_stat);
    DebugInfo(3,$debug_level,"[$process_name][$module_name]::[command_ipfw:$command_ipfw]");
    if ($ipfw_stat==0) {
        foreach ($ipfw_info as $ipfw_detail) {
            list($rule_total_byte,$link_timestamp)=explode(' ',$ipfw_detail);
            DebugInfo(3,$debug_level,"[$process_name][$module_name]::[rule_byte:$rule_total_byte]-[link_timestamp:$link_timestamp]");
            $link_total_byte+=$rule_total_byte;
        }
        DebugInfo(3,$debug_level,"[$process_name][$module_name]::[link_total_byte:$link_total_byte]-[link_timestamp:$link_timestamp]");
        if ($last_link[$server_name]['stamp']>0 && 0<($link_duration=$link_timestamp-$last_link[$server_name]['stamp'])) {
            if (0<($link_flow=$link_total_byte-$last_link[$server_name]['byte'])) {
                $link_rate=ceil($link_flow/$link_duration);
            }
            $link[]="$_server_name,$server_name,$link_rate";
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$_server_name-$server_name]-[rate:$link_rate B/s]");
        }
        $last_link[$server_name]['byte']=$link_total_byte;
        $last_link[$server_name]['stamp']=$link_timestamp;
    } else {
        DebugInfo(1,$debug_level,"[$process_name][$module_name]::[$command_ipfw]-[run_failed]");
    }
    unset($ipfw_info);
}

if ($_monitor_linux) {
    //获取cpu,process.mem信息
    $command_top="$_top -b -n 1";
    @exec($command_top,$info,$stat);
    if ($stat==0) {
        //print_r($info);

        //Load averages
        if (!empty($info[0])) {
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$command_top]-[{$info[0]}]");
            //load averages:  2.10,  1.98,  1.36                                  up 99+21:39:18  11:43:5
            preg_match("/.*load average:\s+(.*),\s+(.*),\s+(.*)/",$info[0],$load_info);
            $one_min_load=$load_info[1];        //负载
            $up_day=$load_info[2];              //运行天数
            $up_his=$load_info[3];              //运行小时分钟秒
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[load:$one_min_load]-[up_day:$up_day]-[up_his:$up_his]");
        }
    } else {
        $debug_data="[$process_name][$module_name]::[$command_top]-[run_top_failed]";
        DebugInfo(1,$debug_level,$debug_data);
    }

    //Process
    if (!empty($info[1])) {
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$command_top]-[{$info[1]}]");
            preg_match("/.*Tasks:(.*)total,(.*)running,(.*)sleeping,(.*)stopped,(.*)zombie/",$info[1],$process_info);
            $total_proc=(int)$process_info[1];          //total数字
            $running_proc=(int)$process_info[2];        //running数字 
            $sleeping_proc=(int)$process_info[3];       //sleeping数字
            $stopped_proc=(int)$process_info[4];        //stop数字
            $zombie_proc=(int)$process_info[5];         //zombie数字
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[total:$proc_sum]-[total:$total_proc]-[running:$running_proc]-[sleeping:$sleeping_proc]-[waiting:$waiting_proc]-[stopped:$stopped_proc]-[zombie:$zombie_proc]");
    }

    //CPU
    if (!empty($info[2])) {
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[{$info[2]}]-[CPU]");
        preg_match("/Cpu\(s\):\s+(.*)%us,\s+(.*)%sy,\s+(.*)%ni,\s+(.*)%id,\s+(.*)%wa,\s+(.*)%hi,\s+(.*)%si,\s+(.*)%st/",$info[2],$cpu_info);
        $user_load=$cpu_info[1];
        $system_load=$cpu_info[2];
        $nice_load=$cpu_info[3];
        $idle_load=$cpu_info[4];
        $interrupt_load=0; // linux无该项目 
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[user:$user_load]-[nice_load:$nice_load]-[system_load:$system_load]-[interrupt_load:$interrupt_load]-[idle_load:$idle_load]");
    }

    //Mem
    if (!empty($info[3])) {
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[{$info[3]}]-[MEM]");
        preg_match("/Mem:\s+(.*)\s+total,\s+(.*)\s+used,\s+(.*)\s+free,\s+(.*)\s+buffers/",$info[3],$mem_info);
        $active_mem=$mem_info[2];
        $inact_mem=0; //linux无该项 
        $wired_mem=0; //linux无该项 
        $cache_mem=0; //linux无该项 
        $buf_mem=0; //linux无该项 
        $free_mem=$mem_info[3];
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[active_mem:$active_mem]-[inact_mem:$inact_mem]-[wired_mem:$wired_mem]-[cache_mem:$cache_mem]-[buf_mem:$buf_mem]-[free_mem:$free_mem]");
    }
    //swap
    if (!empty($info[4])) {
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[{$info[4]}]-[SWAP]");
        preg_match("/Swap:\s+(.*)\s+total,\s+(.*)\s+used,\s+(.*)\s+free,\s+(.*)\s+cached/",$info[4],$swap_info);
        $swap_total=$swap_info[1];
        $swap_used=$swap_info[2];
        $swap_free=$swap_info[3];
        $swap_inuse=$swap_info[4];
    }
    unset($info);
} else {
    //获取cpu,process,mem信息
    $command_top="$_top -t -d2 0";
    @exec($command_top,$info,$stat);
    if ($stat==0) {
        //print_r($info);

        //Load averages
        if (!empty($info[8])) {
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$command_top]-[{$info[8]}]");
            preg_match("/load averages:\s+(\S+),.+up (\d+)\+(\S+) /",$info[8],$load_info);
            $one_min_load=$load_info[1];        //负载
            $up_day=$load_info[2];              //运行天数
            $up_his=$load_info[3];              //运行小时分钟秒
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[load:$one_min_load]-[up_day:$up_day]-[up_his:$up_his]");
        }

        //Process
        if (!empty($info[9])) {
            $proc_sum=0;$running_proc=0;$sleeping_proc=0;
            list($proc_info,$proc_detail)=explode(':',$info[9]);
            list($proc_sum,)=explode(' ',$proc_info);   //总进程数
            $array_proc_detail=explode(',',$proc_detail);
            foreach ($array_proc_detail as $process_desr) {
                list($proc_tag_num,$proc_tag)= explode(' ',trim($process_desr));
                if ($proc_tag=='starting') {
                    $starting_proc=(int)$proc_tag_num;
                } elseif ($proc_tag=='running') {
                    $running_proc=(int)$proc_tag_num;       //running数字
                } elseif ($proc_tag=='sleeping') {
                    $sleeping_proc=(int)$proc_tag_num;      //sleeping数字
                } elseif ($proc_tag=='waiting') {
                    $waiting_proc=(int)$proc_tag_num;      //waiting数字
                } elseif ($proc_tag=='stopped') {
                    $stopped_proc=(int)$proc_tag_num;      //stopped数字
                } elseif ($proc_tag=='zombie') {
                    $zombie_proc=(int)$proc_tag_num;      //zombie数字
                } elseif ($proc_tag=='lock') {
                    $lock_proc=(int)$proc_tag_num;      //lock数字
                }
            }
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[total:$proc_sum]-[starting:$starting_proc]-[running:$running_proc]-[sleeping:$sleeping_proc]-[waiting:$waiting_proc]-[stopped:$stopped_proc]-[zombie:$zombie_proc]-[lock:$lock_proc]");
        }

        //CPU
        if (!empty($info[10])) {
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[{$info[10]}]-[CPU]");
            preg_match("/CPU.+(\S+)% user,\s+(\S+)% nice,\s+(\S+)% system,\s+(\S+)% interrupt,\s+(\S+)% idle/",$info[10],$cpu_info);
            $user_load=$cpu_info[1];
            $nice_load=$cpu_info[2];
            $system_load=$cpu_info[3];
            $interrupt_load=$cpu_info[4];
            $idle_load=$cpu_info[5];
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[user:$user_load]-[nice_load:$nice_load]-[system_load:$system_load]-[interrupt_load:$interrupt_load]-[idle_load:$idle_load]");
        }

        //Mem
        if (!empty($info[11])) {
            preg_match("/Mem:\s+(\S+) Active,\s+(\S+) Inact,\s+(\S+) Wired,\s+(\S+) Cache,\s+(\S+) Buf,\s+(\S+) Free/",$info[11],$mem_info);
            $active_mem=$mem_info[1];
            $inact_mem=$mem_info[2];
            $wired_mem=$mem_info[3];
            $cache_mem=$mem_info[4];
            $buf_mem=$mem_info[5];
            $free_mem=$mem_info[6];
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[active_mem:$active_mem]-[inact_mem:$inact_mem]-[wired_mem:$wired_mem]-[cache_mem:$cache_mem]-[buf_mem:$buf_mem]-[free_mem:$free_mem]");
        }

        //swap
        if (!empty($info[12])) {
            list($swap_name,$swap_detail)=explode(':',$info[12]);
            $array_swap_detail=explode(',',$swap_detail);
            foreach ($array_swap_detail as $swap_desr) {
                list($swap_cate_desr,$swap_cate)=explode(' ',trim($swap_desr));
                if ($swap_cate=='Total') {
                    $swap_total=$swap_cate_desr;
                } elseif ($swap_cate=='Used') {
                    $swap_used=$swap_cate_desr;
                } elseif ($swap_cate=='Free') {
                    $swap_free=$swap_cate_desr;
                } elseif ($swap_cate=='Inuse') {
                    $swap_inuse=$swap_cate_desr;
                }
            }
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[swap_total:$swap_total]-[swap_used:$swap_used]-[swap_free:$swap_free]-[swap_inuse:$swap_inuse]");
        }
    } else {
        $debug_data="[$process_name][$module_name]::[$command_top]-[run_top_failed]";
        DebugInfo(1,$debug_level,$debug_data);
    }
    unset($info);
}

//if stat
$traffic=array();
unset($netInfo);
@exec('cat /proc/net/dev', $netInfo, $netStat); // 目前不考虑非linux                                                    
if ($netStat==0) {                                                                                                         
    //[0] => Inter-|   Receive                                                |  Transmit                                  
    //[1] =>  face |bytes    packets errs drop fifo frame compressed multicast|bytes    packets errs drop fifo colls carrier compressed
    //[2] =>     lo:   14491     177    0    0    0     0          0         0    14491     177    0    0    0     0       0          0
    //[3] =>   eth0: 11659952    8391    0    0    0     0          0         0   323403    4942    0    0    0     0       0          0
    array_shift($netInfo);                                                                                                 
    array_shift($netInfo);                                                                                                 
    foreach ($netInfo as $ifLine) {                                                                                        
        //echo $ifLine;                                                                                                    
        $items=preg_split('/\s+/', ltrim($ifLine));                                                                        
        $if_name=$items[0];                                                                                                
        $if_in=$items[1];                                                                                                  
        $if_out=$items[9];                                                                                                 
        if ($if_name=='lo:') {                                                                                                                                          
            continue;                                                                                                      
        }                                                                                                                  
        $interfaces[$if_name]="${if_in} ${if_out}";                                                                        
    }                                                                                                                   
}
    $byte_in=0;
    $byte_out=0;
    foreach ($interfaces as $if_name=>$traffic_inout) {
        $traffic_stamp=time();
        list($part_in,$part_out)=explode(' ',$traffic_inout);
        DebugInfo(3,$debug_level,"[$process_name][$module_name]::[$if_name]-[part_in:$part_in]-[part_out:$part_out]");
        $byte_in+=$part_in;
        $byte_out+=$part_out;
        DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$if_name]-[in:$byte_in]-[out:$byte_out]");
        //count traffic
        if ($last_traffic[$if_name]['stamp']>0 && 0<($traffic_duration=$traffic_stamp-$last_traffic[$if_name]['stamp'])) {
            if (0<($traffic_in=$byte_in-$last_traffic[$if_name]['in'])) {
                $if_in=ceil($traffic_in/$traffic_duration);
            }
            if (0<($traffic_out=$byte_out-$last_traffic[$if_name]['out'])) {
                $if_out=ceil($traffic_out/$traffic_duration);
            }
            $traffic[]="$if_name,$if_in,$if_out";
            $total_traffic+=$if_in+$if_out;
            DebugInfo(2,$debug_level,"[$process_name][$module_name]::[$if_name]-[in:$if_in B/s]-[out:$if_out B/s]"); //　有bug的瞬时流量统计，不过没传generic罢了 
        }
        $last_traffic[$if_name]['stamp']=$traffic_stamp;
        $last_traffic[$if_name]['in']=$byte_in;
        $last_traffic[$if_name]['out']=$byte_out;
    }

//make update string
$server_str=__FLAG_SERVER.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;
$str_summary="$one_min_load|$up_day|$up_his|$tcp_connections";
$str_cpu="$user_load|$nice_load|$system_load|$interrupt_load|$idle_load";
$str_mem="$active_mem|$inact_mem|$wired_mem|$cache_mem|$buf_mem|$free_mem";
$str_swap="$swap_total|$swap_used|$swap_free|$swap_inuse";
$str_disk=implode('|',$array_partition);
$str_process="$proc_sum|$starting_proc|$running_proc|$sleeping_proc|$stopped_proc|$zombie_proc|$waiting_proc|$lock_proc";
$str_network=implode('|',$traffic);
$str_link=implode('|',$link);
$str_service=implode('|',$service);
$server_str.=$str_summary.__SOURCE_SPLIT_TAG2.$str_cpu.__SOURCE_SPLIT_TAG2.$str_mem.__SOURCE_SPLIT_TAG2.$str_swap.__SOURCE_SPLIT_TAG2.$str_disk.__SOURCE_SPLIT_TAG2.$str_process.__SOURCE_SPLIT_TAG2.$str_network.__SOURCE_SPLIT_TAG2.$str_link.__SOURCE_SPLIT_TAG2.$str_service;

if (!empty($server_str)) {
    $upload_str=$server_str;
    unset($server_str);
}
?>
