<?php   if(!defined('SUNINC')) exit("Request Error!");
/**
 * 模型基类
 *
 * @version        
 * @package        
 * @copyright      Copyright (c) 2016 - , Inc.
 */

class Model
{
    var $dsql;
    var $db;
    
    // 析构函数
    function Model()
    {
        global $dsql;
        $this->dsql = $this->db = isset($dsql)? $dsql : new SunSql();
    }
}
?>
