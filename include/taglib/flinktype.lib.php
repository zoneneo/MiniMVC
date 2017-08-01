<?php
if(!defined('SUNINC'))
{
    exit("Request Error!");
}
require_once(SUNINC."/taglib/flink.lib.php");
/**
 * 友情链接
 *
 * @version        $Id: flinktype.lib.php 1 15:57 2011年2月18日Z niap $
 * @package        SunCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2011, DesDev, Inc.
 * @license        http://help.suncms.com/usersguide/license.html
 * @link           http://www.suncms.com
 */

/*>>sun>>
<name>友情链接类型</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>用于获取友情链接类型</description>
<demo>
{sun:flink row='24'/}
</demo>
<attributes>
    <iterm>row:链接类型数量</iterm>
    <iterm>titlelen:链接文字的长度</iterm>
</attributes> 
>>sun>>*/
 
function lib_flinktype(&$ctag,&$refObj)
{
    global $dsql;
    $attlist="row|24,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $totalrow = $row;
    $revalue = '';
  
    $equery = "SELECT * FROM #@__flinktype order by id asc limit 0,$totalrow";

    if(trim($ctag->GetInnerText())=='') $innertext = "<li>[field:typename /]</li>";
    else $innertext = $ctag->GetInnerText();
	if(!isset($type)) $type = '';
    $dtp = new SunTagParse();
    $dtp->SetNameSpace("sun","{","}");
    $dtp->LoadString($innertext);
    
    $dsql->SetQuery($equery);
    $dsql->Execute();
    $rs = '';
    $row = array();
    while($dbrow=$dsql->GetObject())
    {
        $row[] = $dbrow;
    }
	$suncms = false;
	$suncms->id = 999;
	$suncms->typename = '织梦链';
	if($type == 'suncms') $row[] = $suncms;
	
    foreach ($row as $key => $value) {
        if (is_array($dtp->CTags))
        {
            $GLOBALS['envs']['flinkid'] = $value->id;
            foreach($dtp->CTags as $tagid=>$ctag)
            {
                $tagname = $ctag->GetName();
                if($tagname=="flink") $dtp->Assign($tagid, lib_flink($ctag, $refObj));
            }
        }
        $rs = $dtp->GetResult();
    	$rs = preg_replace("/\[field:id([\/\s]{0,})\]/isU", $value->id, $rs);
        $rs = preg_replace("/\[field:typename([\/\s]{0,})\]/isU", $value->typename, $rs);
        $revalue .= $rs;
    }
    
    return $revalue;
}