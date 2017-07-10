<?php
function test($v){
	if(DEBUG_LEVEL){
		if(isset($v)){
			if(is_bool($v)){
				echo $v==true? " test--- ture":" test --- false";
			}else if(is_string($v)|| is_numeric($v)||is_integer($v)){
				echo "<pre> test------ $v </pre>";		
			}else if(is_object()){
				echo 'test--- print object';
				print_r($v);
			}else{
				echo "test--- ";
				print_r($v);
			}
		}
		else
		{
			echo "<pre> test-------  </pre>";
		}
	}
}
?>