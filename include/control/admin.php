<?php
	class admin extends Control
	{
		public function ac_table()
		{
			$mod=$this->Model('store');
			$stmt=$mod->query("show tables");
			$cont ="<ul>";	
			//$arr=array();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				//$arr[]=$row[0];
				$tname= str_replace($GLOBALS['cfg_dbprefix'],'',$row[0]);
				$cont .="<li><a href='?ct=admin&ac=listing&to={$tname}'> {$tname} </a> </li>";
			}
			$cont .="</ul>";
			echo $cont;
			/* $fdd=array();
			foreach($arr as $tab){
				$fdd =array_merge ($fdd, $mod->GetTabFields($tab));
			}
			echo implode("='',",array_unique($fdd)); */
		}

		public function ac_gods()
		{
			global $_REQUEST;
			$tid = isset($_REQUEST['tid']) ? is_numeric($_REQUEST['tid']) : 1;
			require_once(SUNINC."/list.class.php");
			$lv = new ListView($tid);
			$lv->Display();	
		}
		public function ac_listing()
		{
			global $_REQUEST,$adm_scope,$cn_title;
			require_once(SUNINC."/pager.class.php");
			$sel=$_SERVER["PHP_SELF"];			
			extract($_REQUEST);
			$pag = intval($pag);
			$pag = $pag<1 ? 1 : $pag;
			$_SESSION["TYPE_ID"]=$tid;	
			/*
			$scope=$adm_scope[$to];
			if($scope==""||$scope=='*'){
				$fds = $this->GetTabFields("#@__".$to);
			}else{
				$fds = explode(',',$scope);
			}*/
			$mod=$this->Model('store');
			$fds = $mod->GetTabFields("#@__".$to);
			$flt=array('gbody','descr','words');
			$fds =array_diff($fds,$flt);
			$pgz =30;		
			$query ="SELECT * FROM #@__{$to} ";
			$mod->Execute('me',$query);
			$rdn =$mod->GetTotalRow("me");
			$pset=array("PageSz" =>$pgz,"CurtPage" =>$pag,"Amount"=>$rdn);
			$pager=new Pager($pset);
			list($s,$c,$e,$n)=$pager->PageBar(15);
			$bar="<p id='pagbar'><a href='{$sel}?ac={$ac}&to={$to}&pag=1'>1</a> &lt;&lt; ";
			for($i=$s;$i<$e+1;$i++){
				$css= ($i==$c) ? "class='active'" : '';
				$bar .="<a href='{$sel}?ac={$ac}&to={$to}&pag=$i' $css>$i</a>";
			}
			$bar .=" &gt;&gt; <a href='{$sel}?ac={$ac}&to={$to}&pag=$n'>$n</a></p>";
			$cont ="<html><head><link rel='stylesheet' href='images/admin.css' type='text/css'/></head><body>
			<table class='admtalbe'><tr><th colspan='3' width='60'> <a href='$sel?ct=admin&ac=forms&to={$to}' title='add'>新建</a></th>\r\n";
			foreach($fds as $fd){
				$fn=isset($cn_title[$fd])? $cn_title[$fd] : $fd;	
				$cont .= "<th>$fn</th>";
			}
			$cont .="</tr>\r\n";
			
			$sar=($pag-1)*$pgz;
			$mod->Execute("dd","SELECT * FROM #@__{$to} LIMIT $sar,$pgz");
			while($row = $mod->GetArray('dd'))
			{
				$cont .="<tr><td><a href='$sel?ct=admin&ac=record&do=record&to={$to}&key={$row['id']}' title='Edit'><img src='images/edit.png'></a></td>";
				$cont .="<td><a href='$sel?ct=admin&ac=record&do=copy&to={$to}&key={$row['id']}' title='Copy'><img src='images/drop.png'></a></td>";
				$cont .="<td><a href='$sel?ct=admin&ac=remove&to={$to}&key={$row['id']}' title='Delete'><img src='images/drop.png'></a></td>";	
				foreach($fds as $fd)
				{
					$cont .= empty($row[$fd])?"<td>&nbsp;</td>" : "<td>".$row[$fd]."</td>";
				}
				$cont .="</tr>";
			}
			$cont.="</table></body></html>";
			$cont.=$bar;			
			echo $cont;	
		}
		public function ac_listg()
		{
			global $_REQUEST;
			extract($_REQUEST);		
 			$sql=$this->assemble($to,$ac);
			$arr=$this->Model('store')->Lists($sql);		
			$this->SetTemplate($to."_list.htm");
			$this->SetVar('arr',$arr);
			$this->Display(); 
		}
		public function ac_record()
		{
			global $_REQUEST;
			extract($_REQUEST);
			$sql=$this->assemble($to,$ac);
			$arr=$this->Model('store')->GetOne($sql);
			$this->SetTemplate($to."_".$do.".htm");
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
				$fields=$this->Model('store')->GetTabFields('#@__'.$to,'me');
				$this->BuildForm($tmpfile,$fields,$to);

			}
			$this->SetTemplate($to."_form.htm");		
			$this->Display();
		}
		public function ac_auth(){
			global $_REQUEST,$_SESSION;
			//$pwd=substr(md5($_REQUEST["pass"]), 5, 20);
			$pwd=md5($_REQUEST["pass"]);
			$usr=trim($_REQUEST["user"]);
			$row=$this->Model('store')->GetOne("SELECT * FROM #@__admin WHERE usr='$usr' AND pwd='$pwd'");
			if(!empty($row)){
				$_SESSION["USERNAME"]=$row['usr'];
				$_SESSION["USERLEVEL"]=$row['id'];
				$this->SetTemplate("admin_index.htm");
				$this->Display();
			}else{
				Message('警告','账号或密码错误');
				exit(0);	
			}
		}
		public function ac_login()
		{
			$this->SetTemplate("admin_login.htm");
			$this->Display();
			exit(0);
		}
		public function ac_index()
		{
			$this->SetTemplate("admin_index.htm");			
			$this->Display();
			exit(0);
		}
		public function ac_logout(){
			global $_SESSION;
			//session_start();
			$_SESSION["USERNAME"]="";
			$_SESSION["USERLEVEL"]=null;
			//header("Location: ".$_SERVER['PHP_SELF']);
			header("Location: ?ct=admin&ac=login");
			exit(0);
		}		
		public function assemble($tname,$ac){
			global $_REQUEST,$adm_scope;
			$box=$adm_scope;
			$sql = compact(array('listing','record','remove','append','update'));
			if(array_key_exists($tname,$box)){
				$allow=array();
				$scope=$box[$tname];
				if($scope==""||$scope=='*'){
					$field = $this->Model('store')->GetFields("#@__".$tname);
				}else{
					$field = explode(',',$scope);
				}
				$this->Options($field,$allow);
				$flds=implode(",",array_keys($allow));
				$vars=implode("','",$allow);
				$key=preg_replace('/[^\d]/', '', $_REQUEST['key']);		
				
				$vals="";
				foreach($allow as $k=>$v){
					$vals .="$k =@{$k},";
				}
				$vals= preg_replace('/\,$/', '', $vals);
				$sql['listing'] ="SELECT {$scope} FROM #@__{$tname}";	
				$sql['record'] ="SELECT {$scope} FROM #@__{$tname} WHERE id ='{$key}'";
				$sql['remove'] ="DELETE FROM #@__{$tname} WHERE id = :key";
				$sql['update'] = "UPDATE #@__{$tname} SET {$vals} WHERE id =:key ";
				$sql['append'] = "INSERT INTO `#@__{$tname}` ({$flds}) VALUES('{$vars}')";
			}
			return $sql[$ac];
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
			$fp = fopen($tplname,'w');
			flock($fp,3);
			fwrite($fp,"<html><head><title></title></head><body><ul>\r\n");
			fwrite($fp,"<form method='post' action='index.php?' name='$to'>\r\n");
			fwrite($fp,"<input type='hidden' name='to' value='$to' />\r\n");
			fwrite($fp,"<input type='hidden' name='ct' value='admin' />\r\n");
			fwrite($fp,"<input type='hidden' name='ac' value='append' /><ul>\r\n");
			foreach($fields as $k=>$v)
			{
				fwrite($fp,"<li><label>{$k} {$v}</label><input type='text' name='{$v}' value=''></li>\r\n");
			}
			fwrite($fp,"<input type='submit' name='button' value='确 认'></form></ul></body></html>");
			fclose($fp);
		}
	}
?>
