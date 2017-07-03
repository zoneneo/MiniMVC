<?php
	class upload extends Control
	{
		public function ac_user()
		{
			
		}
		public function ac_illness()
		{

		}

		public function ac_genetic()
		{
			$aff = $this->assemble('genome',array('card','disease','locu','gene','name','attr','bas1','bas2','seq1','seq2','risk'));
		}
		public function ac_report()
		{
			$aff = $this->assemble('report',array('tme','name','card','title','item'));
			if($aff){
				echo '{"sig": 0,"msg":"ok"}';
			}else{
				echo '{"sig": 1,"msg":"Insert the data error"}';
			}
		}
		public function assemble($tname,$scope){
			global $_REQUEST;
			$allow=array();			
			foreach ( $_REQUEST as $key => $val )
			{
				if (in_array($key, $scope)){
					$allow[$key] = $val;
				}
			}
			if(empty($allow)) return 0;
			$flds=implode(",",array_keys($allow));
			$vars=implode("','",$allow);
			$sql = "INSERT INTO `#@__{$tname}` ({$flds}) VALUES ('{$vars}')";
			$sql = str_replace("#@__",$GLOBALS['cfg_dbprefix'],$sql);
			echo $sql;
			//return $this->Model('store')->exec($sql);
			
			return $this->Model('store')->exec($sql);
			
		}	
	}
?>
