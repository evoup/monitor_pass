<?php
/*
  +----------------------------------------------------------------------+
  | Name:fun/eventFun.m                                                
  +----------------------------------------------------------------------+
  | Comment:处理event的函数                                            
  +----------------------------------------------------------------------+
  | Author:evoup                                                         
  +----------------------------------------------------------------------+
  | Created:
  +----------------------------------------------------------------------+
  | Last-Modified: 2013-01-15 11:39:51
  +----------------------------------------------------------------------+
 */
$GLOBALS['httpStatus'] = __HTTPSTATUS_BAD_REQUEST; // 默认返回400 
header("Content-type: application/json; charset=utf-8");

if (!canAccess('read_monitorEvent')) {
    $GLOBALS['httpStatus'] = __HTTPSTATUS_FORBIDDEN;
    return;
}
switch ($GLOBALS['selector']) {
case(__SELECTOR_MASS): // 带分页查询全部事件
    empty($_POST['current_page']) && $_POST['current_page'] = 1;
    empty($_POST['line_per_page']) && $_POST['line_per_page'] = 20;
    /* {{{ 获取全部监控事件当中的一页
     */
    /* 获取全部server */
    /* {{{ 扫描所有主机
     */
    list($table_name, $start_row, $family) = array(__MDB_TAB_HOST, '', array('info')); // 从row的起点开始 
    try {
        $scanner = $GLOBALS['mdb_client']->scannerOpen( $table_name, $start_row , $family );
        while (true) {
            $get_arr = $GLOBALS['mdb_client']->scannerGet( $scanner );
            if (array_filter($get_arr) == null) break;
            foreach ( $get_arr as $TRowResult ) {
                if (!empty($TRowResult->row)) {
                    if (!empty($GLOBALS['rowKey'])) { // 如果URL里带了筛选,则保存选择的服务器 
                        $GLOBALS['rowKey']==$TRowResult->row && $host_arr[] = $TRowResult->row;
                        $GLOBALS['rowKey']==$TRowResult->row && $upload_time[$TRowResult->row] = $TRowResult->columns['info:last_upload']->value;
                    } else {
                        $host_arr[] = $TRowResult->row;
                        $upload_time[$TRowResult->row] = $TRowResult->columns['info:last_upload']->value;
                    }
                }
            }
        }
        $GLOBALS['mdb_client']->scannerClose($scanner); // 关闭scanner 
    } catch (Exception $e) {
        $err = true;
    }
    /* }}} */

    list($line_per_page, $current_page) = array($_POST['line_per_page'],$_POST['current_page']); //每页多少行，当前第几页 
    // TODO 这里要排除不监控的部分
    $host_num = count($host_arr);
    $event_num = count($event_map_table)/__EVENT_TOTAL_STATUS;

    /*{{{ 递推出全部页面和行
     */
    $page_index = 0;  // 页面的下标 
    $last_event_id = 0; // 最后打印出行的事件ID 
    $NextServer = true; // 是否进行下一台服务器的事件读取 
    do {
        for ($row = 0; $row < $line_per_page; $row++) { // 遍历行
            if ($NextServer) {
                $host = array_shift($host_arr); // 递推下一server 

                /* 得到本host的事件状态 */
                //if (@!$readed[$host] && $current_page-1==$page_index) { // 防止多次读取同一个事件 
                if (@!$readed[$host]) { // 防止多次读取同一个事件 
                    try {
                        if (!empty($host)) {
                            $arr = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array("event"));
                            $readed[$host] = true;
                        }
                    } catch (Exception $e) { }
                        $events = $arr[0]->columns;
                    $events = array_filter((array)$events);
                    if ($events) {
                        foreach ($events as $eventCode => $eventVal) {
                            $eventCode = substr($eventCode, -4); // 取出4位事件代码 
                            if ($eventCode != __EVENTCODE_DOWN && $eventVal->value==__EVENT_ACTIVE) { // 宕机事件不显示在这里 
                                @$event_arr[$host][$eventCode] = getDhms(time() - $events["event:".$eventCode]->timestamp); // 找出有事件的服务器的事件号
                            }
                        }
                    }
                }
            }

            /* 确定此行的事件 */
            if ($host) { // 得到一页的content
                if ($current_page-1==$page_index) { // 当前页才计算 
                    if (@!$info_readed[$host]) { // 确保只读一次，否则性能低下 
                        // 取出该host的监控信息,从即时信息表取出,构造各种事件的描述
                        $rs = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array('info'));
                        $uiWordArr = getEventUIDesc($host, $rs[0]->columns,false); // 对于即时表，info列族内的监控项列，不带timestamp，第三个参数传false
                        $info_readed[$host] = true;
                    }
                    $event_id = str_pad($last_event_id, __NUM_EVENTCODE, "0", STR_PAD_LEFT); // 构造本行的事件(不足以0补充 )

                    if (@$event_arr[$host][$event_id.__SUFFIX_EVENT_CAUTION]) { // 本行存在注意事件 
                        // 对于上次有事件的要取事件的描述，因为这时取即时表的数据可能已经恢复了，而尚未扫描的仍有事件的话就不一致了
                        /* 取出该事件 */
                        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $host, "event:".$event_id.__SUFFIX_EVENT_CAUTION);
                        list(,$event_desc) = explode('|', $arr[0]->value); // 取得事件描述
                        $temp_page[$page_index][$row] = array($host, $event_item_map_table[$event_id][__EVENT_LANG_CHS], $event_id, __EVENT_CLASS_CAUTION, $event_arr[$host][$event_id.__SUFFIX_EVENT_CAUTION], date("Y-m-d H:i:s", $upload_time[$host]), $event_desc);
                    } elseif (@$event_arr[$host][$event_id.__SUFFIX_EVENT_WARNING]) { // 本行存在严重事件 
                        /* 取出该事件 */
                        $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $host, "event:".$event_id.__SUFFIX_EVENT_CAUTION);
                        list(,$event_desc) = explode('|', $arr[0]->value);
                        $temp_page[$page_index][$row] = array($host, $event_item_map_table[$event_id][__EVENT_LANG_CHS], $event_id, __EVENT_CLASS_WARNING, $event_arr[$host][$event_id.__SUFFIX_EVENT_WARNING], date("Y-m-d H:i:s", $upload_time[$host]), $event_desc);
                    } else { // 本行的是正常事件
                        @$temp_page[$page_index][$row] = array($host, $event_item_map_table[$event_id][__EVENT_LANG_CHS], $event_id, __EVENT_CLASS_NORMAL, 'N/A', date("Y-m-d H:i:s", $upload_time[$host]), $uiWordArr[$host][$event_id]);
                    }
                } else {
                    $temp_page[$page_index]=__HAS_THIS_PAGE; // 否则仅仅标记有该行 for页面索引  
                }
            }
            if ($last_event_id<($event_num-1)) {
                $last_event_id++; // 为下一行递推下一个事件
                $NextServer = false; 
            } else {
                $last_event_id = 0; // 事件id重置到起点 
                $NextServer = true; // 遍历下一个服务器事件 
            }
        }
        $page_index++;
    } while (!empty($host_arr) || $last_event_id!=0); // 直到全部host遍历完，且事件ID也遍历完 
    /* }}} */

    // 计算总页数
    $total_pages = count($temp_page); 


    // 计算next_page和prev_page是否有效
    switch ($current_page) {
    case(1): // 当前为第一页，只有下一页 
        $prev_page = __PAGE_PREV_NO;
        $next_page = __PAGE_NEXT_YES;
        break;
    case($total_pages): // 最后一页，只有上一页 
        $prev_page = __PAGE_PREV_YES;
        $next_page = __PAGE_NEXT_NO;
        break;
    default: // 在中间的时候都有 
        $prev_page = __PAGE_PREV_YES;
        $next_page = __PAGE_NEXT_YES;
        break;
    }


    // 输出一页的json
    $page_content['records'] = $temp_page[$current_page-1]; // 数组下标从0开始，找的时候减1,显示的时候按照输入的显示 
    $page_content['page_info']['total_pages'] = $total_pages;  
    $page_content['page_info']['current_page'] = $current_page;
    $page_content['page_info']['line_per_page'] = $line_per_page;
    $page_content['page_info']['next_page'] = $next_page;
    $page_content['page_info']['prev_page'] = $prev_page;
    if (!$err) {
        $GLOBALS['httpStatus']=__HTTPSTATUS_OK;
        echo json_encode($page_content);
    } 
    /*}}}*/
    break;
case(__SELECTOR_SINGLE): // 查询一种事件
    empty($_POST['current_page']) && $_POST['current_page'] = 1;
    empty($_POST['line_per_page']) && $_POST['line_per_page'] = 20;
    /* {{{ 获取全部监控事件当中的一页
     */
    /* {{{ 扫描所有主机
     */
    list($table_name, $start_row, $family) = array(__MDB_TAB_HOST, '', array('info')); // 从row的起点开始
    try {
        $scanner = $GLOBALS['mdb_client']->scannerOpen( $table_name, $start_row , $family );
        while (true) {
            $get_arr = $GLOBALS['mdb_client']->scannerGet( $scanner );
            if (array_filter($get_arr) == null) break;
            foreach ( $get_arr as $TRowResult ) {
                if (!empty($TRowResult->row)) {
                    $host_arr[] = $TRowResult->row;
                    $upload_time[$TRowResult->row] = $TRowResult->columns['info:last_upload']->value;
                }
            }
        }
        $GLOBALS['mdb_client']->scannerClose($scanner); // 关闭scanner 
    } catch (Exception $e) {
        $err = true;
    }
    /* }}} */
    foreach ($host_arr as $host) {
        if (@!$readed[$host]) { // 防止多次读取同一个事件 
            try {
                if (!empty($host)) {
                    $arr = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array("event"));
                    $readed[$host] = true;
                }
            } catch (Exception $e) { }
                $events = $arr[0]->columns;
            $events = array_filter((array)$events);
            if ($events) {
                foreach ($events as $eventCode => $eventVal) {
                    $eventCode = substr($eventCode, -4); // 取出4位事件代码 
                    if ($eventCode != __EVENTCODE_DOWN && $eventVal->value==__EVENT_ACTIVE) { // 宕机事件不显示在这里 
                        @$event_arr[$host][$eventCode] = getDhms(time() - $events["event:".$eventCode]->timestamp); // 找出有事件的服务器的事件号
                    }
                }
            }
        }
        
        $event_id = str_pad($GLOBALS['rowKey'], __NUM_EVENTCODE, "0", STR_PAD_LEFT); // 构造本行的事件(不足以0补充 )
        $evClass=__EVENT_CLASS_NORMAL;
        $evDuration='N/A';
        $rs = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array('info'));
        $uiWordArr = getEventUIDesc($host, $rs[0]->columns,false); // 对于即时表，info列族内的监控项列，不带timestamp，第三个参数传false
        if (@$event_arr[$host][$event_id.__SUFFIX_EVENT_CAUTION]) { // 本行存在注意事件 
            $evClass=__EVENT_CLASS_CAUTION;
            $evDuration=@$event_arr[$host][$event_id.__SUFFIX_EVENT_CAUTION];
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $host, "event:".$event_id.__SUFFIX_EVENT_CAUTION);
            list(,$event_desc) = explode('|', $arr[0]->value); // 取得事件描述
        } elseif (@$event_arr[$host][$event_id.__SUFFIX_EVENT_WARNING]) { // 本行存在严重事件 
            $evClass=__EVENT_CLASS_WARNING;
            $evDuration=@$event_arr[$host][$event_id.__SUFFIX_EVENT_WARNING];
            $arr = $GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, $host, "event:".$event_id.__SUFFIX_EVENT_CAUTION);
            list(,$event_desc) = explode('|', $arr[0]->value); // 取得事件描述
        } else {
            //$event_desc= $uiWordArr[$host][$event_id];
            $event_desc = $uiWordArr[$host][str_pad($event_id.'n', __NUM_EVENTCODE, "0", STR_PAD_LEFT)];
        }
        $rs = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array('info'));
        $uiWordArr = getEventUIDesc($host, $rs[0]->columns,false); // 对于即时表，info列族内的监控项列，不带timestamp，第三个参数传false
        $outArr[]=array(
            $host,
            $event_item_map_table["{$GLOBALS['rowKey']}"][__EVENT_LANG_CHS],
            $GLOBALS['rowKey'],
            $evClass,
            $evDuration,
            date("Y-m-d H:i:s",$upload_time[$host]),
            $event_desc
        );
    }

    // 输出一页的json
    $page_content['records']=$outArr;
    $page_content['page_info']['total_pages'] = 1;  
    $page_content['page_info']['current_page'] = 1;
    $page_content['page_info']['line_per_page'] = 1000;
    $page_content['page_info']['next_page'] = 1;
    $page_content['page_info']['prev_page'] = 1;
    if (!$err) {
        $GLOBALS['httpStatus']=__HTTPSTATUS_OK;
        echo json_encode($page_content);
    } 
    /*}}}*/
    break;
case(__SELECTOR_UNHANDLED): // 查询待处理事件 
    /* {{{ 查询needfix事件(以防止恢复事件没有set到事件表造成的事件仍然没有解决的问题)
     */
    //$needfixList=$GLOBALS['mdb_client']->get(__MDB_TAB_SERVER, "needfix", "event:item");
    //$needfixList=$needfixList[0]->value;
    //$needfixArr=explode('|',$needfixList);
    //[> }}} <]
    /* {{{ 未处理有问题的事件列表
     */
    //[> 获取全部server <]
    /* {{{ 扫描所有主机
     */
    //list($table_name,$start_row,$family) = array(__MDB_TAB_HOST, '', array('info')); // 从row的起点开始 
    //try {
        //$scanner = $GLOBALS['mdb_client']->scannerOpen($table_name, $start_row, $family);
        //while (true) {
            //$get_arr = $GLOBALS['mdb_client']->scannerGet( $scanner );
            //if (array_filter($get_arr) == null) break;
            //foreach ($get_arr as $TRowResult) {
                //if (!empty($TRowResult->row)) {
                    //if (!empty($GLOBALS['rowKey'])) { // 如果URL里带了筛选 
                        //$GLOBALS['rowKey']==$TRowResult->row && $host_arr[] = $TRowResult->row;
                        //$GLOBALS['rowKey']==$TRowResult->row && $upload_time[$TRowResult->row] = $TRowResult->columns['info:last_upload']->value;
                    //} else {
                        //$host_arr[] = $TRowResult->row;
                        //$upload_time[$TRowResult->row] = $TRowResult->columns['info:last_upload']->value;
                    //}
                //}
            //}
        //}
        //$GLOBALS['mdb_client']->scannerClose($scanner); // 关闭scanner 
    //} catch (Exception $e) {
        //$err = true;
    //}
    //[> }}} <]
    //foreach ($host_arr as $host) {
        //if (in_array($host, explode(',', $_CONFIG['not_monitored']['not_monitored']))) { // 不监控的不要 
            //continue;
        //}
        //$arr = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array("event"));
        //$events = $arr[0]->columns;
        //if (!empty($events)) {
            //[> 取出该host的监控信息,从即时信息表取出 <] //TODO 这里取可能存在不及时的问题，暂时不考虑 
            //$rs = $GLOBALS['mdb_client']->getRowWithColumns(__MDB_TAB_SERVER, $host, array('info'));
            //$uiWordArr = getEventUIDesc($host, $rs[0]->columns, false); // 对于即时表，info列族内的监控项列，不带有timestamp，第三个参数传false
            //foreach ($events as $eventCode => $eventVal) {
                //$eventCode = substr($eventCode, -4);
                //$eventId = substr($eventCode, 0, 3);
                //$eventLevel = substr($eventCode, -1);
                //$eventClass = $eventLevel==__SUFFIX_EVENT_CAUTION ?2 :3;
                //$eventStartTime = $events["event:{$eventCode}"]->timestamp;
                //$durationTime = getDhms(time()-$eventStartTime); // 持续时间
                //if (in_array($eventCode, $needfixArr) && $eventCode != __EVENTCODE_DOWN && $eventVal->value==__EVENT_ACTIVE) { // 宕机事件不显示在这里 TODO 找更好的方法！ 
                    //$last_arr[] = array(
                        //$host,
                        //$event_item_map_table[$eventId][__EVENT_LANG_CHS],
                        //$eventId,
                        //$eventClass,
                        //$durationTime,
                        //date("Y-m-d H:i:s", $upload_time[$host]),
                        //$uiWordArr[$host][$eventId]
                    //); 
                //}
            //}
        //}
    //}
    //if (!$err) {
        //$GLOBALS['httpStatus']=__HTTPSTATUS_OK;
        //echo json_encode($last_arr);
    //}

    //[> }}} <]
    $ret=<<<EOT
[
	["ban9smd105mediation01-a0a095e", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "690d  3h 54m 14s", "2016-11-25 21:12:41", "Loginfo creation failed! May be not any request incoming."],
	["ban9smd106-a0a096a", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 2, "707d  2h 23m 32s", "2016-12-02 14:48:26", "Disk \/ capacity is 0%,Disk \/home capacity is 11%,Disk \/jails capacity is 1%,Disk \/jails\/ban9smd106dfs01 capacity is 5%,Disk \/jails\/ban9smd106mail01 capacity is 1%,Disk \/jails\/ban9smd106mailserver01 capacity is 39%,Disk \/jails\/ban9smd106matreader01 capacity is 36%,Disk \/jails\/ban9smd106memcached01 capacity is 1%,Disk \/jails\/ban9smd106web01 capacity is 56%,Disk \/jails\/ban9smd106web01\/services\/data\/dfs capacity is 100%,Disk \/jails\/puppet89_hiphop capacity is 7%,Disk \/tmp capacity is 0%,Disk \/usr capacity is 3%,Disk \/var capacity is 0%"],
	["ban9smd106-a0a096a", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 3, "740d 19h 18m 26s", "2016-12-02 14:48:26", "Disk \/ capacity is 0%,Disk \/home capacity is 11%,Disk \/jails capacity is 1%,Disk \/jails\/ban9smd106dfs01 capacity is 5%,Disk \/jails\/ban9smd106mail01 capacity is 1%,Disk \/jails\/ban9smd106mailserver01 capacity is 39%,Disk \/jails\/ban9smd106matreader01 capacity is 36%,Disk \/jails\/ban9smd106memcached01 capacity is 1%,Disk \/jails\/ban9smd106web01 capacity is 56%,Disk \/jails\/ban9smd106web01\/services\/data\/dfs capacity is 100%,Disk \/jails\/puppet89_hiphop capacity is 7%,Disk \/tmp capacity is 0%,Disk \/usr capacity is 3%,Disk \/var capacity is 0%"],
	["ban9smd106web01-a0a0932", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 2, "707d  2h 23m 31s", "2016-11-25 21:59:39", "Disk \/ capacity is 56%"],
	["ban9smd115-a0a0973", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 2, "707d  2h 23m 32s", "2016-12-21 23:14:27", "Disk \/ capacity is 7%,Disk \/jails capacity is 0%,Disk \/jails\/ban9smd115ft01 capacity is 40%,Disk \/jails\/ban9smd115report01 capacity is 73%,Disk \/jails02 capacity is 0%"],
	["ban9smd115report01-a0a0929", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 2, "700d  8h 16m 29s", "2016-11-25 22:29:51", "Disk \/ capacity is 73%"],
	["ban9smd115report01-a0a0929", "(Report) \u5f85\u5904\u7406log\u6570", "022", 2, "700d  8h 16m 42s", "2016-11-25 22:29:51", "wait process logs:11568"],
	["bao17app02hhvm01-a0a097c", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "690d  3h 56m 38s", "2018-07-09 00:37:07", "Loginfo creation failed! May be not any request incoming."],
	["bao17app04hhvm02-a0a0980", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "690d 15h 26m 00s", "2016-11-15 13:31:54", "Loginfo creation failed! May be not any request incoming."],
	["bao17app04hhvm03-a0a0981", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "690d  4h 06m 47s", "2016-11-15 13:33:42", "Loginfo creation failed! May be not any request incoming."],
	["beiai-a0a2191", "\u78c1\u76d8\u53ef\u7528\u7a7a\u95f4", "000", 3, "844d  3h 57m 17s", "2016-12-02 16:13:48", "Disk \/ capacity is 51%,Disk \/home capacity is 38%,Disk \/services capacity is 98%,Disk \/usr capacity is 34%"],
	["beiai-a0a2191", "TCP\/IP\u7aef\u53e3", "006", 2, "844d  3h 56m 57s", "2016-12-02 16:13:48", "service dns status is CORRUPTED,service www status is CORRUPTED,service www1 status is CORRUPTED,service www2 status is CORRUPTED"],
	["bjk09smd04hhvm03-a0b2039", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "690d  0h 41m 40s", "2016-11-23 16:20:35", "Loginfo creation failed! May be not any request incoming."],
	["bjk09smd05hhvm03-a0b203b", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "691d  5h 43m 58s", "2016-11-16 01:16:01", "Loginfo creation failed! May be not any request incoming."],
	["bjk09smd06hhvm01-a0b202d", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "689d 21h 33m 40s", "2016-11-15 10:58:20", "Loginfo creation failed! May be not any request incoming."],
	["bjk09smd10hhvm01-a0b2028", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "691d  5h 43m 58s", "2016-11-15 12:16:26", "Loginfo creation failed! May be not any request incoming."],
	["bjk09smd10hhvm02-a0b2029", "(Serving) \u65e5\u5fd7\u751f\u6210", "023", 2, "691d  5h 43m 58s", "2016-11-15 12:15:57", "Loginfo creation failed! May be not any request incoming."]
]
EOT;
    $GLOBALS['httpStatus']=__HTTPSTATUS_OK;
    echo $ret;
    break;
}
?>
