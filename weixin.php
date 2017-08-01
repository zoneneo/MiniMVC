<?php
	require_once("include/config.inc.php");
	include_once('include/weixin.class.php');//引用刚定义的微信消息处理类<br>

	$weixin = new Weixin(TOKEN,DEBUG);//实例化
	if(isset($_REQUEST['echostr'])){
		$weixin->valid();
		exit;
	}
	
	$weixin->getMsg();
	$user = $weixin->msg['FromUserName'];//哪个用户给你发的消息,这个$username是微信加密之后的，但是每个用户都是一一对应的	
	$type = $weixin->msgtype;//消息类型
	if ($type==='text') {
		$msg = $weixin->msg['Content'];   //用户的文本消息内容
		if(preg_match('/(^G[A-z]*)<([^>]*)\>/',$msg,$out)){
			$cmd=$out[1];
			$par=$out[2];
			if($cmd=='GQR'){
				$col=$mgo->selectCollection("insurance","agent");
				$arr=$col->findOne(array('_id'=>$user));
				$col=$mgo->selectCollection("insurance","organ");
				if($par==''){
					$alw=array('tel'=>$arr['tel']);
				}else{
					//$alw=array('tel'=>$tel,'breve'=>$par);
					$alw=array('breve'=>$par);
				}
				$row=$col->findOne($alw);
				if(empty($row)){
					$reply = $weixin->makeText('无法获取二维码,权限或参数错误！');
					$weixin->reply($reply);	
				}
				else
				{
					$tme=$ORG_Expires +time();
					if(isset($row['media'])&&$row['media']!==''){
						$reply = $weixin->makeImage($row['media']);
						$weixin->reply($reply);
						$col->update(array('_id'=>$row['_id']),array('$set'=>array('tme'=>$tme)));
					}
					else
					{
						$pic=generateQrcode($row['_id']);
						$media=putWXQrcode($pic,$row['org'],$row['_id']);
						if($media==''){
							$reply = $weixin->makeText('生成二维码图片失败!');
							$weixin->reply($reply);
						}else{
							$reply = $weixin->makeImage($media);
							$weixin->reply($reply);	
							$col->update(array('_id'=>$row['_id']),array('$set'=>array('tme'=>$tme,'media'=>$media)));
						}
					}
				}					
			}
			else
			{
				$reply = $weixin->makeText($cmd."无效查询");
				$weixin->reply($reply);	
			}	
		}else{//转多客服系统
			$reply = $weixin->transfer();	//"kf2003@gh_63323edd60b9"
			$weixin->reply($reply);
		}
	}elseif ($type==='location') {
		  //用户发送的是位置信息  稍后的文章中会处理
	}elseif ($type==='image') {
		$media = $weixin->msg['MediaId'];   //图片id
		//$reply = $weixin->makeImage($media);
		$reply = $weixin->makeText($media);
		$weixin->reply($reply);		
	}elseif ($type==='voice') {
		  //用户发送的是声音 稍后的文章中会处理
	}elseif ($type==='video') {
		//
	}elseif ($type==='link') {
		//
	}elseif ($type==='event') {
		$evt=$weixin->msg['Event'];
		//$reply=json_encode($weixin->msg);
		if($evt=='subscribe'){
			$reply = $weixin->makeText("让小G和你一起,探索基因的秘密");
			$weixin->reply($reply);			
		}else if($evt=='service_auto_replay'){
			$reply = $weixin->makeText("小G在线时间：周一至周五，9:00  - 17:00。");
			$weixin->reply($reply);			
		}
	}else{
		//$weixin->reply($reply);
	}

?>
