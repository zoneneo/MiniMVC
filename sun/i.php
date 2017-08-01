<?php
echo phpinfo();
require_once("config.php");
foreach ($GLOBALS as $k => $v) {
	echo "<br> {$k} : ";
	print_r($v);
}

?>