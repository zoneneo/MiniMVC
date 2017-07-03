<?php
/*
 *	tree.class.php
 *	Trees Class	 PHP Program
 *	date:	2012-12-3
 *	Ver:	3.0
 */

class SortTree{
	var $kys;
	var $tre;
	
	function getTree($kys,$k)
	{
		$this->kys=$kys;
		$this->tre=array();
		$this->recurSort($k,$kys[$k]);
		return $this->tre;
	}
	
	function recurSort($kv,$vv)
	{
		if($kv==$vv) return 0;
		$this->tre[$kv]=$this->kys[$kv];
		$ckys=array_diff_key($this->kys,$this->tre);
		if($ky=array_search($kv,$ckys))	//搜索数组值，返回键或false
		{
			$this->recurSort($ky,$vv);
		}else{
			$this->recurSort($this->kys[$kv],$vv);
		}
	}
	function Stack($kys,$id)
	{
		$rul =array($id);
		$tre =array();
		while(count($rul))	//查询尺子长度为度退出
		{
			$s='';
			$ky=array_pop($rul);
			if(is_numeric($ky))
			{
				if($arr=array_keys($kys,$ky))
				{
					array_push($rul,'}');
					$rul=array_merge($rul,$arr);
					$s='{';
				}
			}					
			array_push($tre,$s.$ky);
		}
		return $tre;
	}
}
?>