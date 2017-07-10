<?php
	require_once("config.php");
	require_once("comm.fun.php");
	header("Content-Type: text/html; charset=utf-8");

	$act =basename($_SERVER['PHP_SELF']);
	$cmd=trim($_REQUEST["cmd"]);
	$tid =$_REQUEST['tid'];
	$sot =$_REQUEST['sname']; 
	
	$kys =array();
	$res =array();
	$dsql->Execute('me',"SELECT id,reid,typename FROM #@__arctype order by id DESC");
	$dsql->queryString;
	while($row=$dsql->GetArray('me'))
	{
		$kys[$row['id']]=$row['reid'];
		$res[$row['id']]=$row['typename'];
	}
	$result =GetSunIds($kys);
	foreach($result as $k)
	{
	
		if(is_numeric($k))
		{
			$cont .="<li id='$k'>[.] <a href='adm_goods.php?toi=$k'>$k ".$res[$k]."</a> ";
			$cont .="<u title='添加子类'>A </u><u title='修改类名'>U </u><u title='移动分类'>M </u> <u title='删除'>D </u></li>";
		}else
		{
			if($k=='}')
			{
				$cont .="</ul>";
			}else
			{
				$k=str_replace('{','',$k);
				$cont.="<li><span id='e$k' onclick='ShowNode($k)'>[-]</span> <a href='article.php?toi=$k' >$k ".$res[$k]."</a> ";
				$cont.="<u title='添加子类'>A </u><u title='修改类名'>U </u><u title='移动分类'>M </u> <u title='删除'>D </u></li><ul id='d$k'>";
			}
		}
	}
	$web = file_get_contents('tpl/catalog.htm');
	echo str_replace('{sun:tree/}',$cont,$web);

?>