<?php
	require_once("include/config.inc.php");
	require_once("include/Member.class.php");
	session_start();

	if(isset($_REQUEST["entry"])){
		$_SESSION['ENTRYPAGE']='bindCard';
	}else{
		$_SESSION['ENTRYPAGE']= 'report';
	}

	$user = new Member();
	$scope = 'snsapi_userinfo';
	$opi = $user->getMember();	
	 	
	if($opi !=''){
		/*
		$col = $mgocli->selectCollection("genetk","weixin");
		$row = $col->findOne(array("_id"=>$opi));
		if(!empty($row)){
			if(!isset($row['tel']) || $row['tel']==''){
				//$url="https://wx.genetalks.com/template.html?page=login";
				header("Location: https://wx.genetalks.com/template.html?page=login");
			}else{
				$_SESSION['CALLNUMBER'] = $row['tel'];
				header("Location:https://wx.genetalks.com/template.html?page=bindCard");
			}
			$_SESSION['WEIXINOPENID']=$opi;
			$scope = 'snsapi_base'; 
		}
		*/
		$scope = 'snsapi_base';
	}
	
	$REDIRECT_URI=$cfg_site.'/oauth.php';
	$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$scope.'#wechat_redirect';
	header("Location:".$url);
		
?>