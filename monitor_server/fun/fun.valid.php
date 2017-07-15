<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun.valid.php
  +----------------------------------------------------------------------+
  | Comment:处理上传监控消息有效性
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */

/**
 * @brief 数字检测(允空) 
 * @param $item
 * @return 
 */
function validDigital($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true;
    } else { // 如果不空再检查数据
        $Ret = (is_numeric($item) || is_float($item) || empty($item)) ?true :false;
        return ($Ret);
    }
}

/**
 * @brief 内存数数据有效性检测 
 * @param $item
 * @return 
 */
function validMem($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true; 
    } else { // 如果不空再检查数据(例如10M是有效的数据)
        $unit = strtoupper(substr($item, -1));
        if (in_array($unit, array("P", "T", "G", "M", "K"))) { // 检查是否为容量单位 
            $mb_ok = true; 
        }
        $num = substr($item,0,-1); // 得到数字位 
        $num_ok = (is_numeric($num) || is_float($num) || empty($num)) ?true :false; // 检查是否为数字或空 
        $Ret = ($mb_ok && $num_ok) ?true :false;
        return ($Ret);
    }
}

/**
 * @brief 分区信息数据有效性检测
 * @param $item 数组array(mounted,capacity,iused)
 * @return 
 */
function validPartition($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true; 
    } else { // 如果不空再检查数据(例如/home,72,12是有效数据)
        $partition_info = explode(__SOURCE_SPLIT_TAG4, $item);
        $size_ok = (sizeof($partition_info)==__SERVER_PARTITION_ITEMS_NUM) ?true :false; // 检查数组大小 
        $mounted_ok = (substr($partition_info[__SERVER_PARTITION_ITEM_MOUNTED],0,1)=="/") ?true :false; // 简单判断mounted是否为绝对路径
        $capacity_ok = validDigital($partition_info[__SERVER_PARTITION_ITEM_CAPACITY]); // 检查容量 
        $iused_ok = validDigital($partition_info[__SERVER_PARTITION_ITEM_IUSED]); // 检查使用 
        $Ret = ($size_ok && $mounted_ok && $capacity_ok && $iused_ok) ?true :false;
        return ($Ret);
    }
}

/**
 * @brief 网络接口信息数据有效性检测
 * @param $item
 * @return 
 */
function validNetworkIface($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true;
    } else { // 如果不空再检查数据(例如bge1,746,3611是有效数据)
        $network_info = explode(__SOURCE_SPLIT_TAG4, $item);
        $size_ok = (sizeof($network_info)==__SERVER_NETWORK_ITEMS_NUM) ?true :false; // 检查数组大小 
        $ifname_ok = is_string($network_info[__SERVER_NETWORK_ITEM_IFNAME]) ?true :false; // 检查网络接口名 
        $in_ok = validDigital($network_info[__SERVER_NETWORK_ITEM_IN]) ?true :false; // 检查IN数据 
        $out_ok = validDigital($network_info[__SERVER_NETWORK_ITEM_OUT]) ?true :false; // 检查IN数据 
        $Ret=($size_ok && $ifname_ok && $in_ok && $out_ok) ?true :false;
        return ($Ret);
    }
}

/**
 * @brief 连接信息数据有效性检测
 * @param $item
 * @return  
 */
function validLink($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true;
    } else { // 如果不空再检查数据(例如server1,server2,325是有效数据)
        $link_info = explode(__SOURCE_SPLIT_TAG4, $item);
        $size_ok = (sizeof($link_info)==__SERVER_LINK_ITEMS_NUM) ?true :false; // 检查数组大小 
        $sserver_ok = is_string($link_info[__SERVER_LINK_ITEM_SSERVER]); // 检查sserver名 
        $dserver_ok = is_string($link_info[__SERVER_LINK_ITEM_DSERVER]); // 检查dserver名 
        $flow_ok = validDigital($link_info[__SERVER_LINK_ITEM_FLOW]); // 检查flow数据 
        $Ret = ($size_ok && $sserver_ok && $dserver_ok && $flow_ok) ?true :false;
        return ($Ret);
    }
}

/**
 * @brief 服务信息数据有效性检测 
 * @param $item
 * @return 
 */
function validService($item) {
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true;
    } else { // 如果不空再检查数据(例如www,80,1是有效数据)
        $service_info = explode(__SOURCE_SPLIT_TAG4, $item);
        $size_ok = (sizeof($service_info)==__SERVER_SERVICE_ITEMS_NUM) ?true :false; // 检查数组大小 
        $name_ok = is_string($service_info[__SERVER_SERVICE_ITEM_NAME]); // 检查name名 
        $port_ok = is_string($service_info[__SERVER_SERVICE_ITEM_PORT]); // 检查port名 
        $status_ok = (!empty($service_info[__SERVER_SERVICE_ITEM_STATUS]) && in_array($service_info[__SERVER_SERVICE_ITEM_STATUS],array(0,1)) || empty($service_info[__SERVER_SERVICE_ITEM_STATUS])) ?true :false; // 检查status 
        $Ret = ($size_ok && $name_ok && $port_ok && $status_ok) ?true :false;
        return ($Ret);
    }
}


/**
 * @brief adimage信息数据有效性检测
 * @param $item 数组
 * @return 
 */
function validDomainInfo($item) {
    global $module_name;
    $fun_name = __FUNCTION__;
    if (empty($item)) { // 客户端还会传出有这项但是为空的状况,须允空
        return true; 
    } else { // 如果不空再检查数据域(例如 域id,广告位数量,广告活动数量,投放缓存数量,发布包序列码,发布角色 是有效数据)
        $domain_info = explode(__SOURCE_SPLIT_TAG4, $item);
        list($domain_id, $adpos_num, $adcampaign_num, $delivering_cache_num, $pack_serialnum, $publish_role) = $domain_info;
        $size_ok = true; // 客户端还会少传数据域,所以不检查总数据域个数
        $domainid_ok = is_numeric($domain_id) ?true :false; // 检查domainid 
        $adposnum_ok = is_numeric($adpos_num) || empty($adpos_num) ?true :false; // 检查广告位数量 
        $adcampaignnum_ok = is_numeric($adcampaign_num) || empty($adcampaign_num) ?true :false; // 检查广告活动 
        $deliveringcachenum_ok = is_numeric($delivering_cache_num) || empty($delivering_cache_num) ?true :false; // 检查投放缓存数量 
        SaveSysLog("[$module_name][$fun_name][pack_serialnum:$pack_serialnum]",5);
        $packserialnum_ok = is_string($pack_serialnum) || empty($pack_serialnum) ?true :false; // 发布序列号不一定是指定位数的 
        $publishrole_ok = is_numeric($publish_role) || empty($publish_role) ?true :false; // 检查发布角色(id )
        $Ret = ($size_ok && $domainid_ok && $adposnum_ok && $adcampaignnum_ok && $deliveringcachenum_ok && $packserialnum_ok && $publishrole_ok) ?true :false;
        SaveSysLog("size_ok:$size_ok domainid_ok:$domainid_ok adposnum_ok:$adposnum_ok adcampaignnum_ok:$adcampaignnum_ok deliveringcachenum_ok:$deliveringcachenum_ok packserialnum_ok:$packserialnum_ok publishrole_ok:$publishrole_ok",5);
        return ($Ret);
    }
}
?>
