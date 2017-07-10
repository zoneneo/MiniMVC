<?php
/**
 * 生成列表栏目操作
 *
 * @version        $Id: makehtml_list_action.php 1 11:09 2010年7月19日Z tianya $
 * @package        SunCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 */
	require_once(dirname(__FILE__)."/config.php");
	require_once(SUNDATA."/cache/inc_catalog_base.inc");
	require_once(SUNINC."/channel.func.php");
	require_once(SUNINC."/listview.class.php");
	$tid = $_REQUEST["tid"];
	test(1);	
	$lv = new ListView($tid);
	$lv->MakeHtml(1, 1, 0);
	/*
	$position= MfTypedir($lv->Fields['typedir']);
    $lv->CountRecord();
	$ntotalpage = $lv->TotalPage;
	$reurl = $lv->MakeHtml('', '', 0);
	$reurl = $lv->MakeHtml(1, 1, 0);
	echo $reurl;
	*/
	$lv->Display();
?>