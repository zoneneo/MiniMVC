<?php
// require_once("include/config.php");
// $txt=" %s %s %s %s %s";
// $str = sprintf($txt,SUNROOT,MODEL,CONTROL,DATA,SUNTPL);
// echo $str;

// echo $_SERVER['PHP_SELF'];
// echo phpinfo();

// $wwwroot=$_SERVER["DOCUMENT_ROOT"];
// $cmspath= str_replace($wwwroot, '', SUNROOT);

// echo '<br>'.$cmspath;

$signTime = '1417773892;1417853898';
$signKey = hash_hmac('sha1', $signTime, 'BQYIM75p8x0iWVFSIgqEKwFprpRSVHlz');
echo '<br>';
echo $signKey;
$httpString = "get\n/testfile\n\nhost=bucket1-1254000000.cn-north.myqcloud.com&range=bytes%3D0-3\n";
$sha1edHttpString = sha1($httpString);
echo '<br>';
echo $sha1edHttpString;

$stringToSign = "sha1\n$signTime\n$sha1edHttpString\n";
$signature = hash_hmac('sha1', $stringToSign, $signKey);



?>