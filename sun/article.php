<?php
require_once('common.inc.php');
require_once('pager.php');


session_start();
header("Content-Type: text/html; charset=utf-8");

$tab ='#@__archives';
$act =basename($_SERVER['PHP_SELF']);
if(empty($_SESSION['USERLEVEL'])){
	echo "<h1 style='color:#e00'>你的权限不够！</h1>";
	exit(0);
}

if(isset($_REQUEST['toi'])){
	$_SESSION['CLASSID']=$_REQUEST['toi'];
}
$tid =$_SESSION['CLASSID'];

$content='';
$cmd = isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : '';

//$fdcn=empty(${$tab.'_cn'}) ? $dsql->getFields($tab,'cc') : ${$tab.'_cn'};
$pgz=60;
if($cmd=='ins'||$cmd=='ups')
{
	$content= showRecord($tab,$_REQUEST['id'],$cmd,$tid);		
}
else
{
	if($cmd=='del')
	{
		$dsql->extDSQL($tab,$_REQUEST['id'],'id');
	}else if($cmd=='dls')
	{
		$dsql->Query("DELETE FROM $tab WHERE id in ('".implode("','",$_POST['ids'])."')");
	}
	else if($cmd=='up')
	{
		loadimage($cfg_media_dir,ATTACHED);
		if(!empty($_POST['tme'])&&!is_numeric($_POST['tme'])){
			$_POST['tme'] = strtotime($_POST['tme']);
		}
		$dsql->extUSQL($tab,$_REQUEST['id']);
	}
	else if($cmd=='in')
	{
		loadimage($cfg_media_dir,ATTACHED);
		if(empty($_POST['tme'])){
			$_POST['tme'] = time();
		}else if(!is_numeric($_POST['tme'])){
			$_POST['tme'] = strtotime($_POST['tme']);
		}
		$dsql->extISQL($tab);
	}	
	$content= SubjectList($tab,$tid);
}	
	


function SubjectList($tab,$rid)
{
	global $dsql,$act;
	$pgz =20;
	$pag =empty($_REQUEST['pag']) ? 1:  abs(intval($_REQUEST['pag'])) ;
	$cont = "<div align='left'><div class='actbar'><form name='frm' action='$act' method='post'><input type='hidden' name='cmd' value='dls'>";
	$cont.="<input type='image' src='images/drop.png'>删除选项</div>\r\n";
	$cont .="<table><tr><td>[&radic;]</td><td>操作</td><td>编号</td><td>类型</td><td>标题</td><td>时间</td></tr>\r\n";
	date_default_timezone_set('Asia/Shanghai');

	$dsql->Execute('me',"SELECT * FROM `#@__archives` WHERE rid='$rid' LIMIT ".(intval($pag)-1)*intval($pgz).",$pgz");
	while($row = $dsql->GetArray('me'))
	{
		$tme = empty($row['tme'])? '' : date('Y-m-d H:i',$row['tme']);
		$cont .= "<tr><td><input type='checkbox' name='ids[]' value='".$row['id']."'></td><td>\r\n";
		$cont .= "<a href='$act?cmd=ins&id=".$row['id']."' title='新建'><img src='images/insrow.png' alt='新建'></a>\r\n";
		$cont .= "<a href='$act?cmd=ups&id=".$row['id']."' title='编辑'><img src='images/edit.png' alt='编辑'></a>\r\n";
		$cont .= "<a href='$act?cmd=del&id=".$row['id']."' title='删除'><img src='images/drop.png' alt='删除'></a></td>\r\n";				
		$cont .= "<td>&nbsp;".$row['id']."</td>";
		$cont .= "<td>&nbsp;".$row['flag']."</td>";
		$cont .= "<td>&nbsp;".$row['title']."</td>";
		$cont .= "<td>&nbsp;".$tme."</td></tr>\r\n";
	}
	$cont.='</form></table></div>';

	$dsql->Execute('dd',"SELECT * FROM `#@__archives` WHERE rid='$rid'");
	$num=$dsql->GetTotalRow('dd');

	$pset=array("PageSz" =>$pgz,"CurtPage" =>$pag,"Amount"=>$num);
	$pager=new Pager($pset);
	list($s,$c,$e,$n)=$pager->PageBar(15);
	$bar="<div id='pagbar'><a href='$act?pag=1' class='genr'>1</a> &lt;&lt; ";
	for($i=$s;$i<$e+1;$i++){
		$css= ($i==$c) ? 'active' : 'genr';
		$bar .="<a href='$act?pag=$i' class='$css'>$i</a>";
	}
	$bar .=" &gt;&gt; <a href='$act?pag=$n' class='genr'>$n</a></div>";
	return $cont.$bar;
	
}

function showRecord($tab,$key,$opt)
{
	global $dsql,$act;
	$web = file_get_contents('article.htm');
	$web =str_replace('{act/}',$act,$web);
	$res=$dsql->getOne("select * from $tab where id=$key");
	if($opt=='ups'){
		$web =str_replace('{cmd/}','up',$web);
		$web =str_replace('{button/}','更新',$web);
	}else{
		$web =str_replace('{cmd/}','in',$web);
		$web =str_replace('{button/}','新建',$web);
		$res['id']='';
	}	
	foreach($res as $kk=>$vv)
	{
		$web =str_replace('{'.$kk.'/}',$vv,$web);
	}
	return $web;
}

function loadimage($url_,$dir_){
	if(isset($_FILES['loadimg'])){
		if(is_uploaded_file($_FILES['loadimg']['tmp_name'])){
			$suffix=substr($_FILES['loadimg']['name'], strrpos($_FILES['loadimg']['name'], '.'));
			$str=basename($_POST['litpic']);
			$picn=empty($str) ? time().$suffix : $str;
			move_uploaded_file($_FILES['loadimg']['tmp_name'],$dir_.$picn);
			$_POST['litpic']=$url_.'/'.$picn;
		}
	}
}
 
?>
<HTML><HEAD>
<TITLE>企业网站管理系统后台</TITLE>
<link rel="stylesheet" href="images/CssAdmin.css">
	<link rel="stylesheet" href="../themes/default/default.css" />
	<link rel="stylesheet" href="../plugins/code/prettify.css" />
	<script charset="utf-8" src="../kindeditor.js"></script>
	<script charset="utf-8" src="../lang/en.js"></script>
	<script charset="utf-8" src="../plugins/code/prettify.js"></script>
	<script>
		KindEditor.ready(function(K) {
			var editor1 = K.create('textarea[name="content"]', {
				cssPath : '../plugins/code/prettify.css',
				uploadJson : 'upload_json.php',
				fileManagerJson : 'file_manager_json.php',
				allowFileManager : true,
				afterCreate : function() {
					var self = this;
					K.ctrl(document, 13, function() {
						self.sync();
						K('form[name=example]')[0].submit();
					});
					K.ctrl(self.edit.doc, 13, function() {
						self.sync();
						K('form[name=example]')[0].submit();
					});
				}
			});
			prettyPrint();
		});
	</script>
</HEAD>
<body>
<div style="width:90%; margin:25px auto;">
<?php echo $content; ?>
</div>
</body>
</HTML>