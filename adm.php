<?php
	error_reporting(E_ALL);
	require_once('include/common.inc.php');
	session_start();
	$ct=isset($_REQUEST['ct']) ? $_REQUEST['ct'] : 'admin';
	$ac=isset($_REQUEST['ac']) ? $_REQUEST['ac'] : 'index';	
	if(empty($_SESSION["USERNAME"]))
	{
		if($ac!='auth'){
			$ct='admin';
			$ac='login';
		}	
	}
	RunApp($ct,$ac);
?>
