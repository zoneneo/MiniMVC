<?php
require_once("config.php");
session_start();
if($_SESSION['USERNAME']==''){
	echo "权限不够";
	exit(0);
}
$fdv=urldecode($_REQUEST['tar']);
$act=$_REQUEST['act'];
if(!empty($act))
{
	preg_match("/[\d]+/",$act,$arr);
	if(is_array($arr)){
		$id=$arr[0];
		$fdn =str_replace($id,'',$act);
		if($fdn=='litpic'){	
			if(preg_match("/^(http:\/\/)?([^\/]+)/i",$fdv)){
				$fpath = pathinfo($fdv);
				$img=date('YmdHs',time()).'.'.$fpath['extension'];
				grab_img($fdv,$img);
				$fdv=$img;	
			}
		}
		$n=$dsql->ExecuteNoneQuery2("UPDATE `#@__archives` SET $fdn='$fdv' WHERE id='$id'");
		if($n==true){
			if($fdn=='litpic')
				echo $GLOBALS['cfg_mediasurl'].'/'.$fdv;
			else
				echo $fdv;
		}
		exit;
	}
}
function grab_img($url,$fle)
{
	$curl = curl_init();  
	curl_setopt($curl, CURLOPT_URL, $url);  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);  
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);  
	$imageData = curl_exec($curl);  
	curl_close($curl); 
	$pic=ATTACHED.$fle;
	$tp = @fopen($pic, 'wb');  
	fwrite($tp, $imageData);  
	fclose($tp);
}
?>