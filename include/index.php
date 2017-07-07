<?php
@set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(E_ALL || ~E_NOTICE);

$verMsg = ' V1.0 UTF-8';
$s_lang = 'utf-8';
$dfDbname = 'suncms';
$errmsg = '';

$insLockfile = dirname(__FILE__).'/install_lock.txt';
define('SUNINC', str_replace("\\", '/', dirname(__FILE__) ) );
define('SUNROOT', substr(SUNINC,0,strrpos(SUNINC,'/')));
define('SUNTPL', SUNROOT.'/templets');

header("Content-Type: text/html; charset={$s_lang}");

if(file_exists($insLockfile))
{
    exit(" 程序已运行安装，如果你确定要重新安装，请先从FTP中删除 install/install_lock.txt！");
}
$step=$_POST['step'];
if(empty($step))
{
    $step = 1;
}
$dbhost=$_POST['dbhost'];
$dbuser=$_POST['dbuser'];
$dbpwd=$_POST['dbpwd'];
$dbname=$_POST['dbname'];
$dbprefix=$_POST['dbprefix'];
$dblang=$_POST['dblang'];
$adminuser=$_POST['adminuser'];
$adminpwd=$_POST['adminpwd'];

/*------------------------
设置参数
function _1_WriteSeting()
------------------------*/
if($step==1)
{
    if(!empty($_SERVER['REQUEST_URI']))
    $scriptName = $_SERVER['REQUEST_URI'];
    else
    $scriptName = $_SERVER['PHP_SELF'];

    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

    if(!empty($_SERVER['HTTP_HOST']))
        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
    else
        $baseurl = "http://".$_SERVER['SERVER_NAME'];
    
    include(SUNTPL.'/install/step-1.htm');
    exit();
}
/*------------------------
普通安装
function _2_Setup()
------------------------*/
else if($step==2)
{
    /*$conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("<script>alert('数据库服务器或登录密码无效，\\n\\n无法连接数据库，请重新设定！');history.go(-1);</script>");*/
	
	$conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("db: ".$dbhost.'-'.$dbuser.'-'.$dbpwd);
    mysql_query("CREATE DATABASE IF NOT EXISTS `".$dbname."`;",$conn);
    
    mysql_select_db($dbname) or die("<script>alert('选择数据库失败，可能是你没权限，请预先创建一个数据库！');history.go(-1);</script>");

    //获得数据库版本信息
    $rs = mysql_query("SELECT VERSION();",$conn);
    $row = mysql_fetch_array($rs);
    $mysqlVersions = explode('.',trim($row[0]));
    $mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];

    mysql_query("SET NAMES '$dblang',character_set_client=binary,sql_mode='';",$conn);

    $fp = fopen(dirname(__FILE__)."/common.inc.php","r");
    $configStr1 = fread($fp,filesize(dirname(__FILE__)."/common.inc.php"));
    fclose($fp);

    //common.inc.php
    $configStr1 = str_replace("~dbhost~",$dbhost,$configStr1);
    $configStr1 = str_replace("~dbname~",$dbname,$configStr1);
    $configStr1 = str_replace("~dbuser~",$dbuser,$configStr1);
    $configStr1 = str_replace("~dbpwd~",$dbpwd,$configStr1);
    $configStr1 = str_replace("~dbprefix~",$dbprefix,$configStr1);
    $configStr1 = str_replace("~dblang~",$dblang,$configStr1);
	
    @chmod(SUNINC,0666);
    $fp = fopen(SUNINC."/db.inc.php","w") or die("<script>alert('写入配置失败，请检查".SUNINC."目录是否可写入！');history.go(-1);</script>");
    fwrite($fp,$configStr1);
    fclose($fp);
	
    if($mysqlVersion >= 4.1)
    {
        $sql4tmp = "ENGINE=MyISAM DEFAULT CHARSET=".$dblang;
    }
  
    //创建数据表
  
    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dftables.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp,1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line."\n";
            $query = str_replace('#@__',$dbprefix,$query);
            if($mysqlVersion < 4.1)
            {
                $rs = mysql_query($query,$conn);
            } else {
                if(preg_match('#CREATE#i', $query))
                {
                    $rs = mysql_query(preg_replace("#TYPE=MyISAM#i",$sql4tmp,$query),$conn);
                }
                else
                {
                    $rs = mysql_query($query,$conn);
                }
            }
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);
    
    //导入默认数据
	
	$query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dfdata.txt','r');
	while(!feof($fp))
    {
        $line = rtrim(fgets($fp, 1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line;
            $query = str_replace('#@__',$dbprefix,$query);
			$rs = mysql_query($query,$conn);
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

	include(SUNTPL.'/install/step-2.htm');
    exit();
}