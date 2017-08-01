<?php
	class admin extends Control
	{
		public function ac_listing()
		{
			$stmt=$this->Model('store')->query("show tables");
			$cont ="<ul>";	
			//$arr=array();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				//$arr[]=$row[0];
				$tname= str_replace($GLOBALS['cfg_dbprefix'],'',$row[0]);
				$cont .="<li><a href='?ct=admin&ac=table&to={$tname}'> {$tname} </a> </li>";
			}
			$cont .="</ul>";
			echo $cont;
		}
		public function ac_table()
		{
			global $_REQUEST;
			extract($_REQUEST);	
			$GLOBALS['DB_Table_Name']=$to;
			$GLOBALS['ADM_PageNo']=is_numeric($PageNo)? $PageNo : 1;
			$GLOBALS['ADM_PageSize']=is_numeric($PageSize)? $PageSize : 10;
			$this->SetTemplate($to."_list.htm","admin");
			$this->Display();
		}
		public function ac_arclist()
		{
			global $_REQUEST;
			extract($_REQUEST);
			$GLOBALS['sql_table']=$to;
			$GLOBALS['PageNo']=is_numeric($PageNo)? $PageNo : 1;
			if(isset($typeid)){
				$GLOBALS['sql_where']='typeid';
				$GLOBALS['sql_value']=$typeid;
			}
			require_once(SUNINC."/dblist.class.php");
			$lv = new ListView($to,$typeid);
			$lv->Display();
		}
		public function ac_record()
		{
			global $_REQUEST;
			extract($_REQUEST);
			$GLOBALS['cfg_cur_tname']=$to;
			$arr=$this->assemble($to,$ac);
			$tpl=$to."_record.htm";
			$tmp=$this->GetTemplate($tpl,"admin");
			if(!file_exists($tmp)){
				$this->BuildForm($tmp,"update");
			}			
			$this->SetTemplate($tpl,"admin");
			$this->SetVar('arr',$arr);
			$this->Display();
		}
		public function ac_forms()
		{
			global $_REQUEST;
			extract($_REQUEST);
			$GLOBALS['cfg_cur_tname']=$to;
			$arr=$this->assemble($to,$ac);
			$tpl=$to."_form.htm";
			$tmp=$this->GetTemplate($tpl,"admin");
			if(!file_exists($tmp)){
				$this->BuildForm($tmp,"insert");
			}			
			$this->SetTemplate($tpl,"admin");
			$this->SetVar('arr',$arr);
			$this->Display();
		}
		public function ac_remove(){
			global $_REQUEST;
			extract($_REQUEST);
			$aff=$this->assemble($to,$ac);
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_insert(){
			global $_REQUEST;
			extract($_REQUEST);
			$aff=$this->assemble($to,$ac);
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_update(){
			global $_REQUEST;
			extract($_REQUEST);
			$aff=$this->assemble($to,$ac);
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_auth(){
			global $_REQUEST,$_SESSION;
			$pwd=md5($_REQUEST["pass"]);
			$usr=trim($_REQUEST["user"]);
			$row=$this->Model('store')->GetOne("SELECT * FROM #@__admin WHERE usr='$usr' AND pwd='$pwd'");
			if(!empty($row)){
				$_SESSION["USERNAME"]=$row['usr'];
				$_SESSION["USERLEVEL"]=$row['id'];
				$this->SetTemplate("admin_index.htm","admin");
				$this->Display();
			}else{
				Message('警告','账号或密码错误');
				exit(0);	
			}
		}
		public function ac_login()
		{
			$this->SetTemplate("admin_login.htm","admin");
			$this->Display();
			exit(0);
		}
		public function ac_index()
		{
			$this->SetTemplate("admin_index.htm","admin");			
			$this->Display();
			exit(0);
		}
		public function ac_logout(){
			global $_SESSION;
			$_SESSION["USERNAME"]="";
			$_SESSION["USERLEVEL"]=null;
			header("Location: ?ct=admin&ac=login");
			exit(0);
		}		
		public function assemble($tname,$ac)
		{
			global $_REQUEST;
			$QLS = compact(array('record','remove','insert','update'));
			$vals="";
			$allow=array();
			$key=preg_replace('/[^\d]/', '', $_REQUEST['key']);
			$mod = $this->Model('store');			
			if($ac=='update'||$ac=='insert')
			{
				$field = $mod->GetTabFields("#@__".$tname);
				$this->Options($field,$allow);
				$flds=implode(",",array_keys($allow));
				$vars=implode("','",$allow);
				foreach($allow as $k=>$v){
					$vals .="$k='{$v}',";
				}
				$vals= preg_replace('/\,$/', '', $vals);
			}
			$QLS['record'] = "SELECT * FROM #@__{$tname} WHERE id =:key";
			$QLS['remove'] = "DELETE FROM #@__{$tname} WHERE id ={$key}";
			$QLS['update'] = "UPDATE #@__{$tname} SET {$vals} WHERE id={$key} ";
			$QLS['insert'] = "INSERT #@__{$tname} ({$flds}) VALUES ('{$vars}')";
			if(array_key_exists($ac,$QLS)){
				$ql=$mod->SetQuery($QLS[$ac]);
				if($ac=='record'){
					$pre=$mod->prepare($ql);
					$pre->execute(array(':key'=>$key));
					// $pre->debugDumpParams();
					return $pre->fetch(PDO::FETCH_ASSOC);
				}else{
					return $mod->exec($ql);
				}
			}else{
				return null;
			}
		}		
		public function Options($scope,&$allow)
		{
			global $_REQUEST;
			foreach ( $_REQUEST as $key => $val )
			{
				if (in_array($key, $scope)){
					$allow[$key] = str_replace("'", "&apos;", $val);
				}
			}
		}
		function BuildForm($tplname, $act="insert")
		{
			extract($_REQUEST);
			$htm ="<html><head><title></title></head><body>\r\n";
			$htm .="<form method='post' action='index.php?' name='{$to}'>\r\n";
			$htm .="<input type='hidden' name='to' value='{$to}' />\r\n";
			$htm .="<input type='hidden' name='ct' value='{$ct}' />\r\n";
			$htm .="<input type='hidden' name='ac' value='{$act}' /><ul>\r\n";
			$mod=$this->Model('store');
			$fields=$mod->GetTabFields('#@__'.$to,'me');
			foreach ($fields as $k => $v) {
				$htm .="<li><label>{$v}</label><input type='text' name='{$v}' value='{sun:var.arr.{$v}/}'></li>\r\n";
			}
			$htm .="<input type='submit' name='button' value='确 认'></ul></form></body></html>";
			$fp = fopen($tplname,'w');
			fwrite($fp,$htm);
			fclose($fp);
		}
		function GetArcList($atts,$refObj,$fields)
		{
			global $cfg_list_son,$cfg_digg_update;
			$tname=$GLOBALS['DB_Table_Name'];
			$pgno=is_numeric($GLOBALS['ADM_PageNo'])? intval($GLOBALS['ADM_PageNo']) : 1;
			$pgsz=$atts['pagesize'];
			$limitstart= ($pgno-1)*$pgsz;
			$result=array();
			$query = "SELECT *  FROM `#@__$tname` arc  LIMIT $limitstart,$pgsz";
			$mod=$this->Model('store');
			$mod->SetQuery($query);
			$mod->Execute('al');
			while($row = $mod->GetArray("al"))
			{
				$result[]=$row;
			}
			return $result;

		}
		function GetPageList($atts,$refObj,$fields)
		{
			require_once(SUNINC."/pagelist.class.php");
			$tname=$GLOBALS['DB_Table_Name'];
			$pgno=is_numeric($GLOBALS['ADM_PageNo'])? intval($GLOBALS['ADM_PageNo']) : 1;
			$mod = $this->Model('store');
			$num = $mod->GetRowTotal($tname);
			//$num,$pgno,$pgsz,$listitem
			$pager= new PageList($num,$pgno,$atts['listsize'],$atts['listitem']);
			return $pager->GetPageBar();
		}
	}
?>
