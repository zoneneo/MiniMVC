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
define('SUNROOT', substr(SUNINC,0,strrpos(SUNINC,'/')));
// define('SUNROOT', str_replace("\\", '/', substr(SUNINC,0,-8) ) );
define('MODEL', SUNINC.'/model');
define('CONTROL', SUNINC.'/control');
define('DATA', SUNROOT.'/data');
define('SUNTPL', SUNROOT.'/templets');
define('ATTACH', SUNROOT.'/static');
define('CACHE', SUNROOT.'/data/tplcache');

//模板的存放目录
$cfg_df_style = "default";
$cfg_templets_dir = SUNTPL;
$cfg_tplcache_dir = CACHE;
//other
$cfg_lang = 'utf8';
$cfg_site = 'http://wx.xx.com';
$cfg_api ='http://api.xx.com';
$cfg_mediasurl='/static/upload';
$cfg_cmspath = '/';
$app_url = '/index.html?page=';

$cfg_cookie = 'G_N_T_K';
$cookie_encode = 'Genetalks';
$sms_url='http://service.winic.org/sys_port/gateway/';
$sms_arg=array('id'=>"vnsoft",'pwd'=>"gamedemorhwl",'to'=>'','content'=>'','time'=>0);
$sms_tip=' 验证码请在30分钟内填写! ';


/*定义微信公众号参数*/
define('APPID', "");
define('SECRET', "");
define('TOKEN', "");

date_default_timezone_set('Asia/Shanghai');

//数据库连接信息
require_once(SUNINC.'/db.inc.php');

//全局常用函数
require_once(SUNINC.'/common.func.php');


//导入数据控制
require_once(SUNINC.'/sunpdo.class.php');
//require_once(SUNINC.'/sunsql.class.php');

// 模块MVC框架需要的控制器和模型基类
require_once(SUNINC.'/control.class.php');
require_once(SUNINC.'/model.class.php');

require_once(SUNINC."/Block.php");

//载入小助手配置,并对其进行默认初始化
if(file_exists(DATA.'/helper.inc.php'))
{
    require_once(DATA.'/helper.inc.php');
    // 若没有载入配置,则初始化一个默认小助手配置
    if (!isset($cfg_helper_autoload))
    {
        $cfg_helper_autoload = array('util', 'charset', 'string', 'time', 'cookie');

    }
    // 初始化小助手
    helper($cfg_helper_autoload);
}

if(!isset($cfg_NotPrintHead)) {
    header("Content-Type: text/html; charset={$cfg_lang}");
}

//自动加载类库处理
function __autoload($classname)
{
    global $cfg_lang;
    $classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
    if( class_exists ( $classname ) )
    {
        return TRUE;
    }
    $classfile = $classname.'.php';
    $libclassfile = $classname.'.class.php';
        if ( is_file ( SUNINC.'/'.$libclassfile ) )
        {
            require SUNINC.'/'.$libclassfile;
        }
        else if( is_file ( MODEL.'/'.$classfile ) ) 
        {
            require MODEL.'/'.$classfile;
        }
        else
        {
            if (DEBUG_LEVEL === TRUE)
            {
                echo '<pre>';
				echo $classname.'类找不到';
				echo SUNINC.'/'.$libclassfile;
				echo '</pre>';
				exit ();
            }
            else
            {
                header ( "location:/404.html" );
                die ();
            }
        }
}


//系统相关变量检测
if(!isset($needFilter))
{
    $needFilter = false;
}
$registerGlobals = @ini_get("register_globals");
$isUrlOpen = @ini_get("allow_url_fopen");
$isSafeMode = @ini_get("safe_mode");
if( preg_match('/windows/i', @getenv('OS')) )
{
    $isSafeMode = false;
}


//Session保存路径
$enkey = substr(md5(substr($cfg_cookie,0,5)),0,10);
$sessSavePath = DATA."/sessions_{$enkey}";
if ( !is_dir($sessSavePath) ) mkdir($sessSavePath);

if(is_writeable($sessSavePath) && is_readable($sessSavePath))
{
    session_save_path($sessSavePath);
}

//系统配置参数
require_once(DATA."/config.cache.inc.php");

//载入小助手配置,并对其进行默认初始化
if(file_exists(DATA.'/helper.inc.php'))
{
    require_once(DATA.'/helper.inc.php');
    // 若没有载入配置,则初始化一个默认小助手配置
    if (!isset($cfg_helper_autoload))
    {
        $cfg_helper_autoload = array('util', 'charset', 'string', 'time', 'cookie');

    }
    // 初始化小助手
    helper($cfg_helper_autoload);
}

$adm_scope=array(
'archives'=>'id,typ,typeid,sendate,trade,stock,market,price,sale,score,seller,flag,code,brand,spec,weight,litpic,picture,album,origin,title,words,descr,gbody',
'arctype'=>'id,reid,sortrank,typename,typedir,tempindex,templist,temparticle,namerule,namerule2',
'member'=>'id,typ,usr,pwd,uname,address,sex,phone,exptime,money,email,scores,face',
'supplier'=>'id,typ,usr,pwd,name,phone,email,industry,account,company,agency,fund,partner,ann_sale,address',
'bill'=>'id,typ,tme,proc,usrid,tid,title,total,amount',
'comment'=>'*',
'admin'=>'typ,usr,pwd'
);

$cn_title=array('id'=>'编号','typ'=>'类型','reid'=>'分类','typeid'=>'栏目','sendate'=>'时间','code'=>'条码','flag'=>'标识','click'=>'点击','author'=>'作者','litpic'=>'图片',
'brand'=>'品牌','spec'=>'规格','weight'=>'重量','unit'=>'单位','price'=>'价格','sale'=>'优惠','title'=>'品名','words'=>'关键词','descr'=>'描述','gbody'=>'内容',
'usr'=>'账号','pwd'=>'密码','name'=>'姓名','tel'=>'电话','account'=>'余额','market'=>'网吧','area'=>'地区','street'=>'街道','address'=>'地址','company'=>'公司',
'pubdate'=>'日期','trade'=>'交易','stock'=>'库存','score'=>'积分','seller'=>'商家','rqcode'=>'二维码','album'=>'像册','origin'=>'产地');

?>
