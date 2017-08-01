<?php
	session_start();
	require_once('../include/config.inc.php');
	require_once(DATA."/cache/inc_catalog_base.inc");
	require_once(SUNINC."/channelunit.func.php");
	require_once(SUNINC."/list.class.php");
	require_once(SUNINC."/archives.class.php");
	require_once(SUNINC."/tree.class.php");



	header("Content-Type: text/html; charset=utf-8");
	// if($_SESSION['USERNAME']==''){
	// 	echo "<h1>权限不够请登录！</h1>";
	// 	exit(0);
	// }
	
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
	$cmd=isset($_REQUEST["cmd"])?$_REQUEST["cmd"] :'';

	$tid =$_REQUEST['tid'];
	$sot =$_REQUEST['sname']; 
	if($cmd=="make"){
		$tid = $_REQUEST["tid"];

		$lv = new ListView($tid);
		$position= MfTypedir($lv->Fields['typedir']);
		$lv->CountRecord();
		$ntotalpage = $lv->TotalPage;
		$reurl = $lv->MakeHtml(1, 0, 0);
		//$lv->Display();

		// $vs = array();
		// $vs = GetParentIds($tid);
		// foreach($vs as $k=>$v)
		// {
		// 	$dsql->Execute('dd',"SELECT id FROM #@__archives WHERE typeid ='".$v."' ");
		// 	echo $dsql->queryString;
		// 	while ($row = $dsql->GetArray('dd')) {
		// 		$aid=$row['id'];
		// 		$ac = new Archives($aid);
		// 		$rurl = $ac->MakeHtml(0);
		// 		echo "生成文档: <a herf='{$rurl}'> {$rurl} </a>";
		// 	}
		// }


		$dsql->Execute('dd',"SELECT id FROM #@__archives WHERE typeid='{$tid}'");
		while($row = $dsql->GetArray('dd')) {
			$aid=$row['id'];
			$ac = new Archives($aid);
			$rurl = $ac->MakeHtml(0);
			echo "<br>生成文档: <a herf='{$rurl}'> {$rurl} </a>";
		}
		exit();	
	}
	
	$cont='';
	$result =$bjt->Stack($kys,1);
	foreach($result as $k)
	{
	
		if(is_numeric($k))
		{
			$cont .="<h3><b>$k . <a href='{$act}?cmd=make&tid=$k'> ".$res[$k]." </a></b></h3>";
		}else
		{
			if($k=='}')
			{
				$cont .="</div>";
			}else
			{
				$k=str_replace('{','',$k);
				$cont .="<h3><b onclick='ShowNode($k)'>$k [<span id='e$k'>-</span>] <a href='{$act}?cmd=make&tid=$k'>  ".$res[$k]."</a></b>";
				$cont .="</h3><div id='d$k'>";
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