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


//获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项

$isUrlOpen = @ini_get('allow_url_fopen');
$Nowurl = $s_scriptName = '';
$Nowurl = GetCurUrl();
$Nowurls = explode('?', $Nowurl);
$s_scriptName = $Nowurls[0];

$cfg_remote_site = empty($cfg_remote_site)? 'N' : $cfg_remote_site;

//检验用户登录状态

// $cuserLogin = new userLogin();


// $cache1 = DATA.'/cache/inc_catalog_base.inc';
// if(!file_exists($cache1)) UpDateCatCache();
// $cacheFile = DATA.'/cache/admincat_'.$cuserLogin->userID.'.inc';
// if(file_exists($cacheFile)) require_once($cacheFile);




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


helper('cache');



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


?>