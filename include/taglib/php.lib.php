<?php
if (!defined('SUNINC'))
    exit('Request Error!');
/**
 * 
 *
 * @version        $Id: php.lib.php1 9:29 2010年7月6日Z tianya $
 * @package        SunCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.suncms.com/usersguide/license.html
 * @link           http://www.suncms.com
 */
 
 /*>>sun>>
<name>PHP代码标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>调用PHP代码</description>
<demo>
{sun:php}
$a = "sun";
echo $a;
{/sun:php}
</demo>
<attributes>
</attributes> 
>>sun>>*/
 
function lib_php(&$ctag, &$refObj)
{
    global $dsql;
    global $db;
    $phpcode = trim($ctag->GetInnerText());
    if ($phpcode == '')
        return '';
    ob_start();
    extract($GLOBALS, EXTR_SKIP);
    @eval($phpcode);
    $revalue = ob_get_contents();
    ob_clean();
    return $revalue;
}