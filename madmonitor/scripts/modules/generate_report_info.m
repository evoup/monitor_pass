<?php
/*
  +----------------------------------------------------------------------+
  | Name:modules/generate_report_info.m                                  |
  +----------------------------------------------------------------------+
  | Comment:收集report信息                                               |
  +----------------------------------------------------------------------+
  | Author:Odin                                                          |
  +----------------------------------------------------------------------+
  | Create:2009-10-11 23:02:43                                           |
  +----------------------------------------------------------------------+
  | Last-Modified:2009-10-11 23:02:49                                    |
  +----------------------------------------------------------------------+
*/
$module_name='report_info';

$report_str=__FLAG_REPORT.__SOURCE_SPLIT_TAG1.$_server_name.__SOURCE_SPLIT_TAG1;

//file info
$str_finfo='';

//speed
$str_speed=100;

//left
$str_left=0;
if ($left_files=(int)@exec("$_find $_upload_path -name \"*.tbz2\" | $_wc -l")) {
    $str_left=$left_files;
}

$report_str.=$str_finfo.__SOURCE_SPLIT_TAG2.$str_speed.__SOURCE_SPLIT_TAG2.$str_left;

if (!empty($report_str)) {
    if (!empty($upload_str)) $upload_str.="\n".$report_str;
    else $upload_str=$report_str;
    unset($report_str);
}
?>
