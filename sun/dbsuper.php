<?php
	require_once("config.php");
	header("Content-Type: text/html; charset=utf-8");
	session_start();
	global $dsql;
	$sel=basename($_SERVER['PHP_SELF']);
	//$tabs=array();
	$act=isset($_REQUEST['act'])? $_REQUEST['act']:'';

	if($act=='mang')
	{
		if(!empty($_REQUEST['ton'])){$_SESSION['SUPERTABLE']=$_REQUEST['ton'];}
		$tab=$_SESSION['SUPERTABLE'];
		$cmd=$_REQUEST['cmd'];
		$fds=array();
		$dsql->Execute('fd',"SHOW COLUMNS FROM $tab");	
		while($row=$dsql->GetArray('fd'))
		{
			$fds[]=$row['Field'];
		}
		if($cmd=='edt'){
			$id=$_REQUEST['id'];
			$row=$dsql->GetOne("SELECT * FROM $tab WHERE id='$id'");
			$dsql->GetTableFields($tab,'fd');	
			$cont = "<ul id='article'><form action='$sel' method='post'>";
			foreach($fds as $fn){
				$cont .= "<li><b>$fn</b><input type='text' name='$fn' id='$fn' value='".$row[$fn]."'></li>";		
			}
			$cont.="<li><input type='hidden' name='act' value='mang'><input type='hidden' name='cmd' value='inn'><input type='submit' name='insert' value='复制' class='btn'>";
			$cont.="<input type='submit' name='update' value='修改' class='btn'></li></form></ul>";
		}
		else if($cmd=='del')
		{
			$dsql->Query("DELETE FROM $tab WHERE id='".$_REQUEST['id']."'");
		}
		else if($cmd=='inn')
		{
			if(isset($_REQUEST['update'])){
			$dsql->extUSQL($tab,$_REQUEST['id']);	
			}else{
			$dsql->extISQL($tab);
			}
		}
		if(empty($cont)){
			require_once(SUNINC.'/pager.class.php');
			$sql ="SELECT * FROM $tab";		
			$dsql->Execute("me",$sql);
			$num =$dsql->GetTotalRow("me");
			$pgz =30;
			$pag =intval($_REQUEST['pag']);
			$pag =($pag<1) ? 1:$pag;
			$pset=array("PageSz" =>$pgz,"CurtPage" =>$pag,"Amount"=>$num);
			$pager=new Pager($pset);
			list($s,$c,$e,$n)=$pager->PageBar(15);
			$bar="<a href='$acf?act=".$act."&pag=1' class='genr'>1</a> &lt;&lt; ";
			for($i=$s;$i<$e+1;$i++){
				$css= ($i==$c) ? 'crnt' : 'genr';
				$bar .="<a href='$acf?act=".$act."&pag=$i' class='$css'>$i</a>";
			}
			$bar .=" &gt;&gt; <a href='$acf?act=".$act."&pag=$n' class='genr'>$n</a>";			
			$cont ="<div id='article'><table><tr><th colspan='2' width='60'>operate</th>\r\n";
			foreach($fds as $fn){$cont .= "<th> $fn </th>";}
			$cont .="</tr>\r\n";

			$sar=($pag-1)*$pgz;
			$dsql->Execute("dd",$sql." LIMIT $sar,$pgz");
			while($row = $dsql->GetArray('dd'))
			{
				$cont .="<tr><td><a href='$sel?act=mang&cmd=edt&id=".$row['id']."' title='Edit'><img src='images/edit.png'></a></td>";
				$cont .="<td><a href='$sel?act=mang&cmd=del&id=".$row['id']."' title='Delete'><img src='images/drop.png'></a></td>";	
				foreach($fds as $fd)
				{
					$cont .= empty($row[$fd])?"<td>&nbsp;</td>" : "<td>".$row[$fd]."</td>";
				}
				$cont .="</tr>";
			}
			$cont.="</table></div>";
			$cont.=$bar;	
		}
	}
	else if($act=='list')
	{
		$dsql->GetDBTables('aa');
		$cont ="<ul>";	
		while($arr=$dsql->GetArray('aa',MYSQL_NUM)){
			$str=implode(",",$dsql->GetTabFields($arr[0]));
			$cont .="<li><a href='$sel?act=mang&ton=".$arr[0]."'>".$arr[0]."</a> [ $str ]</li>";
		}
		$cont .="</ul>";
	}
	else if($act=='query')
	{
		if(isset($_FILES['sqltxt'])){
			$filename=$_FILES['sqltxt']['tmp_name'];
			$source='';
			$cont='<h5>sql文件:'.$_FILES['sqltxt']['name'].'</h5>';
			$fp = @fopen($filename, "r");
			while($line = fgets($fp,1024))
			{
				$source .= $line;
			}
			fclose($fp);
			
			$arr=explode(";",$source);
			foreach($arr as $sql){
				if($dsql->Query($sql)) $cont.='<p><font color="#FF0000">ok! </font>'.$sql.'</p>';
				else $cont.='<p><font color="#FF0000">ok! </font>'.$sql.'</p>';
			}
			
		}
	}
	else if($act=='exec')
	{
		if(!empty($_REQUEST['sqlcmd'])){
			$sql=$_REQUEST['sqlcmd'];
			if(preg_match('/select/i',$sql))
			{
				$cont='';
				$dsql->Execute('dd',$sql);
				while($row=$dsql->GetArray('dd',MYSQL_NUM))
				{
					$cont .='<p>'.implode(" | ",$row).'</p>';
				}		
			}
			else
			{
				$a=$dsql->Query($sql);
				if($a+1) $cont= $a.' ok! '.$sql;
				else $cont= $a.' err! '.$sql;
			}		
		}	
	}
	else if($act=='load')
	{
		$cont="<div id='sqlbox'><form name='sqlform' action='$sel' method='post' enctype='multipart/form-data'><input type='hidden' name='act' value='query'><input type='file' name='sqltxt' value=''>　<input type='submit' value='导入SQL文件' class='btn'></form></div><br>\n";
		
	}
	else if($act=='show')
	{
		$cont="<div id='sqlbox'><form name='sqlform' action='$sel' method='post'><textarea name='sqlcmd' style='width:100%;height:200px'></textarea><input type='hidden' name='act' value='exec'><input type='submit' value='执行SQL' class='btn'></form></div><br>\n";
	}

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