<?php
/**
 * 生成列表栏目操作
 *
 * @version        $Id: makehtml_list_action.php 1 11:09 2010年7月19日Z tianya $
 * @package        SunCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 */
	// require_once(dirname(__FILE__)."/config.php");
	require_once('../include/config.inc.php');
	require_once(DATA."/cache/inc_catalog_base.inc");
	require_once(SUNINC."/channelunit.func.php");
	//require_once(SUNINC."/listview.class.php");
	require_once(SUNINC."/list.class.php");

	$tid = $_REQUEST["tid"];
	$lv = new ListView($tid);
	$position= MfTypedir($lv->Fields['typedir']);
	$lv->CountRecord();
	$ntotalpage = $lv->TotalPage;
	$reurl = $lv->MakeHtml(1, 10, 0);
	$lv->Display();
?>