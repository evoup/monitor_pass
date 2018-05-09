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



function getItems() {
    $ret = [];
	if (($handle = fopen("items.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $ret[]=$data;
		}
		fclose($handle);
	}
    return $ret;
}

function getItemsApplication($item_id) {
    $ret = [];
	if (($handle = fopen("items_applications.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            list($itemappid,$applicationid,$itemid)=$data;
            if ($item_id==$itemid) {
                $ret[]=$applicationid;
            }
		}
		fclose($handle);
	}
    return $ret;
}


