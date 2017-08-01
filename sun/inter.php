<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(SUNINC."/dbstore.class.php");
	global $dsql;
	$act=$_REQUEST['act'];
	$req=$_REQUEST['req'];
	$id=$_REQUEST['id'];
	$app=new DBStore('goods',$dsql); 
	if($_REQUEST['act']=="list"){
		$arr= $app->TitleList($req);
		print_r($arr);
	}
	else if($_REQUEST['act']=="query")
	{
		$arr=$app->queryOne($id,$req);
		print_r($arr);	
	}
	else if($_REQUEST['act']=="update")
	{
		$app->replace($req);
	}
	else if($_REQUEST['act']=="append")
	{
		$app->append($req);
	}
	else if($_REQUEST['act']=="remove")
	{
		$app->remove($req);
	}
	else if($_REQUEST['act']=="select")
	{
		$app->select($req);
	}
	
?>