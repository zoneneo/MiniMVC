<?php
/**
 * 生成列表栏目操作
 *
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 */

require_once('../include/config.inc.php');
require_once(DATA."/cache/inc_catalog_base.inc");
require_once(SUNINC."/channelunit.func.php");
//require_once(SUNINC."/listview.class.php");
require_once(SUNINC."/list.class.php");

require_once(SUNINC."/archives.class.php");

if(!isset($_REQUEST["tid"]))
	exit(0);

$tid = $_REQUEST["tid"];
$lv = new ListView($tid);
$position= MfTypedir($lv->Fields['typedir']);
$lv->CountRecord();
$ntotalpage = $lv->TotalPage;
$reurl = $lv->MakeHtml(1, 0, 0);
//$lv->Display();


$vs = array();
$vs = GetParentIds($tid);
foreach($vs as $k=>$v)
{
	$dsql->Execute('dd',"SELECT id FROM #@__archives typeid = '{$v}' ");
	while ($row = $dsql->GetArray(dd)) {
		$aid=$row['id'];
		$ac = new Archives($aid);
		$rurl = $ac->MakeHtml(0);
		echo "生成文档: <a herf='{$rurl}'> {$rurl} </a>";
	}
}

?>