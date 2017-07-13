<?php
require_once("include/config.php");
$txt=" %s %s %s %s %s";
$str = sprintf($txt,SUNROOT,MODEL,CONTROL,DATA,SUNTPL);
echo $str;

echo $_SERVER['PHP_SELF'];
echo phpinfo();

$wwwroot=$_SERVER["DOCUMENT_ROOT"];
$cmspath= str_replace($wwwroot, '', SUNROOT);

echo '<br>'.$cmspath;

?>