<?php
	class goods extends Control{
		public function ac_rcord(){
			if(!is_numeric($_REQUEST['key'])) exit('request error');
			$arr=$this->Model('gods')->onerd($id);
			$arr['cmd']=$_REQUEST['cmd'];
			$this->SetTemplate("godbody.htm");
			echo $this->tpl->templateDir;
			echo '<br>';
			echo $this->tpl->templateFile;
			echo '<br>';
			
			echo $this->tpl->refDir;
			echo '<br>';
			echo $this->tpl->cacheDir;
	
			$this->SetVar('arr',$arr);
			$this->Display();
		}
		public function ac_copy(){
			echo 'copy';
			/*
			if(!is_numeric($_REQUEST['key'])) exit('request error');
			$id=$_REQUEST['key'];
			$arr=$this->Model('gods')->onerd($id);
			$this->SetTemplate("godbody.htm");			
			$this->SetVar('arr',$arr);
			$this->Display();
			*/
		}
	}
?>