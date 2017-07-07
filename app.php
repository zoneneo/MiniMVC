<?php
/**
 * 手机APP接口程序
 * @explain		软件要求支持PDO数据对象的服务环境
 * @author		ander.sun
 * @version		1.0.1
 * @package     Mobile APP后台接口
 */
 
//include("TopSdk.php");
require_once("include/config.inc.php");
require_once("include/AppControl.php");


header("Content-Type: text/html; charset=utf-8");
//header("Access-Control-Allow-Origin: http://vuvu:8888"); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
session_start();

$param=array(
		"register"=>[],
		"signature"=>[],
		"cardid"=>[],
		"auth"=>[],
		"authp"=>[],
		"report"=>[],
		"tririsk"=>[],
		"disease"=>[],
		"listing"=>[],
		"sms"=>[],
		"wx"=>[],
		"smscode"=>[],
		"getProfile"=>[],
		"tripower"=>[],
		"capacity"=>[],
		"test"=>[]
);

$mod=isset($_REQUEST['mod'])? preg_replace('/[^a-z]/i','',$_REQUEST['mod']) : '';
if(class_exists('AppControl')){
	$control = new ReflectionClass('AppControl'); 
	if(method_exists('AppControl',$mod)){
		if(!isset($_SESSION['WEIXINOPENID']) && $mod!='wx'){
			echo json_encode(['sig'=>3,'msg'=>'Without Authorization!']);
			exit;
		}	
		$instance = $control->newInstanceArgs();
		$ec=$control->getmethod($mod);
		$arr = $ec->invokeArgs($instance,$param[$mod]); 
		echo json_encode($arr);
		exit;
	}else{
		echo json_encode(['sig'=>2,'msg'=>$mod.' Methods does not exist!']);
		exit;		
	}
}else{
	echo json_encode(['sig'=>1,'msg'=>'Controller does not exist!']);
	exit;
}

?>
