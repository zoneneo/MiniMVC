<?php
	$fle = '1.jpg';	//创建源图的实例
	$arr=getimagesize($fle);
	$src = imagecreatefromstring(file_get_contents($fle));
	preg_match_all ("/\d+/",$arr[3],$attr);
	$att=$attr[0];

	$x = 500;
	$y = 500;

	//最终保存成图片的宽和高，和源要等比例，否则会变形
	$width = 200;
	$height = 200;
	//$final_width = 100;
	//$final_height = round($final_width * $height / $width);

	$img = imagecreatetruecolor($width, $height);
	//$img = imagecreatetruecolor(50, 50);
	//imagecopyresampled($img, $src, 0, 0, $x, $y, $width, $height, $att[0], $att[1]);
	imagecopyresampled($img, $src, 0, 0, $x, $y, $width, $height, 500, 500);
	$pic='g'.$fle;
	/*输出图片	
	
	$tp = @fopen($pic, 'wb');  
	fwrite($tp, $img);
	fclose($tp);
	*/
	imagejpeg($img,$pic);
	header('Content-Type: image/jpeg');
	imagejpeg($img);
	imagedestroy($src);
	imagedestroy($img);
	
?>