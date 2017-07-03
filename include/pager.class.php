<?php

/**
 *  处理数据分页类
 *
 */
 
Class Pager
{
	var $PageSz;		//每页的数量
	var $CurtPage;		//当前的页数
	var $NextPage;		//下一页
	var $PrevPage;		//上一页
	var $PageSum;		//总页数
	var $Amount;		//总记录数
	var $isFst;			//首页?
	var $isLst;			//最后页?
	var $cols=10;		//新增同时例出页数,默认是10页
	
	function Pager($option)
	{
		$allow = array('PageSz','CurtPage','Amount');
		$this->_setOptions($option,$allow);
		$this->Pagination();
	}
	function Pagination()
	{
	   if ( $this->Amount > 0 )		//设置页数
	   {
		   if ( $this->Amount < $this->PageSz ){ $this->PageSum = 1; }
		   if ( $this->Amount % $this->PageSz )
		   {
			   $this->PageSum= (int)($this->Amount / $this->PageSz) + 1;
		   }else{
			   $this->PageSum = $this->Amount / $this->PageSz;
		   }
	   }else{
		   $this->PageSum = 0;
	   }
		if($this->CurtPage== 1)
		{
			$this->isFst = true;
		}else
		{
			$this->isFst = false;
		}
		if($this->CurtPage==$this->PageSum)
		{
			$this->isLst = true;
		}else
		{
			$this->isLst = false;
		}

	   if ( $this->PageSum > 1 )
	   {
		   if ( !$this->isLst ) { $this->NextPage = $this->CurtPage + 1; }
		   if ( !$this->isFst ) { $this->PrevPage = $this->CurtPage - 1; }
	   }
	  
	   return true;
	}

	function _setOptions($option,$allow)
	{
	   foreach ( $option as $key => $value )
	   {
		   if ( in_array($key, $allow) && ($value != null) )
		   {
			   $this->$key = $value;
		   }
	   }
	   return true;
	}
	function setValue($key,$value)
	{
	   $this->$key = $value;
	}
	function getValue($key)
	{
	   return $this->$key;
	}
	function PageBar($i=15)
	{
		$d=floor($i/2);
		$c=$this->CurtPage;
		$n=$this->PageSum;
		$s=$e=0;
		if($this->isFst){
			$s=1;
			$e=($n>$i)? $i : $n;
		}else if($this->isLst){
			$e=$n;
			$s=($n-$i >0)? $n-$i : 1;
		}else if($c < ($n-$c)){		//右边可分页数量大于左边,从左边开始分页
			$s=($c>$d)? $c-$d : 1;
			$e=($s+$i>$n)? $n: $s+$i;
		}else{
			$e=($c+$d > $n)? $n : $c+$d;
			$s=($e-$i > 0)? $e-$i : 1;
		}
		return array($s,$c,$e,$n);
	}
}
?>
