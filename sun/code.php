<?php
require_once('../include/common.inc.php');
//require_once('config.php');
require_once(SUNINC.'/suntag.class.php');
$tagstr=<<<EOT
{tianya:test att1='1' att2='2'}
	[field:test/]
{/tianya:test}

{tianya:my att1='1' att2='2'}
[field:test/]
{/tianya:my}
EOT;
function FuncMy(&$ctag,$att1='1',$att2='2')
{
	$dtp2 = new SunTagParse;
	$dtp2->SetNameSpace('field','[',']');
	$dtp2->LoadSource($ctag->GetInnerText());
	foreach($dtp2->CTags as $tagid=>$ctag){
		if($ctag->GetName()=='test')
		{
			$dtp2->Assign($tagid,'test tag');
		}	
	}
	$testvar=$dtp2->GetResult();
	$reval='属性1='.$att1;
	$reval.='属性2='.$att2;
	return '处理这个标签的函数'.$reval.',解析底层模板'.$testvar;
}

$dtp = new SunTagParse;
$dtp->SetNameSpace('tianya');
$dtp->LoadSource($tagstr);
foreach($dtp->CTags as $tagid=>$ctag){
	$name=$ctag->GetName();
	//$innertext=$ctag->GetInnerText();
	if($name=='test')
	{
		$dtp->Assign($tagid,'坑爹的不支持标签');
	}else if($name=='my')
	{
		$dtp->Assign($tagid,FuncMy($ctag,$ctag->GetAtt('att1'),$ctag->GetAtt('att2')));	
	}
	
}
$dtp->Display();
?>