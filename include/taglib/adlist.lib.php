<?php   if(!defined('SUNINC')) exit('Request Error!');
/**
 * 广告调用
 *
 * @version        $Id: myad.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        SunCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.suncms.com/usersguide/license.html
 * @link           http://www.suncms.com
 */
 
/*>>sun>>
<name>广告标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>获取广告代码</description>
<demo>
{sun:myad name=''/}
</demo>
<attributes>
    <iterm>typeid:投放范围,0为全站</iterm> 
    <iterm>name:广告标识</iterm>
</attributes> 
>>sun>>*/

function lib_adlist(&$ctag, &$refObj)
{
	global $dsql;
    $attlist="row|12,titlelen|24,flag|p,typeid|0";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());
    $totalrow = $row;
    $revalue = '';

    if(empty($innertext))
    {
        $innertext = GetSysTemplets('tag_adlist.htm');
    }

	$wsql = " WHERE FIND_IN_SET('{$flag}', flag)>0 ";

    $equery = "SELECT * FROM `#@__adlink` $wsql  LIMIT 0 , $totalrow";
    $ctp = new SunTagParse();
    $ctp->SetNameSpace('field','[',']');
    $ctp->LoadSource($innertext);

    $dsql->Execute('fb',$equery);
    while($arr=$dsql->GetArray('fb'))
    {
        #$arr['title'] = cn_substr($arr['arctitle'],$titlelen);
        foreach($ctp->CTags as $tagid=>$ctag)
        {
            if(!empty($arr[$ctag->GetName()]))
            {
                $ctp->Assign($tagid,$arr[$ctag->GetName()]);
            }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}