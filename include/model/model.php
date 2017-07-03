<?php
/**
 * 模型基类
 *
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */

class store extends PDO {
    var $dbHost;
    var $dbUser;
    var $dbPwd;
    var $dbName;
    var $dbPrefix;
    var $result;
    var $queryString;
    var $isClose;
    var $safeCheck;
	var $isInit=false;

    //用外部定义的变量初始类，并连接数据库 //PDO::__construct ( string $dsn [, string $username [, string $password [, array $driver_options ]]] )
    function __construct() 
    {
		try {
			parent::__construct("mysql:host=localhost;dbname=genetk","root","12345678",array(PDO::ATTR_PERSISTENT => true));	
		}catch (PDOException $e) {
			print $e->getMessage();
		}  
    }
        
    function Execute($id="me", $sql='')
    {
        if(!empty($sql))
        {
            $this->SetQuery($sql);
        }		
		$this->result[$id]=$this->prepare($this->queryString, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$this->result[$id]->execute();
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
		
        //return $this->queryString;
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
}

?>
