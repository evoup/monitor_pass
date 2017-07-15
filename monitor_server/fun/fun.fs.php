<?php
/*
  +----------------------------------------------------------------------+
  | Name:
  +----------------------------------------------------------------------+
  | Comment: 文件操作相关函数
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified:
  +----------------------------------------------------------------------+
 */

/**
 * @brief 创建文件夹
 *
 * @param $path 文件路径
 * @param $mode 文件模式
 * @param $depth 级数
 * @param $type 未采用(保留)
 *
 * @return 
 */
function makeDir($path,$mode="0755",$depth=0,$type='d') {
    $input_type=empty($type)?'d':strtolower($type);
    $path=($input_type==='d')?$path:dirname($path);
    $depth--;
    $subpath=dirname($path);
    if (!file_exists($path)) {
        if ($depth>0 && (!empty($subpath) || $subpath!='.')) {
            makeDir($subpath,$mode,$depth);
        }
        exec("/bin/mkdir -p -m $mode $path");
    } elseif (is_dir($path)) {
        return true;
    } else {
        return false;
    }
}

?>

