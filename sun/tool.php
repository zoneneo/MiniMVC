<?php

//$sel=basename($_SERVER['PHP_SELF']);

	//md5,urlencode,urldecode,base64_encode,base64_decode.
	$cont="<div id='toolbox'><form name='toencode' action='tool.php' method='post'>
	<p><textarea name='q' style='width:500px;height:200px'></textarea></p><p>
	<input type='submit' name='cmd' value='md5'>
	<input type='submit' name='cmd' value='urlencode'>
	<input type='submit' name='cmd' value='urldecode'>
	<input type='submit' name='cmd' value='base64encode'>
	<input type='submit' name='cmd' value='base64decode'>
	</p></form></div><br>\n";
	echo $cont;
	echo "<textarea name='q' style='width:500px;height:200px'>";
	if(!empty($_REQUEST['q']))
	{
		$q=$_REQUEST['q'];
		$cmd=$_REQUEST['cmd'];
		if($cmd=='md5'){
			echo md5($q);
		}
		else if($cmd=='urlencode')
		{
			echo urlencode($q);
		}
		else if($cmd=='urldecode')
		{
			echo urldecode($q);
		}
			else if($cmd=='base64encode')
		{
			echo base64_encode($q);
		}
		else if($cmd=='base64decode')
		{
			echo base64_decode($q);
		}		
	}
	echo "</textarea>";

?>