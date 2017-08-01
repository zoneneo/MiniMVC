<?php
	//require_once('config.php');
	session_start();
	header("Content-Type: text/html; charset=utf-8");

	$tab ='#@__archives';


	if(isset($_REQUEST['toi'])){
		$_SESSION['CLASSID']=$_REQUEST['toi'];
	}
	$tid =$_SESSION['CLASSID'];

	$content='';
	$cmd = isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : '';


	$web = file_get_contents('article.htm');
	$web =str_replace('{act/}',$act,$web);
	$res=$dsql->getOne("select * from $tab where id=$key");		
 function showRecord($tab,$key,$opt)
{
	global $dsql,$act;

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
<textarea name="content" style="width:900px;height:300px;">{content/}</textarea>
</div>
</body>
</HTML>