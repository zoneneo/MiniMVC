<?php
require_once(dirname(__FILE__)."/../include/common.inc.php");
include(SUNINC."/list.view.php");
include("testing.php");

$tid = $_REQUEST['tid'];
$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);

//$channelid = (isset($channelid) && is_numeric($channelid) ? $channelid : 0);

//if($tid==0 && $channelid==0) die(" Request Error! ");
if($tid==0) die(" Request Error! ");
if(isset($TotalResult)) $TotalResult = intval(preg_replace("/[^\d]/", '', $TotalResult));

//print_r($GLOBALS);
$lv = new ListView($tid);

$lv->Display();

?>