<?php
if(!defined('SUNINC')) exit("Request Error!");
/**
 * 数据库类
 * 说明:系统底层数据库核心类
 * @version        
 * @package        SunSql
 * @copyright      Copyright (c) 2007 - 2010, , Inc.
 */

$dsql = new SunSql(FALSE);
// 在工程所有文件中均不需要单独初始化这个类，可直接用 $dsql 进行操作

class SunSql
{
    var $linkID;
    var $dbHost;
    var $dbUser;
    var $dbPwd;
    var $dbName;
    var $dbPrefix;
    var $result;
    var $queryString;
    var $parameters;
    var $isClose;
    var $safeCheck;
    var $recordLog=false; // 记录日志到data/mysqli_record_log.inc便于进行调试
	var $isInit=false;
	var $pconnect=false;

    //用外部定义的变量初始类，并连接数据库
    function __construct($pconnect=FALSE,$nconnect=FALSE)
    {
        $this->isClose = FALSE;
        $this->safeCheck = TRUE;
		$this->pconnect = $pconnect;
        if($nconnect)
        {
            $this->Init($pconnect);
        }
    }

    function SunSql($pconnect=FALSE,$nconnect=TRUE)
    {
        $this->__construct($pconnect,$nconnect);
    }

    function Init($pconnect=FALSE)
    {
        $this->linkID = 0;
        //$this->queryString = '';
        //$this->parameters = Array();
        $this->dbHost   =  $GLOBALS['cfg_dbhost'];
        $this->dbUser   =  $GLOBALS['cfg_dbuser'];
        $this->dbPwd    =  $GLOBALS['cfg_dbpwd'];
        $this->dbName   =  $GLOBALS['cfg_dbname'];
        $this->dbPrefix =  $GLOBALS['cfg_dbprefix'];
        $this->result["me"] = 0;
        $this->Open($pconnect);
    }

    //用指定参数初始数据库信息
    function SetSource($host,$username,$pwd,$dbname,$dbprefix="sun_")
    {
        $this->dbHost = $host;
        $this->dbUser = $username;
        $this->dbPwd = $pwd;
        $this->dbName = $dbname;
        $this->dbPrefix = $dbprefix;
        $this->result["me"] = 0;
    }

    //执行一个带返回结果的SQL语句，如SELECT，SHOW等
    function Execute($id="me", $sql='')
    {
        global $dsql;
		if(!$dsql->isInit)
		{
			$this->Init($this->pconnect);
		}
        if($dsql->isClose)
        {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }
        if(!empty($sql))
        {
            $this->SetQuery($sql);
        }
		
        //SQL语句安全检查
        if($this->safeCheck)
        {
            CheckSql($this->queryString);
        }
		
        $t1 = ExecTime();
        
        $this->result[$id] = mysql_query($this->queryString,$this->linkID);
        
        if($this->recordLog) {
			$queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
        }
        
        if(!empty($this->result[$id]) && $this->result[$id]===FALSE)
        {
            $this->DisplayError(mysql_error()." <br />Error sql: <font color='red'>".$this->queryString."</font>");
        }
    }

    function Query($id="me",$sql='')
    {
        $this->Execute($id,$sql);
    }

    //执行一个SQL语句,返回前一条记录或仅返回一条记录
    function GetOne($sql='')
    {
        if(!empty($sql))
        {
            if(!preg_match("/LIMIT/i",$sql)) $this->SetQuery(preg_replace("/[,;]$/i", '', trim($sql))." LIMIT 0,1;");
            else $this->SetQuery($sql);
        }
        $this->result['one']=$this->query($this->queryString);
        return $this->result['one']->fetch(PDO::FETCH_ASSOC);
    }
	
	//获取数据库所有表
	function GetDBTables($id='me')
	{
		global $dsql;
		if(!$dsql->isInit)
		{
			$this->Init($this->pconnect);
		}
		$this->result[$id] = @mysql_list_tables($this->dbName);	
	}
	
	//获取表字段集
	function GetTabFields($tname){	//不完整的表名请带上前缀#@__
		$arr=array();
		$this->Execute('fd',"SHOW COLUMNS FROM ".$tname);
		while($row=$this->GetArray('fd'))
		{
			$arr[]=$row['Field'];
		}
		return $arr;
	}
	


    //设置SQL语句，会自动把SQL语句里的#@__替换为$this->dbPrefix(在配置文件中为$cfg_dbprefix)
    function SetQuery($sql)
    {
        $prefix="#@__";
        $sql = str_replace($prefix,$GLOBALS['cfg_dbprefix'],$sql);
        $this->queryString = $sql;
    }
	
	function RecordLog($runtime=0)
	{
		$RecordLogFile = DATA.'/mysqli_record_log.inc';
		$url = $this->GetCurUrl();
		$savemsg = <<<EOT
		SQL:{$this->queryString}Page:$urlRuntime:$runtime 
EOT;
        $fp = @fopen($RecordLogFile, 'a');
        @fwrite($fp, $savemsg);
        @fclose($fp);
	}


    

    
}


//SQL语句过滤程序，由80sec提供，这里作了适当的修改
if (!function_exists('CheckSql'))
{
    function CheckSql($db_string,$querytype='select')
    {
        global $cfg_cookie_encode;
        $clean = '';
        $error='';
        $old_pos = 0;
        $pos = -1;
        $log_file = SUNINC.'/../data/'.md5($cfg_cookie_encode).'_safe.txt';
        //$userIP = GetIP();
        //$getUrl = GetCurUrl();
		$userIP = '';
        $getUrl = '';

        //如果是普通查询语句，直接过滤一些特殊语法
        if($querytype=='select')
        {
            $notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";

            //$notallow2 = "--|/\*";
            if(preg_match("/".$notallow1."/i", $db_string))
            {
                fputs(fopen($log_file,'a+'),"$userIP||$getUrl||$db_string||SelectBreak\r\n");
                exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
            }
        }

        //完整的SQL检查
        while (TRUE)
        {
            $pos = strpos($db_string, '\'', $pos + 1);
            if ($pos === FALSE)
            {
                break;
            }
            $clean .= substr($db_string, $old_pos, $pos - $old_pos);
            while (TRUE)
            {
                $pos1 = strpos($db_string, '\'', $pos + 1);
                $pos2 = strpos($db_string, '\\', $pos + 1);
                if ($pos1 === FALSE)
                {
                    break;
                }
                elseif ($pos2 == FALSE || $pos2 > $pos1)
                {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr($db_string, $old_pos);
        $clean = trim(strtolower(preg_replace(array('~\s+~s' ), array(' '), $clean)));
        
        if (strpos($clean, '@') !== FALSE  OR strpos($clean,'char(')!== FALSE OR strpos($clean,'"')!== FALSE 
        OR strpos($clean,'$s$$s$')!== FALSE)
        {
            $fail = TRUE;
            if(preg_match("#^create table#i",$clean)) $fail = FALSE;
            $error="unusual character";
        }

        //老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
        if (strpos($clean, 'union') !== FALSE && preg_match('~(^|[^a-z])union($|[^[a-z])~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="union detect";
        }

        //发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
        elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== FALSE || strpos($clean, '#') !== FALSE)
        {
            $fail = TRUE;
            $error="comment detect";
        }

        //这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
        elseif (strpos($clean, 'sleep') !== FALSE && preg_match('~(^|[^a-z])sleep($|[^[a-z])~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="slown down detect";
        }
        elseif (strpos($clean, 'benchmark') !== FALSE && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="slown down detect";
        }
        elseif (strpos($clean, 'load_file') !== FALSE && preg_match('~(^|[^a-z])load_file($|[^[a-z])~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="file fun detect";
        }
        elseif (strpos($clean, 'into outfile') !== FALSE && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="file fun detect";
        }

        //老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
        elseif (preg_match('~\([^)]*?select~is', $clean) != 0)
        {
            $fail = TRUE;
            $error="sub select detect";
        }
        if (!empty($fail))
        {
            fputs(fopen($log_file,'a+'),"$userIP||$getUrl||$db_string||$error\r\n");
            exit("<font size='5' color='red'>Safe Alert: Request Error step 2!</font>");
        }
        else
        {
            return $db_string;
        }
    }
}