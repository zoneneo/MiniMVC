<?php

/**
 *  处理数据分页类
 *
 */
 
Class PageList
{
	var $PageSize;		//每页的数量
	var $PageNo;		//当前的页数
	var $NextPage;		//下一页
	var $PrevPage;		//上一页
	var $PageSum;		//总页数
	var $TotalResult;	//总记录数
	var $PageBar;
	
	function __construct($num,$pgno,$pgsz,$listitem)
	{
		$this->PageList($num,$pgno,$pgsz,$listitem);
	}

	function PageList($num,$pgno,$pgsz,$listitem)
	{
		$this->PageNo=$pgno;
		$this->PageSize=$pgsz;
		$this->TotalResult=$num;
		$tname=$GLOBALS['DB_Table_Name'];
		$prepage = $nextpage = '';
		$prepagenum = $this->PageNo-1;
		$nextpagenum = $this->PageNo+1;
		if($list_len=='' || preg_match("/[^0-9]/", $list_len))
		{
		    $list_len=3;
		}
		$totalpage = ceil($this->TotalResult/$this->PageSize);
		if($totalpage<=1 && $this->TotalResult>0)
		{
		    return "<li><span class=\"pageinfo\">共 1 页/".$this->TotalResult." 条记录</span></li>\r\n";
		}
		if($this->TotalResult == 0)
		{
		    return "<li><span class=\"pageinfo\">共 0 页/".$this->TotalResult." 条记录</span></li>\r\n";
		}
		$maininfo = "<li><span class=\"pageinfo\">共 <strong>{$totalpage}</strong>页<strong>".$this->TotalResult."</strong>条</span></li>\r\n";

		$purl = '';

		$geturl = "ct=admin&ac=table&to=$tname&TotalResult=".$this->TotalResult."&";
		$purl .= '?'.$geturl;

		$optionlist = '';

		//获得上一页和下一页的链接
		if($this->PageNo != 1)
		{
		    $prepage.="<li><a href='".$purl."PageNo=$prepagenum'>上一页</a></li>\r\n";
		    $indexpage="<li><a href='".$purl."PageNo=1'>首页</a></li>\r\n";
		}
		else
		{
		    $indexpage="<li><a>首页</a></li>\r\n";
		}
		if($this->PageNo!=$totalpage && $totalpage>1)
		{
			$nextpage.="<li><a href='".$purl."PageNo=$nextpagenum'>下一页</a></li>\r\n";
			$endpage="<li><a href='".$purl."PageNo=$totalpage'>末页</a></li>\r\n";
		}
		else
		{
			$endpage="<li><a>末页</a></li>\r\n";
		}


		//获得数字链接
		$listdd="";
		$total_list = $list_len * 2 + 1;
		if($this->PageNo >= $total_list)
		{
			$j = $this->PageNo-$list_len;
			$total_list = $this->PageNo+$list_len;
			if($total_list>$totalpage)
			{
				$total_list=$totalpage;
			}
		}
		else
		{
			$j=1;
			if($total_list>$totalpage)
			{
			    $total_list=$totalpage;
			}
		}
		for($j;$j<=$total_list;$j++)
		{
			if($j==$this->PageNo)
			{
			    $listdd.= "<li class=\"thisclass\"><a>$j</a></li>\r\n";
			}
			else
			{
			    $listdd.="<li><a href='".$purl."PageNo=$j'>".$j."</a></li>\r\n";
			}
		}

		$plist = '';
		if(preg_match('/index/i', $listitem)) $plist .= $indexpage;
		if(preg_match('/pre/i', $listitem)) $plist .= $prepage;
		if(preg_match('/pageno/i', $listitem)) $plist .= $listdd;
		if(preg_match('/next/i', $listitem)) $plist .= $nextpage;
		if(preg_match('/end/i', $listitem)) $plist .= $endpage;
		if(preg_match('/option/i', $listitem)) $plist .= $optionlist;
		if(preg_match('/info/i', $listitem)) $plist .= $maininfo;
		$this->PageBar = $plist;
	}

	function GetPageBar(){
		return $this->PageBar;
	}

}
?>
