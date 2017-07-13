<?php
	require_once("../include/config.inc.php");
	require_once(SUNINC."/tree.class.php");
	//require_once(SUNINC."/typelink.class.php");
	header("Content-Type: text/html; charset=utf-8");
	session_start();
	if($_SESSION['USERNAME']==''){
		echo "<h1>权限不够请登录！</h1>";
		exit(0);
	}
	
	$act = basename($_SERVER['PHP_SELF']);
	$kys =array();
	$res =array();

	$dsql->Execute('me',"SELECT * FROM #@__arctype order by id DESC");
	while($row=$dsql->GetArray('me'))
	{
		$kys[$row['id']]=$row['reid'];
		$res[$row['id']]=$row['typename'];
	}
	$bjt =new SortTree();	
	$cmd=trim($_REQUEST["cmd"]);
	$tid =$_REQUEST['tid'];
	$sot =$_REQUEST['sname']; 
	if($cmd=="1")
	{
		$dsql->ExecuteNoneQuery("insert into #@__arctype (reid,typename) values ('$tid','$sot')");
		header('Location:'.$act);
		exit();	
	}
	else if($cmd=="2")
	{
		$dsql->ExecuteNoneQuery("update #@__arctype set typename='$sot' where id='$tid'");
		header('Location:'.$act);
		exit();	
	}
	else if($cmd=="3")
	{
		if(is_numeric($sot)) $dsql->ExecuteNoneQuery("update #@__arctype set reid='$sot' where id='$tid'");
		header('Location:'.$act);
		exit();	
	}	
	else if($cmd=="4")
	{
		if(is_numeric($sot)&&$tid==$sot){
		$tree=$bjt->getTree($kys,$tid);
		$ids=implode(",",array_keys($tree));
		$dsql->ExecuteNoneQuery("DELETE FROM #@__arctype WHERE id in ($ids)");
		}
		header('Location:'.$act);
		exit();	
	}
	if($cmd=='1'||$cmd=='2'||$cmd=='3'||$cmd=='4'){
		UpDateCatCache();
		header('Location:'.$act);
		exit();	
	}
	
	$cont='';
	$result =$bjt->Stack($kys,0);
	foreach($result as $k)
	{
	
		if(is_numeric($k))
		{
			$cont .="<h3><b>$k . <a href='index.php?ct=admin&ac=goods&tid=$k'> ".$res[$k]." </a></b>";
			$cont .="<span><a href='javascript:JSMgSort(1,$k);'>子类</a> <a href='javascript:JSMgSort(2,$k);'>类名</a>";
			$cont .=" <a href='javascript:JSMgSort(3,$k);'>移动</a> <a href='javascript:JSMgSort(4,$k);'> 删除</a> <a href='index.php?ct=admin&ac=recode&to=arctype&key=$k'>修改</a> </span></h3>";
		}else
		{
			if($k=='}')
			{
				$cont .="</div>";
			}else
			{
				$k=str_replace('{','',$k);
				$cont .="<h3><b onclick='ShowNode($k)'>$k [<span id='e$k'>-</span>]<a href='index.php?ct=admin&ac=goods&tid=$k' > ".$res[$k]."</a></b>";
				$cont .=" <span><a href='javascript:JSMgSort(1,$k);'>子类</a> <a href='javascript:JSMgSort(2,$k);'>类名</a>";
				$cont .=" <a href='javascript:JSMgSort(3,$k);'>移动</a> <a href='javascript:JSMgSort(4,$k);'> 删除</a>";
				$cont .=" <a href='index.php?ct=admin&ac=recode&to=arctype&key=$k'>修改</a> </span></h3><div id='d$k'>";
			}
		}
	}

?>
<html>
<head>
<title>网站栏目分类管理</title>
<link rel="stylesheet" href="images/CssAdmin.css">
<script type="text/javascript" src="images/admin.js"></script>
</head>
<body>
<div id='SiteSort'>
<?php echo $cont;?>
</div>	
</body>
</html>
 
