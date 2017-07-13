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
			$this->SetTemplate($to."_list.htm");
			$this->Display();
		}
		public function ac_goods()
		{
			global $_REQUEST;
			extract($_REQUEST);
			require_once(SUNINC."/list.class.php");
			$tempfile = SUNTPL."/default/archives_list.htm";
			$tid = is_numeric($tid) ? $tid : 0;
			$lv = new ListView($tid);
			$lv->Display();	
		}
		public function ac_record()
		{
			global $_REQUEST;
			extract($_REQUEST);
			$sql=$this->assemble($to,$ac);
			$arr=$this->Model('store')->GetOne($sql);
			//$this->SetTemplate($to."_".$ac.".htm");
			$this->SetTemplate($to."_form.htm");
			$this->SetVar('arr',$arr);
			$this->Display();
		}
		public function ac_remove(){
			global $_REQUEST;
			extract($_REQUEST);
			$sql=$this->assemble($to,$ac);
			$key=preg_replace('/[^\d]/', '', $_REQUEST['key']);
			$mod=$this->Model('store')->prepare($sql);
			$aff=$mod->execute(array(':key'=>$key));
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_append(){
			global $_REQUEST;
			extract($_REQUEST);
			$sql=$this->assemble($to,$ac);
			$aff=$this->Model('store')->Execute($sql);
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_update(){
			global $_REQUEST;
			extract($_REQUEST);
			$sql=$this->assemble($to,$ac);	
			$key=preg_replace('/[^\d]/', '', $_REQUEST['key']);
			$mod=$this->Model('store')->prepare($sql);
			$aff=$mod->execute(array(':key'=>$key));
			if($aff) echo 'ok';
			else	echo 'err';
		}
		public function ac_forms(){
			global $_REQUEST;
			extract($_REQUEST);
			$tmpfile=$this->tpldir.'/'.$to.'_form.htm';
			if(!file_exists($tmpfile))
			{
				$loop="<li><label>[$v}</label><input type='text' name='[$v]' value='{$v}'></li>\r\n";
				$htm ="<html><head><title></title></head><body>\r\n";
				$htm .="<form method='post' action='index.php?' name='$to'>\r\n";
				$htm .="<input type='hidden' name='to' value='$to' />\r\n";
				$htm .="<input type='hidden' name='ct' value='admin' />\r\n";
				$htm .="<input type='hidden' name='ac' value='append' /><ul>\r\n";
				$htm .="{<li><label></label><input type='hidden' name='' value='' /></li>\}";
				$htm .="<input type='submit' name='button' value='确 认'></ul></form></body></html>";

				$fields=$this->Model('store')->GetTabFields('#@__'.$to,'me');
				foreach ($fields as $k => $v) {


				}
				$this->BuildForm($tmpfile,$fields,$to);

			}
			$this->SetTemplate($to."_form.htm");		
			$this->Display();
		}
		public function ac_auth(){
			global $_REQUEST,$_SESSION;
			// $pwd=md5($_REQUEST["pass"]);
			$pwd=$_REQUEST["pass"];
			$usr=trim($_REQUEST["user"]);
			// $row=$this->Model('store')->GetOne("SELECT * FROM #@__admin WHERE usr='$usr' AND pwd='$pwd'");
			// if(!empty($row)){
			// 	$_SESSION["USERNAME"]=$row['usr'];
			// 	$_SESSION["USERLEVEL"]=$row['id'];
			// 	$this->SetTemplate("admin_index.htm","admin");
			// 	$this->Display();
			// }else{
			// 	Message('警告','账号或密码错误');
			// 	exit(0);	
			// }
			$_SESSION["USERNAME"]=$usr;
			$_SESSION["USERLEVEL"]=100;
			$this->SetTemplate("admin_index.htm","admin");
			$this->Display();
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
			$sql = compact(array('listing','record','remove','insert','update'));
			$vals="";
			$allow=array();
			$field = $this->Model('store')->GetFields("#@__".$tname);
			$this->Options($field,$allow);
			$flds=implode(",",array_keys($allow));
			$vars=implode("','",$allow);
			$key=preg_replace('/[^\d]/', '', $_REQUEST['key']);
			foreach($allow as $k=>$v){
				$vals .="$k =@{$k},";
			}
			$vals= preg_replace('/\,$/', '', $vals);
			$sql['listing']= "SELECT {$scope} FROM #@__{$tname}";	
			$sql['record'] = "SELECT {$scope} FROM #@__{$tname} WHERE id ='{$key}'";
			$sql['remove'] = "DELETE FROM #@__{$tname} WHERE id = :key";
			$sql['update'] = "UPDATE #@__{$tname} SET {$vals} WHERE id =:key ";
			$sql['insert'] = "INSERT INTO #@__{$tname} ({$flds}) VALUES('{$vars}')";
			if(array_key_exists($ac,$sql)){
				return $sql[$ac];				
			}else{
				return '';
			}
		}		
		public function Options($scope,&$allow)
		{
			global $_REQUEST;
			foreach ( $_REQUEST as $key => $val )
			{
				if (in_array($key, $scope)){
					$allow[$key] = $val;
				}
			}
		}
		function BuildForm($tplname,$fields,$to)
		{
			$loop="<li><label>[$v}</label><input type='text' name='[$v]' value='{$v}'></li>\r\n";
			$htm ="<html><head><title></title></head><body>\r\n";
			$htm .="<form method='post' action='index.php?' name='$to'>\r\n";
			$htm .="<input type='hidden' name='to' value='$to' />\r\n";
			$htm .="<input type='hidden' name='ct' value='admin' />\r\n";
			$htm .="<input type='hidden' name='ac' value='append' /><ul>\r\n";
			$htm .="{$loop}";
			$htm .="<input type='submit' name='button' value='确 认'></ul></form></body></html>";
			//$fp = fopen($tplname,'w');			
			// fwrite($fp,$htm);
			// fclose($fp);
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
