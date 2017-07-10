<?php
	require_once("config.php");
	$prefix="#@__";	
	$tname=$prefix.$_REQUEST['t'];
	$fname=$_REQUEST['f'];

	$table = str_replace($prefix,$GLOBALS['cfg_dbprefix'],$tname);
	if($dsql->IsTable($tname))
	{
		$dsql->GetTableFields($table,'me');		
		//$tplname = SUNTEMPLATE.'/'.$fname.'htm';
		$tplname = dirname(__FILE__).'\\'.$fname.'.htm';
		/*
		if(!is_writeable($tplname))
		{
			echo "模板文件'{$tplname}'不支持写入！";
			exit();
		}*/
		/*
		$fp = fopen($tplname,'w');
		flock($fp,3);
		fwrite($fp,"<html><head><title></title></head><body><ul>\r\n");
		foreach($fields as $k=>$v)
		{
			fwrite($fp,"<li><label>{$k} {$v}</label><input type='text' name='{$v}'></li>\r\n");
		}
		fwrite($fp,"</ul></body></html>");
		fclose($fp);
		*/
		$cont ="<html><head><title></title></head><body><ul>\r\n";
		$cont .="<form method='post' action='amd_".$fname.".php' name='form_$fname'>\r\n";
		while($row = $dsql->GetFieldObject('me')){
			$id=$row->name;
			$cont .="<li><label>$id </label><input id='e_$id' type='text' name='$id' value='{$row->def}'></li>\r\n";
		}
		$cont .="<button type='submit' id='btn' class='btn'>OK</button></form></body></html>";
		file_put_contents($tplname,$cont);
		echo '创建模板'.$tplname;
	}else{
		echo '表格不存在';
	}
	
?>