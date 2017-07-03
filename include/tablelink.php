<?php
	require_once("config.php");
	header("Content-Type: text/html; charset=utf-8");

	$dsql->GetDBTables('aa');
	$cont ="<ul>";	
	while($arr=$dsql->GetArray('aa',MYSQL_NUM)){
		$str=implode(",",$dsql->GetTabFields($arr[0]));
		$cont .="<li><a href='$sel?act=mang&ton=".$arr[0]."'>".$arr[0]."</a> [ $str ]</li>";
	}
	$cont .="</ul>";

	$menu ="<ul id='cmdmenu'><li><a href='javascript:displayone()'>输入管理表名</a></li><li><a href='$sel?act=list'>查询数据库表</a></li>
	<li><a href='$sel?act=show'>执行Sql命令</a></li><li><a href='$sel?act=load'>导入Sql文件</a></li></ul>";

?>
<html><head>
<link href='images/admin.css' rel='stylesheet' type='text/css'>
<script language='javascript'>
function displayone(){
var str=window.prompt('请输入查询表名','');
if(str!=null && str!='undefined'){
window.location.href="dbsuper.php?act=mag&tna="+str;
}}
</script>
</head><body>
<div>
<?php echo $menu; ?>
<?php echo $cont; ?>
</div>
</body>
</html> 