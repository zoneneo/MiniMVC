<?php
	require_once("include/config.inc.php");
	require_once("include/Member.class.php");
	session_start();

	if(!isset($_REQUEST["code"]) || $_REQUEST["code"]=='')
		echo json_encode(['sig'=>10,'msg'=>'请使用微信客户端访问链接!']);

	$code = isset($_REQUEST["code"]) ? $_REQUEST["code"] : '';
	$stat = isset($_REQUEST["state"]) ? $_REQUEST["state"] : 'snsapi_base';
	$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPID.'&secret='.SECRET.'&code='.$code.'&grant_type=authorization_code';

	$res = curlget($get_token_url);
	$json = json_decode($res,true);	//根据openid和access_token查询用户信息
	if(isset($json['access_token'])&&isset($json['openid'])){
		$tme=strval(time());
		$opi = $json['openid'];	
		$_SESSION['WEIXINOPENID']=$opi;
		$col = $mgocli->selectCollection("genetk","weixin");
		$row = $col->findOne(array("_id"=>"$opi"));
		if(empty($row)){
			$opi = $json['openid'];
			if($stat=='snsapi_userinfo'){
				$token = $json['access_token'];			
				$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$opi.'&lang=zh_CN';
				$ifo = curlget($get_user_info_url);
				$info = json_decode($ifo,true);
				$img=isset($info['headimgurl'])? $info['headimgurl'] : '';
				$usr=isset($info['nickname'])? $info['nickname'] : '';
			}else{
				$img=$usr='';				
			}
			$col->insert(['_id'=>$opi,'tme'=>$tme,'tel'=>'','usr'=>$usr,'name'=>'','avatar'=>$img]);
			$url="https://wx.genetalks.com/template.html?page=login";
		}else{
			if(!isset($row['tel']) || $row['tel']==''){
				$url="https://wx.genetalks.com/template.html?page=login";
			}else{
				$_SESSION['CALLNUMBER'] = $row['tel'];
				$entry=$_SESSION[ENTRYPAGE];
				$url="https://wx.genetalks.com/template.html?page=".$entry;				
			}
		}
		$user = new Member();
		$user->addMember($opi);
		header("Location:".$url);
	}else{
		echo json_encode(['sig'=>10,'msg'=>'获取OpenID失败']);
	}
?>