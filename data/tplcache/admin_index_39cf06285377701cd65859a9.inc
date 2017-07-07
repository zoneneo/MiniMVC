<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html> 
<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<title></title>
<meta name='keywords' content=''>
<meta name='description' content=''>
<link rel="stylesheet" href="images/admin.css" type="text/css"/>
<script type="text/javascript" src="images/jquery-1.8.0.js"></script>
<script type="text/javascript" src="images/admin.js"></script>
</head>
<body style="overflow:hidden;">
<div id="Nav">
<div id="NavL">
<a href="sortree.php" target='mainFrame'>网站栏目</a>
<a href="index.php?ct=admin&ac=listing&to=arctype" target='mainFrame'>分类管理</a>
<a href="index.php?ct=admin&ac=listing&to=illness" target='mainFrame'>生信管理</a>
<a href="index.php?ct=admin&ac=listing&to=genome" target='mainFrame'>基因信息</a>
<a href="index.php?ct=admin&ac=listing&to=customer" target='mainFrame'>客户管理</a>
<a href="index.php?ct=admin&ac=listing&to=report" target='mainFrame'>检测报告</a>

</div>
<div class="sysbox lfloat" onMouseMove="sysbar(event)" onMouseOut="sysbar(event)" style="display:none"><a>系统管理</a>
<ul id="sysbar">
<li><a href="index.php?ct=admin&ac=table" target='mainFrame'>管理栏目</a></li>
<li><a href="index.php?ct=admin&ac=listing&to=admin" target='mainFrame'>管理权限</a></li>
<li><a href="tool.php" target='mainFrame'>转换工具</a></li>
</ul>
</div>
<div id="NavR"><a href="?ct=admin&ac=logout" target="_parent">安全退出</a></div>
</div>
<iframe style="width:100%;height:99%;overflow:auto" name="mainFrame" id="mainFrame" src="" frameborder="0" noresize scrolling="yes"></iframe>
</div>
</body>
<script language="javascript">
Dimensions(); //调用函数，获取数值 
window.onresize=Dimensions;
window.mainFrame.onresize=Dimensions;  
</script>
</html>