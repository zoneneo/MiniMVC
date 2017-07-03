<?php
/**
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */
define('DEBUG', TRUE);
// 报错级别设定,一般在开发环境中用E_ALL,这样能够看到所有错误提示
// 系统正常运行后,直接设定为E_ALL || ~E_NOTICE,取消错误显示
if(DEBUG){
	error_reporting(E_ALL);
}else{
	error_reporting(E_ALL || ~E_NOTICE);
}

define('SUNINC', str_replace("\\", '/', dirname(__FILE__) ) );
define('SUNROOT', str_replace("\\", '/', substr(SUNINC,0,-8) ) );
define('MODEL', SUNINC.'/model');
define('CONTROL', SUNINC.'/control');
define('SUNTPL', SUNINC.'/templets');
define('ATTACH', SUNROOT.'/static/uploads');

//数据库连接信息
$cfg_dbhost = 'localhost';
$cfg_dbname = 'gene';
$cfg_dbuser = 'root';
$cfg_dbpwd = 'sun2asw@gen';
$cfg_dbprefix = 'sun_';
$cfg_db_language = 'utf8';
$cfg_cmspath = '/gene';

//模板的存放目录
$cfg_df_style = "default";
$cfg_tplcache_dir = DATA."/tplcache";
$cfg_templets_dir = $cfg_cmspath.'/templets';

//other

$cfg_site = 'http://wx.genetalks.com';
$cfg_api ='http://api.genetalks.com';

$app_url = $cfg_site.'/template.html?page=';
$cookie_id = 'G_N_T_K';
$cookie_encode = 'Genetalks';
$sms_url='http://service.winic.org/sys_port/gateway/';
$sms_arg=array('id'=>"vnsoft",'pwd'=>"gamedemorhwl",'to'=>'','content'=>'','time'=>0);
$sms_tip=' 验证码请在30分钟内填写! ';

//全局常用函数
require_once(SUNINC.'/common.func.php');


//导入数据控制
//require_once(SUNINC.'/sunpdo.class.php');

// 模块MVC框架需要的控制器和模型基类
require_once(SUNINC.'/control.class.php');
require_once(SUNINC.'/model.class.php');

require_once(SUNINC."/Block.php");


/*定义微信公众号参数*/
define('APPID', "wxda9d81cad8e63237");
define('SECRET', "a3bc650a8de954ccbf795f818a32ae6a");
define('TOKEN', "genetalks");


date_default_timezone_set('Asia/Shanghai');

if($_SERVER["HTTP_HOST"]=='localhost'){
	$mgo = new MongoClient();
}else{
	$mgo = new MongoClient("mongodb://gene:talks2run@wx.genetalks.com:25015",array("connect" => TRUE));
}




?>
