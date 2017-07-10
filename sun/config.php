<?php
/**
 * 管理目录配置文件
 *
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */
require_once('../include/config.inc.php');
require_once(SUNINC.'/userlogin.class.php');
define('SUNADMIN', str_replace("\\", '/', dirname(__FILE__) ) );
header('Cache-Control:private');

$dsql->safeCheck = FALSE;
//$dsql->SetLongLink();
$cfg_admin_skin = 1; // 后台管理风格

if(file_exists(DATA.'/admin/skin.txt'))
{
	$skin = file_get_contents(DATA.'/admin/skin.txt');
	$cfg_admin_skin = !in_array($skin, array(1,2,3,4))? 1 : $skin;
}

//获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项

$isUrlOpen = @ini_get('allow_url_fopen');
$Nowurl = $s_scriptName = '';
$Nowurl = GetCurUrl();
$Nowurls = explode('?', $Nowurl);
$s_scriptName = $Nowurls[0];

$cfg_remote_site = empty($cfg_remote_site)? 'N' : $cfg_remote_site;

//检验用户登录状态

$cuserLogin = new userLogin();
// if($cuserLogin->getUserID()==-1)
// {
//     header("location:login.php?gotopage=".urlencode($Nowurl));
//     exit();
// }


function XSSClean($val)
{

    if (is_array($val))
    {
        while (list($key) = each($val))
        {
            if(in_array($key,array('tags','body','sun_fields','sun_addonfields','dopost','introduce'))) continue;
            $val[$key] = XSSClean($val[$key]);
        }
        return $val;
    }
    return RemoveXss($val);
}

if($cfg_sun_log=='Y')
{
    $s_nologfile = '_main|_list';
    $s_needlogfile = 'sys_|file_';
    $s_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    $s_query = isset($sunNowurls[1]) ? $sunNowurls[1] : '';
    $s_scriptNames = explode('/', $s_scriptName);
    $s_scriptNames = $s_scriptNames[count($s_scriptNames)-1];
    $s_userip = GetIP();
    if( $s_method=='POST' || (!preg_match("#".$s_nologfile."#i", $s_scriptNames) && $s_query!='') || preg_match("#".$s_needlogfile."#i",$s_scriptNames) )
    {
        $inquery = "INSERT INTO `#@__log`(adminid,filename,method,query,cip,dtime)
             VALUES ('".$cuserLogin->getUserID()."','{$s_scriptNames}','{$s_method}','".addslashes($s_query)."','{$s_userip}','".time()."');";
        $dsql->ExecuteNoneQuery($inquery);
    }
}

//启用远程站点则创建FTP类
if($cfg_remote_site=='Y')
{
    require_once(SUNINC.'/ftp.class.php');
    if(file_exists(DATA."/cache/inc_remote_config.php"))
    {
        require_once DATA."/cache/inc_remote_config.php";
    }
    if(empty($remoteuploads)) $remoteuploads = 0;
    if(empty($remoteupUrl)) $remoteupUrl = '';
    $config = array(
      'hostname' => $GLOBALS['cfg_ftp_host'],
      'username' => $GLOBALS['cfg_ftp_user'],
      'password' => $GLOBALS['cfg_ftp_pwd'],
      'debug' => 'TRUE'
    );
    $ftp = new FTP($config); 
    //初始化FTP配置
    if($remoteuploads==1){
        $ftpconfig = array(
            'hostname'=>$rmhost, 
            'port'=>$rmport,
            'username'=>$rmname,
            'password'=>$rmpwd
        );
    }
}

//管理缓存、管理员频道缓存

$cache1 = DATA.'/cache/inc_catalog_base.inc';
if(!file_exists($cache1)) UpDateCatCache();
$cacheFile = DATA.'/cache/admincat_'.$cuserLogin->userID.'.inc';
if(file_exists($cacheFile)) require_once($cacheFile);


//更新服务器

//require_once (DATA.'/admin/config_update.php');

// if(strlen($cfg_cookie_encode)<=10)
// {
//     $chars='abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
//     $hash='';
//     $length = rand(28,32);
//     $max = strlen($chars) - 1;
//     for($i = 0; $i < $length; $i++) {
//         $hash .= $chars[mt_rand(0, $max)];
//     }
// 	$dsql->ExecuteNoneQuery("UPDATE `#@__sysconfig` SET `value`='{$hash}' WHERE varname='cfg_cookie_encode' ");
// 	/*
// 	$configfile = DATA.'/config.cache.inc.php';
//     if(!is_writeable($configfile))
//     {
//         echo "配置文件'{$configfile}'不支持写入，无法修改系统配置参数！";
//         exit();
//     }*/
	
//     $fp = fopen($configfile,'w');
//     flock($fp,3);
//     fwrite($fp,"<"."?php\r\n");
//     $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC ");
//     $dsql->Execute();
//     while($row = $dsql->GetArray())
//     {
//         if($row['type']=='number')
//         {
//             if($row['value']=='') $row['value'] = 0;
//             fwrite($fp,"\${$row['varname']} = ".$row['value'].";\r\n");
//         }
//         else
//         {
//             fwrite($fp,"\${$row['varname']} = '".str_replace("'",'',$row['value'])."';\r\n");
//         }
//     }
//     fwrite($fp,"?".">");
//     fclose($fp);
// }



/**
 *  更新栏目缓存
 *
 * @access    public
 * @return    void
 */
 
 
function UpDateCatCache()
{
    global $dsql, $cfg_multi_site, $cache1, $cacheFile, $cuserLogin;
    $cache2 = DATA.'/cache/channelsonlist.inc';
    $cache3 = DATA.'/cache/channeltoplist.inc';
    $dsql->SetQuery("SELECT id,reid,typename FROM `#@__arctype`");
    $dsql->Execute();
    $fp1 = fopen($cache1,'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$cfg_Cs;\r\n\$cfg_Cs=array();\r\n";
    fwrite($fp1,$fp1Header);
    while($row=$dsql->GetObject())
    {
        // 将typename缓存起来
        $row->typename = base64_encode($row->typename);
        fwrite($fp1,"\$cfg_Cs[{$row->id}]=array({$row->reid},'{$row->typename}');\r\n");
    }
    fwrite($fp1, "{$phph}>");
    fclose($fp1);
    //$cuserLogin->ReWriteAdminChannel();
    @unlink($cache2);
    @unlink($cache3);
}


// 清空选项缓存


function ClearOptCache()
{
    $tplCache = DATA.'/tplcache/';
    $fileArray = glob($tplCache."inc_option_*.inc");
    if (count($fileArray) > 1)
    {
        foreach ($fileArray as $key => $value)
        {
            if (file_exists($value)) unlink($value);
            else continue;
        }
        return TRUE;
    }
    return FALSE;
}

/**
 *  更新会员模型缓存
 *
 * @access    public
 * @return    void
 */
 
function UpDateMemberModCache()
{
    global $dsql;
    $cachefile = DATA.'/cache/member_model.inc';

    $dsql->SetQuery("SELECT * FROM `#@__member_model` WHERE state='1'");
    $dsql->Execute();
    $fp1 = fopen($cachefile,'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$_MemberMod;\r\n\$_MemberMod=array();\r\n";
    fwrite($fp1,$fp1Header);
    while($row=$dsql->GetObject())
    {
        fwrite($fp1,"\$_MemberMod[{$row->id}]=array('{$row->name}','{$row->table}');\r\n");
    }
    fwrite($fp1,"{$phph}>");
    fclose($fp1);
}

/**
 *  引入模板文件
 *
 * @access    public
 * @param     string  $filename  文件名称
 * @param     bool  $isabs  是否为管理目录
 * @return    string
 */
function sunInclude($filename, $isabs=FALSE)
{
    return $isabs ? $filename : SUNADMIN.'/'.$filename;
}

/**
 *  获取当前用户的ftp站点
 *
 * @access    public
 * @param     string  $current  当前站点
 * @param     string  $formname  表单名称
 * @return    string
 */
 
// function GetFtp($current='', $formname='')
// {
//     global $dsql;
//     $formname = empty($formname)? 'serviterm' : $formname;
//     $cuserLogin = new userLogin();
//     $row=$dsql->GetOne("SELECT servinfo FROM `#@__multiserv_config`");
//     $row['servinfo']=trim($row['servinfo']);
//     if(!empty($row['servinfo'])){
//         $servinfos = explode("\n", $row['servinfo']);
//         $select="";
//         echo '<select name="'.$formname.'" size="1" id="serviterm">';
//         $i=0;
//         foreach($servinfos as $servinfo){
//             $servinfo = trim($servinfo);
//             list($servname,$servurl,$servport,$servuser,$servpwd,$userlist) = explode('|',$servinfo);
//             $servname = trim($servname);
//             $servurl = trim($servurl);
//             $servport = trim($servport);
//             $servuser = trim($servuser);
//             $servpwd = trim($servpwd);
//             $userlist = trim($userlist);   
//             $checked = ($current == $i)? '  selected="selected"' : '';
//             if(strstr($userlist,$cuserLogin->getUserName()))
//             {
//                 $select.="<option value='".$servurl.",".$servuser.",".$servpwd."'{$checked}>".$servname."</option>";  
//             }
//             $i++;
//         }
//         echo  $select."</select>";
//     }
// }
// helper('cache');


/**
 *  根据用户mid获取用户名称
 *
 * @access    public
 * @param     int  $mid   用户ID
 * @return    string
 */
if(!function_exists('GetMemberName')){
	function GetMemberName($mid=0)
	{
		global $dsql;
		$rs = GetCache('memberlogin', $mid);
		if( empty($rs) )
		{
			$rs = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}' ");
			SetCache('memberlogin', $mid, $rs, 1800);
		}
		return $rs['uname'];
	}
}

function GetCurUrl()
{
	if(!empty($_SERVER["REQUEST_URI"]))
	{
		$nowurl = $_SERVER["REQUEST_URI"];
		$nowurls = explode("?",$nowurl);
		$nowurl = $nowurls[0];
	}
	else
	{
		$nowurl = $_SERVER["PHP_SELF"];
	}
	return $nowurl;
}
if (!function_exists('test'))
{
	require_once("testing.php");
}
function MyReduce($v, $w)
{
	$v .= "$w ='@".$w."',";
	return $v;
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