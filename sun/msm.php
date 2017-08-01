<?php
	/*
	短信验证网关url http://sms.mob.com/	
	*/
	require_once('common.inc.php');
	$mob=$_REQUEST['mob'];
	$cod=$_REQUEST['cod'];

	
	if(!is_numeric($mob)||!is_numeric($cod))
		exit('格式错误');
	
	//接口地址
	$api = 'https://webapi.sms.mob.com'; 
	$appkey = '116bdf79fe13a';
	//发送验证码
	$response = postRequest($api .'/sms/verify', array(
		'appkey' => $appkey,
		'phone' => $mob,
		'zone' => '86',
		'code' => $cod,
	) );
	
	$txt=base64_encode($response);
	$dsql->SetQuery("INSERT INTO `#@__smscheck` (chk,sed,tme,cod,mob,typ,txt) VALUES('0','1','1','$cod','$mob','register','$txt')");
	$lgc=$dsql->ExecuteNoneQuery2();
	echo '{"result":"'.$lgc.'"}';
	/**
	 * 发起一个post请求到指定接口
	 * 
	 * @param string $api 请求的接口
	 * @param array $params post参数
	 * @param int $timeout 超时时间
	 * @return string 请求结果
	 */
	function postRequest( $api, array $params = array(), $timeout = 30 ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $api );
		// 以返回的形式接收信息
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		// 设置为POST方式
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
		// 不验证https证书
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
			'Accept: application/json',
		) ); 
		// 发送数据
		$response = curl_exec( $ch );
		// 不要忘记释放资源
		curl_close( $ch );
		return $response;
	}
?>