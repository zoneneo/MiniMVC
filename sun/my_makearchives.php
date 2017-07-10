<?php
/**
 * 生成文档操作
 *
 * @version        $Id: makehtml_archives_action.php 1 9:11 2010年7月19日Z tianya $
 * @package        SunCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 */
	require_once(dirname(__FILE__)."/config.php");
	require_once(SUNINC."/archives.class.php");
	$aid = $_REQUEST["aid"];
    $ac = new Archives($aid);
	$rurl = $ac->MakeHtml(0);
?>