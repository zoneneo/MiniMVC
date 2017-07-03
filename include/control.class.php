<?php
/**
 * 控制器基类
 *
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */
if(!defined('SUNINC')) exit("Request Error!");

require_once(SUNINC."/template.class.php");

class Control
{
    var $tpl;
	var $tpldir;
    var $apptpl;
	
    function __construct()
    {
        $this->Control();
    }
    
    // 析构函数
    function Control()
    {
		$this->SetTpldir();
        $this->tpl = isset($this->tpl)? $this->tpl : new SunTemplate();
    }
    
	//初如化应用模板
	function SetTpldir()
	{
		$this->tpldir=SUNTPL.'/admin';
	}
	
    //设置模板,如果想要使用模板中指定的pagesize，必须在调用模板后才调用 SetSource($sql)
    function SetTemplate($tplfile)
    {
		$this->apptpl = $this->tpldir.'/'.$tplfile;
        $this->tpl->LoadTemplate($this->apptpl);  
    }
    
    //设置文档相关的各种变量
    function SetVar($k, $v)
    {
        $this->tpl->Assign($k, $v);
    }
	//获取文档相关的各种变量
    function GetVar($k)
    {
        global $_vars;
        return isset($_vars[$k]) ? $_vars[$k] : '';
    }
    
    function Model($name='')
    {
        $name = preg_replace("#[^\w]#", "", $name);
        $modelfile = SUNMODEL.'/'.$name.'.php';
        if (file_exists($modelfile))
        {
            require_once $modelfile;
        }
        if (!empty($name) && class_exists($name))
        {
            return new $name;
        } 
        return false;
    }
    
    function Libraries($name='',$data = '')
    {
		if(defined('APPNAME')) 
		{
			$classfile = 'MY_'.$name.'.class.php';
			if ( file_exists ( '../'.APPNAME.'/libraries/'.$classfile ) )
			{
				require '../'.APPNAME.'/libraries/'.$classfile;
				return new $name($data);
			}else{
				if (!empty($name) && class_exists($name))
				{
				return new $name($data);
				}
			}
			return FALSE;
		}else{
			if (!empty($name) && class_exists($name))
			{
				return new $name($data);
			}
			return FALSE;
		}
    }  
    
    //载入helper
    function helper($helper = "",$path)
    {   
        $help_path = $path.'/'.$helper.".helper.php";
        if (file_exists($help_path))
        { 
            include_once($help_path);
        }else{
            exit('Unable to load the requested file: '.$helper.".helper.php");          
        }  
    }
    
    //显示数据
    function Display()
    {
        $this->tpl->SetObject($this);
        $this->tpl->Display();
    }
    
    //保存为HTML
    function SaveTo($filename)
    {
        $this->tpl->SetObject($this);
        $this->tpl->SaveTo($filename);
    }
    
    // 释放资源
    function __destruct() {
        unset($this->tpl);
    }
}
?>
