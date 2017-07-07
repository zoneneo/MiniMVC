<?php
	class product extends Control{
		public function ac_cord(){		
			$arr=$this->Model('gods')->onerd();
			$this->SetTemplate("archives.htm");			
			$this->SetVar('var',$arr);
			$this->Display();
			echo $this->tpl->templateDir;
			echo '<br>';
			echo $this->tpl->templateFile
		}
	}
?>