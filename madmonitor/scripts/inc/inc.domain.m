<?php
/*
  +----------------------------------------------------------------------+
  | Name:
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
*/

// generic监控类型                                                                                                      
define(__SERVER_FIELDS_NUM,              9 ); // server的各大指标总数                                                   
define(__SERVER_FIELD_SUMMARY,           0 ); // server信息数组的概要下标                                               
define(__SERVER_FIELD_CPU,               1 ); // server信息数组的CPU下标                                                
define(__SERVER_FIELD_CPU_NUM,           5 ); // server信息数组的CPU数组元素个数                                        
define(__SERVER_FIELD_MEM,               2 ); // server信息数组的内存下标                                               
define(__SERVER_FIELD_MEM_NUM,           6 ); // server信息数组的MEM数组元素个数                                        
define(__SERVER_FIELD_SWAP,              3 ); // server信息数组的SWAP下标                                               
define(__SERVER_FIELD_SWAP_NUM,          4 ); // server信息数组的SWAP数组元素个数                                       
define(__SERVER_FIELD_DISK,              4 ); // server信息数组的磁盘下标                                               
define(__SERVER_FIELD_PROCESS,           5 ); // server信息数组的进程下标                                               
define(__SERVER_FIELD_NETWORK,           6 ); // server信息数组的网卡下标                                               
define(__SERVER_FIELD_LINK,              7 ); // server信息数组的LINK下标                                               
define(__SERVER_FIELD_SERVICE,           8 ); // server信息数组的服务下标                                               
define(__SERVER_SUMMARY_ITEMS_NUM,       4 ); // 概要数组大小                                                           
define(__SERVER_CPU_ITEMS_NUM,           5 ); // CPU数组大小                                                            
define(__SERVER_MEM_ITEMS_NUM,           6 ); // 内存数组大小                                                           
define(__SERVER_DISK_ITEMS_NUM,          3 ); // 磁盘输出大小                                                           
define(__SERVER_SWAP_ITEMS_NUM,          4 ); // SWAP数组大小                                                           
define(__SERVER_PARTITION_ITEMS_NUM,     3 ); // 分区数组大小                                                           
define(__SERVER_PARTITION_ITEM_MOUNTED,  0 ); // 分区数组mounted下标                                                    
define(__SERVER_PARTITION_ITEM_CAPACITY, 1 ); // 分区数组capacity下标                                                   
define(__SERVER_PARTITION_ITEM_IUSED,    2 ); // 分区数组iused下标                                                      
define(__SERVER_PROCESS_ITEMS_NUM,       8 ); // 进程数组大小                                                           
define(__SERVER_NETWORK_ITEMS_NUM,       3 ); // 网络数组大小                                                           
define(__SERVER_NETWORK_ITEM_IFNAME,     0 ); // 网络数组ifname下标                                                     
define(__SERVER_NETWORK_ITEM_IN,         1 ); // 网络数组in下标                                                         
define(__SERVER_NETWORK_ITEM_OUT,        2 ); // 网络数组out下标                                                        
define(__SERVER_LINK_ITEMS_NUM,          3 ); // Link数组大小                                                           
define(__SERVER_LINK_ITEM_SSERVER,       0 ); // Link数组sserver下标                                                    
define(__SERVER_LINK_ITEM_DSERVER,       1 ); // Link数组dserver下标                                                    
define(__SERVER_LINK_ITEM_FLOW,          2 ); // Link数组flow下标                                                       
define(__SERVER_SERVICE_ITEMS_NUM,       3 ); // 服务数组大小                                                                                                           
define(__SERVER_SERVICE_ITEM_NAME,       0 ); // 服务数组name下标                                                       
define(__SERVER_SERVICE_ITEM_PORT,       1 ); // 服务数组port下标                                                       
define(__SERVER_SERVICE_ITEM_STATUS,     2 ); // 服务数组status下标                                                     
define(__SERVER_SUMMARY_FIELDS_NUM,      4 ); // server类型summary字段总数



