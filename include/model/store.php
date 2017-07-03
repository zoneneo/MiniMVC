<?php
/**
 * 模型基类
 *
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */

class Store extends PDO {
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
}

?>
