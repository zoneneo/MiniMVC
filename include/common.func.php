<?php
/**
 * 系统核心函数存放文件
 * @version        $Id: 
 * @package        SunCMS.Libraries
 * @copyright      Copyright (c) 2016 - , Inc.
 */
if(!defined('SUNINC')) exit('suncms');

/**
 *  载入小助手,系统默认载入小助手
 *  在/data/helper.inc.php中进行默认小助手初始化的设置
 *  使用示例:
 *      在开发中,首先需要创建一个小助手函数,目录在\include\helpers中
 *  例如,我们创建一个示例为test.helper.php,文件基本内容如下:
 *  <code>
 *  if ( ! function_exists('Hellosun'))
 *  {
 *      function Hellosun()
 *      {
 *          echo "Hello! sun...";
 *      }
 *  }
 *  </code>
 *  则我们在开发中使用这个小助手的时候直接使用函数helper('test');初始化它
 *  然后在文件中就可以直接使用:Hellosun();来进行调用.
 *
 * @access    public
 * @param     mix   $helpers  小助手名称,可以是数组,可以是单个字符串
 * @return    void
 */
$_helpers = array();
function helper($helpers)
{

    //如果是数组,则进行递归操作
    if (is_array($helpers))
    {
        foreach($helpers as $sun)
        {
            helper($sun);
        }
        return;
    }

    if (isset($_helpers[$helpers]))
    {
        continue;
    }
    if (file_exists(SUNINC.'/helpers/'.$helpers.'.helper.php'))
    { 

        include_once(SUNINC.'/helpers/'.$helpers.'.helper.php');
        $_helpers[$helpers] = TRUE;
    }
    // 无法载入小助手
    if (!isset($_helpers[$helpers]))
    {
        exit('Unable to load the requested file: helpers/'.$helpers.'.helper.php');                
    }
}

function sun_htmlspecialchars($str) {
    global $cfg_lang;
    if (version_compare(PHP_VERSION, '5.4.0', '<')) return htmlspecialchars($str);
    if ($cfg_lang=='gb2312') return htmlspecialchars($str,ENT_COMPAT,'ISO-8859-1');
    else return htmlspecialchars($str);
}


/**
 *  控制器调用函数
 *
 * @access    public
 * @param     string  $ct    控制器
 * @param     string  $ac    操作事件
 * @param     string  $path  指定控制器所在目录
 * @return    string
 */
function RunApp($ct, $ac = '',$directory = '')
{
    
    $ct = preg_replace("/[^0-9a-z_]/i", '', $ct);
    $ac = preg_replace("/[^0-9a-z_]/i", '', $ac);
        
    $ac = empty ( $ac ) ? $ac = 'index' : $ac;
	if(!empty($directory)) $path = CONTROL.'/'.$directory. '/' . $ct . '.php';
	else $path = CONTROL . '/' . $ct . '.php';
        
	if (file_exists ( $path ))
	{
		require $path;
	} else {
		 if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Controller false!");
        }
        //生产环境中，找不到控制器的情况不需要记录日志
        else
        {
            header ( "location:/404.html" );
            die ();
        }
	}
	$action = 'ac_'.$ac;
    $loaderr = FALSE;
    $instance = new $ct ( );
    if (method_exists ( $instance, $action ) === TRUE)
    {
        $instance->$action();
        unset($instance);
    } else $loaderr = TRUE;
        
    if ($loaderr)
    {
        if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Method false!");
        }
        //生产环境中，找不到控制器的情况不需要记录日志
        else
        {
            header ( "location:/404.html" );
            die ();
        }
    }
}

/**
 *  载入小助手,这里用户可能载入用helps载入多个小助手
 *
 * @access    public
 * @param     string
 * @return    string
 */
function helpers($helpers)
{
    helper($helpers);
}

/**
 *  获取验证码的session值
 *
 * @return    string
 */
function GetCkVdValue()
{
	@session_id($_COOKIE['PHPSESSID']);
    @session_start();
    return isset($_SESSION['securimage_code_value']) ? $_SESSION['securimage_code_value'] : '';
}

/**
 *  PHP某些版本有Bug，不能在同一作用域中同时读session并改注销它，因此调用后需执行本函数
 *
 * @return    void
 */
function ResetVdValue()
{
    @session_start();
    $_SESSION['securimage_code_value'] = '';
}


function curlget($url){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $web = curl_exec($ch);
    curl_close($ch);
    return $web;
}

function curlpost($url,$dat)
{
    $ch = curl_init ();
    curl_setopt ($ch,CURLOPT_URL, $url );
    curl_setopt ($ch,CURLOPT_HEADER, 0 );
    curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ($ch,CURLOPT_POST, 1 );
    curl_setopt ($ch,CURLOPT_POSTFIELDS, $dat );
    $web = curl_exec($ch);
    curl_close ($ch);
    return $web;
}

function curljson($url,$dat){
    $ch = curl_init ();
    curl_setopt ($ch,CURLOPT_URL, $url );
    //这里必须定义数据传输类型为json
    $data=json_encode($dat);
    curl_setopt ($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Content-Length: ' . strlen($data)));
    curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ($ch,CURLOPT_POST, 1 );
    curl_setopt ($ch,CURLOPT_POSTFIELDS, $data );
    $web = curl_exec($ch);
    curl_close ($ch);   
    return $web;
}

function smsdata($tel,$cod,$tme){
    global $sms_arg,$sms_tip;
    $str=$cod.$sms_tip;
    $cnt=iconv("UTF-8","GB2312//IGNORE",$str);
    return http_build_query(array('id'=>"vnsoft",'pwd'=>"gamedemorhwl",'to'=>$tel,'content'=>$cnt,'time'=>$tme));   
}

function getSymbol($ln){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = '';
    for ( $i = 0; $i < $ln; $i++ ){
        $token .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $token;
}

function millisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*10000);
}

function microtimes($len=-2) {
    list($t1, $t2) = explode(' ', microtime());
    return $t2.substr($t1, 2, $len);
}

function get_access_token(){
    $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".SECRET;
    $dat=curlget($token_url);
    $jso=json_decode($dat);
    if(isset($jso->access_token)){
        return $jso->access_token;
    }
    return '';
}

function get_jsapi_ticket($token){
    $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$token;
    $dat=curlget($url);
    $jso=json_decode($dat);
    if(isset($jso->ticket)){
        return $jso->ticket;
    }
    return '';
}

function jssdk_signature($url,$tme,$ticket,$noe='wx.genetalks.com')
{
    $cfg=["debug"=> false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            "appId"=> APPID, // 必填，公众号的唯一标识
            "timestamp"=> $tme, // 必填，生成签名的时间戳
            "nonceStr"=> $noe, // 必填，生成签名的随机串
            "signature"=> '',// 必填，签名，见附录1
            "jsApiList"=> ['scanQRCode','onMenuShareTimeline','chooseWXPay','getLocation','getNetworkType'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    ];

    $req=array('jsapi_ticket'=>$ticket,
            'noncestr'=>$cfg['nonceStr'],
            'timestamp'=>$cfg['timestamp']
    );
    $tmp=http_build_query($req);
    $tmp .='&url='.$url;
    $cfg['signature'] = sha1($tmp);
    return $cfg;
}

function get_jssdk_config($url){
    $mem = new Block(897);
    $tme=time();
    $tck=false;
    try{
        $dat=json_decode($mem->read());
        $tck=($tme < $dat->tme)? true: false;
    }catch (Exception $e){
        $tck=false;
    }
    if($tck){
        $cfg=jssdk_signature($url,$tme,$dat->jsapi_ticket);
    }else{
        $token=get_access_token();
        $ticket=get_jsapi_ticket($token);
        $cfg= jssdk_signature($url,$tme,$ticket);
        try{
            $mem->write(json_encode(['tme'=>$tme + 7200,'jsapi_ticket'=>$ticket]));
        }catch(Exception $e){
        }
    }
    return json_encode($cfg);
}
function jssdk_cfg_api($url){
    $mem = new Block(897);
    $tme=time();
    $tck=false;
    try{
        $dat=json_decode($mem->read());
        $tck=($tme < $dat->tme)? true: false;
    }catch (Exception $e){
        $tck=false;
    }
    if($tck){
        $cfg=jssdk_signature($url,$tme,$dat->jsapi_ticket);
    }else{
        $token=get_access_token();
        $ticket=get_jsapi_ticket($token);
        $cfg= jssdk_signature($url,$tme,$ticket);
        try{
            $mem->write(json_encode(['tme'=>$tme + 7200,'jsapi_ticket'=>$ticket]));
        }catch(Exception $e){
        }
    }
    return $cfg;
}

function  generateQrcode($textData,$lel='L',$siz='5'){
    include SUNROOT."/lib/full/qrlib.php";
    $pngFilename = SUNROOT.'/temp/'.md5($textData.'|'.$lel.'|'.$siz).'.png';
    QRcode::png($textData, $pngFilename, $lel, $siz, 2);
    return $pngFilename;
}

function putWXQrcode($pic,$tle,$str='qrcode'){
    $token=get_access_token();
    /*临时素材
     $ul2='https://api.weixin.qq.com/cgi-bin/media/upload?type=image&access_token='.$token;
     $arr=array('file'=>'@'.$fle);
     */
    $url="https://api.weixin.qq.com/cgi-bin/material/add_material?type=image&access_token=".$token;
    $descr='{"title":"'.$tle.'", "introduction":"'.$str.'"}';
    $arr=array('media'=>'@'.$pic,'description'=>$descr);
    $jso=json_decode(curlpost($url,$arr));  
    return isset($jso->media_id)? $jso->media_id :"";   
}

function Message($title,$msg){
$str="<html><title>Message</title><body style='background-color: #ddd;'><div style='width:500px;height:200px; margin:35px auto; border:#ccc 1px solid; background-color:#FFF' ><h3 style='color:#333; text-align:center'>{Title}</h3><p style='color:#e00;text-align:center;font:Georgia'>{Message}</p><p style='text-align:center;cursor: pointer;' onClick='javascript:history.go(-1);'>Click to prev page (<span id='time'>5</span>)seconds</p></div><script language='javascript'>var t=6;var time=document.getElementById('time');function fun(){t--;time.innerHTML = t;if(t<=0){history.go(-1);clearInterval(inter);}}var inter = setInterval('fun()',1000);</script></body></html>";
$str=str_replace("{Title}",$title,$str);
echo str_replace("{Message}",$msg,$str);
}

?>
