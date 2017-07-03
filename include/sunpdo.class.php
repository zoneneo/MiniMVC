<?php
if(!defined('SUNINC')) exit("Request Error!");
/**
 * 数据库类
 * 说明:系统底层数据库核心类
 * @version        
 * @package        SunSql
 * @copyright      Copyright (c) 2007 - 2010, , Inc.
 */

$dsql = $db = new SunPdo();
// 在工程所有文件中均不需要单独初始化这个类，可直接用 $dsql 或 $db 进行操作

class SunPdo extends PDO {
    var $dbHost;
    var $dbUsr;
    var $dbPwd;
    var $dbName;
    var $dbPrefix;
    var $result;
    var $queryString;
    var $safeCheck;

    //用外部定义的变量初始类，并连接数据库 //PDO::__construct ( string $dsn [, string $username [, string $password [, array $driver_options ]]] )
    function __construct() 
    {
		$this->dbHost   =  $GLOBALS['cfg_dbhost'];
        $this->dbUsr   =  $GLOBALS['cfg_dbuser'];
        $this->dbPwd    =  $GLOBALS['cfg_dbpwd'];
        $this->dbName   =  $GLOBALS['cfg_dbname'];
        $this->dbPrefix =  $GLOBALS['cfg_dbprefix'];
		$dbs="mysql:host={$this->dbHost};dbname={$this->dbName}";
		try {
			parent::__construct($dbs,$this->dbUsr,$this->dbPwd,array(PDO::ATTR_PERSISTENT => true));	
		}catch (PDOException $e) {
			print $e->getMessage();
		}  
    }

    function SunPdo()
    {
        $this->__construct();
    }

    function Execute($id="me", $sql='')
    {
        if(!empty($sql))
        {
            $this->SetQuery($sql);
        }	
		
        //SQL语句安全检查
        if($this->safeCheck)
        {
            CheckSql($this->queryString);
        }
		$t1=time();
		$this->result[$id]=$this->prepare($this->queryString, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$this->result[$id]->execute();
		
		if($this->recordLog) {
			$this->RecordLog(time() - $t1);
        }
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

    //返回当前的一条记录并把游标移向下一记录
    function GetArray($id="me")
    {
		return $this->result[$id]->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
    }

    //设置SQL语句，会自动把SQL语句里的#@__替换为$this->dbPrefix(在配置文件中为$cfg_dbprefix)
    function SetQuery($sql)
    {
        $prefix="#@__";
        $sql = str_replace($prefix,$GLOBALS['cfg_dbprefix'],$sql);
        $this->queryString = $sql;
        return $this->queryString;
    }
	
	//获取数据库所有表
	function GetDBTables($id='me')
	{
		$this->result[$id] = @$this->query("show tables");	
	}
	
    function GetTabFields($tname){	//不完整的表名请带上前缀#@__
		$arr=array();
		$this->Execute('fd',"SHOW COLUMNS FROM ".$tname);
		while($row=$this->GetArray('fd'))
		{
			$arr[]=$row['Field'];
		}
		return $arr;
	}
	function GetTotalRow($id=me){
		return count($this->result[$id]->fetchall());
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
        $log_file = DATA.'/'.md5($cfg_cookie_encode).'_safe.txt';
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