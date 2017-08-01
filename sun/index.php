<?php
	require_once("config.php");
	$ct=$_REQUEST['ct']==''? 'admin' : $_REQUEST['ct'];
	$ac=$_REQUEST['ac']==''? 'index' : $_REQUEST['ac'];	
	if(empty($_SESSION["USERNAME"]))
	{
		if($ac!='auth'){
			$ct='admin';
			$ac='login';	
		}	
	}

	RunApp($ct,$ac);
?>