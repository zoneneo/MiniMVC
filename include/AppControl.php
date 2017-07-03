<?php
/**
 * APP接口控制类
 * @author		ander.sun
 * @version		1.0.1
 * @package     APP后台接口，软件包iapp
 */

class AppControl {
    var $cli;
    var $mgo;

    function __construct() 
    {
    	global $mgocli;
    	$this->cli = $mgocli;
    }

    function AppControl(){
    	$this->__construct();
    }

    function getProfile()
    {
    	$col = $this->cli->selectCollection("genetk","weixin");
    	$row = $col->findOne(array("_id"=>$_SESSION['WEIXINOPENID']));
    	if(!isset($row['tel']) || $row['tel']==''){
    		return ['sig'=>61,'msg'=>'没有注册'];
    	}
    	unset($row['_id']);
    	if(isset($row['name'])&& $row['name']!='')
    		$row['name']=base64_decode($row['name']);
    	return ['sig'=>0,'res'=>$row];
    }
    
 	function listing()
	{
		$res = array();
		$wei = $_SESSION['WEIXINOPENID'];
		$col = $this->cli->selectCollection("genetk","customer");
		$cur= $col->find(array("weix"=>array('$regex'=>$wei)));
		$descr=['样本末知(0/4)','收到样本(1/4)','样本检测中(2/4)','数据分析中(3/4)','已出报告(4/4)，可点击查看'];
		foreach($cur as $doc){		//_id,tme,name,sets,tel,title,header,step,weix
			extract($doc);
			$step=is_numeric($step)? abs(intval($step)) : 0;
			if($step>4){ $step=0;}

			$des=$descr[$step];
			$tme= is_null($tme)? 0 : intval($tme);
			$title = is_null($title)? '' : $title;
			$header = is_null($header)? '': $header;
			$day=date('Y年m月d日',$tme);
			array_push($res,['header'=>$title.$header,'title'=>$title,'name'=>$doc['name'],'card'=>$doc['_id'],'step'=>"$step",'stepDescr'=>$des,'date'=>$day]);			
		}
		return ['sig'=>0,'res'=>$res];
	}
	
	function capacity(){//60
		$wei = $_SESSION['WEIXINOPENID'];
		if(!isset($_REQUEST["title"])||$_REQUEST["title"]=='')
			return ['sig'=>61,'msg'=>'能力名不能为空!'];
		if(!isset($_REQUEST["_id"])||$_REQUEST["_id"]=='')
			return ['sig'=>62,'msg'=>'基因卡编码不能为空!'];
		$tle = $_REQUEST["title"];
		$encode = mb_detect_encoding($tle, array('ASCII','GB2312','GBK','UTF-8'));
		if ($encode == "GB2312"){$tle = iconv("GB2312","UTF-8",$tle);}
		if ($encode == "GBK"){$tle = iconv("GBK","UTF-8",$tle);}
		$row = $this->checkmgo(array('_id'),'report');
		$sna = $row['set'];
		$gen = $row['gender']; 
		$arr = ['B'=>'description','F'=>'descriptionF','M'=>'descriptionM'];
		$abl = $arr[$gen];
		$rk='';
		$lel=['6','5','4','3','2','1'];
		foreach ($row['dis'] as $key=>$dis){
			if($dis['Title']==$tle){
				$rk = $dis['type'];
				$inx = intval($rk);			
				$pin = str_replace('section_', '', $key);
				$con = $dis['concl'];
				$gno=array();
				
				foreach($dis['genotype'] as $g){
					$arr=array();
					$arr["gtid"]=$g["gtid"];
					$arr["geneID"]=$g["geneID"];
					$seq=$g["seq1"].'['.$g["base1"].']'.$g["seq2"];
					$arr["seqb1"]=str_replace('\\', '', $seq);
					$seq=$g["seq1"].'['.$g["base2"].']'.$g["seq2"];					
					$arr["seqb2"]=str_replace('\\', '', $seq);
					$arr["type"]=$g["type"];
					#$arr["type"]=$lel[intval($g["type"])];
					array_push($gno, $arr);
				}
				break;
			}
		}
		if($rk!=''){
			$col = $this->cli->selectCollection("genetk","setInfos");
			$ifo = $col->findOne(array('_id'=>$sna));
			$pre = $ifo['taoxi'].'_'.$ifo['taocan'].'_'.$pin;
			$col = $this->cli->selectCollection("genetk","prodata");
			$all = $col->findOne(array('_id'=>$pre));
			$desc=isset($all['CN']['summary']['description'])? $all['CN']['summary']['description'] : "";
			if(isset($all['CN']['suggestion'])){
				$sug = $all['CN']['suggestion'];
				$spot=$sug['sport']['l'.$rk]['part1'];
				$nutr=$sug['nutrition']['l'.$rk]['part1'];
				$heal=$sug['healthy']['l'.$rk]['part1'];
				$heal .=$sug['healthy']['l'.$rk]['part2'];
				$tip=$sug['healthy']['l'.$rk]['part3'];
			}else{
				$spot=$nutr=$heal=$tip='';
			}

			#return ['sig'=>0,'res'=>['risk'=>$lel[$inx],'title'=>$tle,'concl'=>$con,'genotype'=>$gno,'summary'=>$desc,"advices"=>[['name'=>'体检策略','advice'=>$heal,'tip'=>$tip],['name'=>'生活指南','advice'=>$nutr,'tip'=>$spot]]]];
			return ['sig'=>0,'res'=>['risk'=>"$inx",'title'=>$tle,'concl'=>$con,'genotype'=>$gno,'summary'=>$desc,"advices"=>[['name'=>'体检策略','advice'=>$heal,'tip'=>$tip],['name'=>'生活指南','advice'=>$nutr,'tip'=>$spot]]]];
		}else{
			return ['sig'=>53,'msg'=>'当前报告没有查询的疾病!'];
		}
	}
	
	function disease(){ //50
		$wei = $_SESSION['WEIXINOPENID'];
		if(!isset($_REQUEST["title"])||$_REQUEST["title"]=='')
			return ['sig'=>51,'msg'=>'疾病名不能为空!'];
		if(!isset($_REQUEST["_id"])||$_REQUEST["_id"]=='')
			return ['sig'=>52,'msg'=>'基因卡编码不能为空!'];
		$tle = $_REQUEST["title"];
		$encode = mb_detect_encoding($tle, array('ASCII','GB2312','GBK','UTF-8'));
		if ($encode == "GB2312"){$tle = iconv("GB2312","UTF-8",$tle);}
		if ($encode == "GBK"){$tle = iconv("GBK","UTF-8",$tle);}
		$row = $this->checkmgo(array('_id'),'report');
		$sna = $row['set'];
		$rk='';
		foreach ($row['dis'] as $key=>$dis){
			if($dis['Title']==$tle){
				$rk = strval($dis['type']);
				$pin = str_replace('section_', '', $key);
				$con = $dis['concl'];
				$gno=array();
				foreach($dis['genotype'] as $g){
					$arr=array();
					$arr["gtid"]=$g["gtid"];
					$arr["geneID"]=$g["geneID"];
					$seq=$g["seq1"].'['.$g["base1"].']'.$g["seq2"];
					$arr["seqb1"]=str_replace('\\', '', $seq);
					$seq=$g["seq1"].'['.$g["base2"].']'.$g["seq2"];					
					$arr["seqb2"]=str_replace('\\', '', $seq);
					$arr["type"]=$g["type"];
					array_push($gno, $arr);
				}
				break;
			}
		}
		if($rk!=''){
			$col = $this->cli->selectCollection("genetk","setInfos");
			$ifo = $col->findOne(array('_id'=>$sna));
			$pre = $ifo['taoxi'].'_'.$ifo['taocan'].'_'.$pin;
			$col = $this->cli->selectCollection("genetk","prodata");
			$all = $col->findOne(array('_id'=>$pre));
			$desc=isset($all['CN']['summary']['description'])? $all['CN']['summary']['description'] : "";
			if(isset($all['CN']['suggestion'])){
				$sug = $all['CN']['suggestion'];
				$spot=$sug['sport']['l'.$rk]['part1'];
				$nutr=$sug['nutrition']['l'.$rk]['part1'];
				$heal=$sug['healthy']['l'.$rk]['part1'];
				$heal .=$sug['healthy']['l'.$rk]['part2'];
				$tip=$sug['healthy']['l'.$rk]['part3'];
			}else{
				$spot=$nutr=$heal=$tip='';
			}
			return ['sig'=>0,'res'=>['risk'=>$rk,'title'=>$tle,'concl'=>$con,'genotype'=>$gno,'summary'=>$desc,"advices"=>[['name'=>'体检策略','advice'=>$heal,'tip'=>$tip],['name'=>'生活指南','advice'=>$nutr,'tip'=>$spot]]]];
		}else{
			return ['sig'=>53,'msg'=>'当前报告没有查询的疾病!'];
		}
	}
	function tripower(){
		$wei = $_SESSION['WEIXINOPENID'];
		$rsk = isset($_REQUEST["level"])? $_REQUEST["level"] : '';
		$arr = $this->checkmgo(array('_id'),'customer');
		if (!preg_match ("/".$wei."/", $arr['weix']))
			return ['sig'=>31,'msg'=>'没有绑定的报告'];
		$row = $this->checkmgo(array('_id'),'report');
		$sna =$row['set'];
		$cid =$row['_id'];
		if($rsk=='low') $str = " > 3";
		else if($rsk=='middle') $str = " == 3";
		else if($rsk=='high') $str = " < 3";
		else $str = " > 0";
		$num=0;
		$arr=array();
		$lgc=0;
		$lel=['6','5','4','3','2','1'];
		foreach ($row['dis'] as $key=>$dis){
			if($dis['category']=='0'){
				$rk=$dis['type'];
				$inx = intval($rk);
				eval("\$lgc= ".$rk.$str." ? 1 : 0;");
				if($lgc){
					$num +=1;
					$pin=str_replace('section_', '', $key);
					//$arr[$pin]=[$dis['Title'],$lel[$inx]];
					$arr[$pin]=[$dis['Title'],$inx];
				}	
			}
		}

		if($num<11){ $typ='flat';
		}else if($num > 50){ $typ='complex';
		}else{	$typ='loft';}

		$col2 = $this->cli->selectCollection("genetk","setInfos");
		$ifo = $col2->findOne(array('_id'=>$sna));
		$txi = $ifo['taoxi'];
		$can = $ifo['taocan'];
		$pre = $txi.'_'.$can.'_';
		//db.prodata.find({_id:/Xclient_jiyinshuoxilie_changjianzhongliu3xiang/})
		$cls=array();
		$col3 = $this->cli->selectCollection("genetk","prodata");
		if(!empty($arr)){
			foreach($arr as $k=>$v){
				$id=$pre.$k;
				$all = $col3->findOne(array('_id'=>$id));
				$sec=$all['CN']['section1']['name'];
				$se2=$all['CN']['section2']['name'];
				$se2 = $se2==''? 'xxxxx' : $se2;
				if(array_key_exists($sec,$cls)){
					if(array_key_exists($se2,$cls[$sec])){
						array_push($cls[$sec][$se2],$v);
					}else{
						$cls[$sec][$se2]=array();
						array_push($cls[$sec][$se2],$v);
					}
				}else{
					$cls[$sec]=array();
					$cls[$sec][$se2]=array();
					array_push($cls[$sec][$se2],$v);
				}
			}
		}
		$res = array('card'=>$cid,'num'=>$num,'listype'=>$typ,'capability'=>$cls);
		return ['sig'=>0,'res'=>$res];
	}	
	function tririsk(){//card关联openid
		$wei = $_SESSION['WEIXINOPENID'];
		$rsk = isset($_REQUEST["risk"])? $_REQUEST["risk"] : '';
		$arr = $this->checkmgo(array('_id'),'customer');
		if (!preg_match ("/".$wei."/", $arr['weix']))
			return ['sig'=>31,'msg'=>'没有绑定的报告'];
		$row = $this->checkmgo(array('_id'),'report');
		$sna =$row['set'];
		$cid =$row['_id'];
		if($rsk=='low') $str = " < 3";
		else if($rsk=='middle') $str = " == 3";
		else if($rsk=='high') $str = " > 3";
		else $str = " > 0";
		$num=0;
		$arr=array();
		foreach ($row['dis'] as $key=>$dis){
			if($dis['category']=='1'){
				$rk = strval($dis['type']);
				eval("\$lgc= ".$rk.$str." ? 1 : 0;");
				if($lgc){
					$num +=1;
					$pin=str_replace('section_', '', $key);
					$arr[$pin]=[$dis['Title'],$rk];
				}
			}	
		}
		//flat|loft|complex
		//$typ= $num>50 ? 'complex': $num<11 ? 'flat': 'loft';
		if($num<11)
			$typ='flat';
		else if($num > 50)
			$typ='complex';
		else
			$typ='loft';		
		
		//db.setInfos.find({_id:"BP"})
		$col2 = $this->cli->selectCollection("genetk","setInfos");
		$ifo = $col2->findOne(array('_id'=>$sna));
		$txi = $ifo['taoxi'];
		$can = $ifo['taocan'];
		$pre = $txi.'_'.$can.'_';
		//db.prodata.find({_id:/Xclient_jiyinshuoxilie_changjianzhongliu3xiang/})
		$cls=array();
		$col3 = $this->cli->selectCollection("genetk","prodata");
		if(!empty($arr)){
			foreach($arr as $k=>$v){
				$id=$pre.$k;
				$all = $col3->findOne(array('_id'=>$id));
				$sec=$all['CN']['section1']['name'];
				$se2=$all['CN']['section2']['name'];
				$se2 = $se2==''? 'xxxxx' : $se2;
				if(array_key_exists($sec,$cls)){				
					if(array_key_exists($se2,$cls[$sec])){
						array_push($cls[$sec][$se2],$v);
					}else{
						$cls[$sec][$se2]=array();
						array_push($cls[$sec][$se2],$v);
					}
				}else{
					$cls[$sec]=array();
					$cls[$sec][$se2]=array();
					array_push($cls[$sec][$se2],$v);
				}
			}
		}
		$res = array('card'=>$cid,'num'=>$num,'listype'=>$typ,'disease'=>$cls);
		return ['sig'=>0,'res'=>$res];
	}

	function report()	//30
	{
		if(!isset($_REQUEST['_id']) ||$_REQUEST['_id']=='')
			return ['sig'=>30,'msg'=>'基因卡编码为空'];
		$rsk=array('low'=>0,'middle'=>0,'high'=>0);
		$cap=array('low'=>0,'middle'=>0,'high'=>0);
		$wei = $_SESSION['WEIXINOPENID'];
		$arr=$this->checkmgo(array('_id'),'customer');
		if (!preg_match ("/".$wei."/", $arr['weix']))
			return ['sig'=>31,'msg'=>'没有绑定的报告'];
		$tle=$arr['title'].$arr['header'];
		$day=date('Y年m月d日',intval($arr['tme']));
		$nam=$arr['name'];
		$row=$this->checkmgo(array('_id'),'report');
		if(empty($row))
			return ['sig'=>32,'msg'=>'样本检测中'];
		foreach ($row['dis'] as $dis){
			$typ=$dis['category'];
			$rk=$dis['type'];
			if($typ){
				if($rk < 3){
					$rsk['low'] +=1;
				}else if($rk > 3){
					$rsk['high'] +=1;
				}else{
					$rsk['middle'] +=1;
				}
			}else{//数值越低能力越强
				if($rk < 3){
					$cap['high'] +=1;
				}else if($rk > 3){
					$cap['low'] +=1;
				}else{
					$cap['middle'] +=1;
				}
			}
		}
		$nutr="";
		$heal="";
		if(!empty($row['comp'])){
			for($i=1;$i<4;$i++){
				$nutr .=empty($row['comp']['nutrition']['part'.$i])? '' : $row['comp']['nutrition']['part'.$i];
				$heal .=empty($row['comp']['healthy']['part'.$i])? '' : $row['comp']['healthy']['part'.$i];
			}
		}
		$spot="1. 参考理想体重安排运动量。维持理想体重有利于降低您患糖尿病、心脑血管等疾病的风险。运动2. 建议每周进行规律的运动,可以根据自己的年龄、体质及喜好来选择运动方式,如慢跑、快步走、健身操、舞蹈、太极拳、游泳、球类等,坚持每周运动 5~7 次,每次运动 30 分钟,
逐步养成良好的运动习惯。";
		$psyc="心理平衡对维护健康起着至关重要的作用,请保持积极乐观的心态,注意情绪和心理调节,以平和的心态面对生活与工作中的各种压力,可以通过倾诉、听音乐、娱乐等途径来减压,避免	长期压抑、紧张、焦虑等负性情绪损害健康。";
		$tip="在此之前如您没有运动习惯或有运动禁忌,请咨询医生。运动时请从最小量开始,循序渐进,并结合自我感觉来判断运动量是否适宜。判断标准:1. 运动量适宜表现为锻炼后有微汗、轻松愉快、食欲和睡眠良好,虽然稍感疲乏、肌肉酸痛,但休息后可消失,第二天体力充沛,有运动欲望。2. 如出现心率明显加快、心律不齐,甚至严重胸闷憋气、膝踝关节疼痛等症状,应立即停止运动,及时与医生
联系。";
		$res=['header'=>$tle,'name'=>$nam,'date'=>$day,'category'=>['capability'=>$cap,'risk'=>$rsk],"advices"=>[['name'=>'饮食','advice'=>$nutr,'tip'=>''],['name'=>'运动','advice'=>$spot,'tip'=>$tip],['name'=>'心理','advice'=>$psyc,'tip'=>''],['name'=>'体检','advice'=>$heal,'tip'=>'']]];
		return ['sig'=>0,'res'=>$res];
	}
	
	function Bundle($cid)	//绑定报告。
	{
		$opi =$_SESSION['WEIXINOPENID'];
		$col = $this->cli->selectCollection("genetk","customer");
		$arr = array("_id"=>"$cid");
		$row =$col->findOne($arr);
		if(empty($row['weix'])){
			$col->update($arr,array('$set'=>array('weix'=>$opi)));
		}else{	
			$all=explode(',',$row['weix']);
			if(!in_array($opi, $all)){
				array_push($all,$opi);
				$wei = implode(',',$all);
				$col->update($arr,array('$set'=>array('weix'=>$wei)));
			}			
		}
		$descr=['样本末知(0/4)','收到样本(1/4)','样本检测中(2/4)','数据分析中(3/4)','已出报告(4/4)，可点击查看'];		
		$res=$this->filter($row);
		$des=$descr[intval($res['step'])];
		$res['stepDescr']=$des;
		return ['sig'=>'0','res'=>$res];
	}

	function authp()	//验证基因卡和手机号绑定报告。
	{	
		if(!isset($_SESSION['AUTHCARDID']))
			return ['sig'=>28,'msg'=>'先请通过Auth认证!'];

		if($_SESSION['AUTHCALLN']!=$_REQUEST['tel']){	
			$phone=substr_replace($_SESSION['AUTHCALLN'],'*****',3,5);
			return ['sig'=>26,'msg'=>$phone];
		}
		//$row = $this->checkmgo(array('tel','code'),'alisms');
		$row = $this->checksms();
		if(empty($row)){
			return ['sig'=>27,'msg'=>'短信验证码不正确'];
		}
		return $this->Bundle($_SESSION['AUTHCARDID']);
	}
	
	function auth()	//20 验证基因卡和手机号绑定报告。
	{
		if(!isset($_SESSION['CALLNUMBER'])||$_SESSION['CALLNUMBER']=='')
			return ['sig'=>21,'msg'=>'请先注册基因说'];
		if(!isset($_REQUEST['name']) ||$_REQUEST['name']=='')
			return ['sig'=>22,'msg'=>'用户名不能为空'];
		$_REQUEST["name"] = urldecode($_REQUEST["name"]);
		$row=$this->checkmgo(array('_id','name'),'customer');
		if(empty($row))
			return ['sig'=>23,'msg'=>'查询不到用户，资料可能尚未录入系统!'];	//用户名不存在，改为可能末录入。
		$sets = $row['sets'];
		$alw = explode(',', "BO,BR,BT,BU,BW,BX,DA");	//array(BX','BV')
		if(in_array($sets,$alw))
			return ['sig'=>24,'msg'=>'相应在线报告正在研发中，敬请期待!','sets'=>$sets];	//用户名不存在，改为可能末录入。		
		$col = $this->cli->selectCollection("genetk","setInfos");
		$one =$col->findOne(array('_id'=>$sets));	//增加限定非基因说的报告绑定
		$one =$col->findOne(array('_id'=>$sets,'taoxi'=>'Xclient_jiyinshuoxilie'));	//增加限定非基因说的报告绑定
		if(empty($one))
			return ['sig'=>20,'msg'=>'此套餐不支持在线查看报告'];
		
		if($row['tel'] != $_SESSION['CALLNUMBER']){
			$_SESSION['AUTHCARDID']=$row['_id'];
			$_SESSION['AUTHCALLN']=$row['tel'];
			$phone=substr_replace($row['tel'],'*****',3,5);	
			return ['sig'=>25,'msg'=>'请输入检测时预留的手机号('.$phone.') 进行验证'];
		}else{
			return $this->Bundle($row['_id']);
		}
	}	
	
	function cardid()
	{
		$row = $this->checkmgo(array('_id'),'customer');
		if(empty($row))
			return ['sig'=>20,'msg'=>'编码不存在'];
		else
			return ['sig'=>0,'msg'=>'正确'];
	}

	function signature()
	{
		if(!isset($_REQUEST["name"])||$_REQUEST["name"]=='')
			return ['sig'=>12,'msg'=>'真实姓名不能为空!'];
		$na = base64_encode(urldecode($_REQUEST["name"]));	
		$col = $this->cli->selectCollection("genetk","weixin");
		$col->update(array("_id"=>$_SESSION['WEIXINOPENID']),array('$set'=>array('name'=>"$na")));
		
		/*
		try{
			$col = $this->cli->selectCollection("genetk","customer");
			$col->update(array("name"=>$_REQUEST["name"],'tel'=>$_SESSION['CALLNUMBER']),array('$set'=>array('weix'=>$_SESSION['WEIXINOPENID'])),array("multiple" => true));
		}catch(Exception $e){
			$msg=$e->getMessage();
		}*/
		$opi=$_SESSION['WEIXINOPENID'];
		$col = $this->cli->selectCollection("genetk","customer");
		$alw = array("name"=>$_REQUEST["name"],'tel'=>$_SESSION['CALLNUMBER']);
		$row =$col->findOne($alw);
		if(!empty($row)){
			if($row['weix']==''){
				$col->update($alw,array('$set'=>array('weix'=>$opi)));
			}else{
				$all=explode(',',$row['weix']);
				if(!in_array($opi, $all)){
					array_push($all,$opi);
					$wei = implode(',',$all);
					$col->update($arr,array('$set'=>array('weix'=>$wei)));
				}				
			}
			$col->update($arr,array('$set'=>array('weix'=>$opi)));
		}
		return ['sig'=>0];
	}
	
	function register()		//10 var_dump(iterator_to_array($cur));
	{
		$row = $this->checksms();
		if(empty($row)){
			return ['sig'=>11,'msg'=>'短信验证码错误'];
		}
		$col = $this->cli->selectCollection("genetk","weixin");
		$alw = $this->allows(array('tel'));
		$col->update(array("_id"=>$_SESSION['WEIXINOPENID']),array('$set'=>$alw));
		$_SESSION['CALLNUMBER']=$alw['tel'];
		return ['sig'=>0,'tel'=>$alw['tel']];
	}

	/* ERP 何彬提供短信发送 */
	function smscode()
	{
	    global $sms_url,$sms_arg;
		$tel=preg_replace('/[^\d]/', '', $_REQUEST['tel']);
		if(strlen($tel)<11)
			return ['sig'=>1,'msg'=>"请检查手机号码!"];
		$tme = time();
		$tm2 = $tme - 600;
		$col = $this->cli->selectCollection("genetk","alisms");
		$qey = array('tel'=>$tel,"tme"=>array('$gt'=>$tm2));		
		$cur = $col->find($qey)->sort(array('tme'=>-1));	
		$num =$cur->count();
		if($num > 1)
			return ['sig'=>18,'msg'=>"短信连续发送多次，请过几分钟再试!"];//
		
		if($num > 0){
			$doc=$cur->getNext();	
			$tm3 = $tme - intval($doc['tme']);
			if($tm3 < 60)
				return ['sig'=>0,'msg'=>"短信已经发送,验证码30分钟内有效!"];
		}
		
		$id = microtimes();
		$cod = rand(10000,99999);
		$dat = smsdata($tel,$cod,$tme);
		$res=curlpost($sms_url,$dat);
		$res='000 send 1 test content';
		if(preg_match('/^0{3}/',$res)){
			$col->insert(array("_id"=>$id,"tme"=>$tme,"tel"=>"$tel","code"=>"$cod"));
			$_SESSION['SMSDENYTIME']=$tme;
			return ['sig'=>0,'msg'=>'ok'];	//['sig'=>0,'msg'=>'短信验证码发送成功'];
		}else{
			return ['sig'=>19,'msg'=>'err'];	//['sig'=>19,'msg'=>'短信发送失败，请重新请求发送'];
		}
	}
	
	function checksms() 	//10
	{
		global $_REQUEST;
		$col = $this->cli->selectCollection("genetk","alisms");
		$tme = time() - 1800;
		$tel=isset($_REQUEST['tel']) ? $_REQUEST['tel'] :  '';
		$cod=isset($_REQUEST['code']) ? $_REQUEST['code'] :  '';
		$qey= array('tel'=>$tel,'code'=>$cod,'tme'=>array('$gt'=>$tme));
		return $col->findOne($qey);
	}
		
	function checkmgo($arr,$tab,$dbs='genetk') 	//10
	{
		$col = $this->cli->selectCollection($dbs,$tab);
		$alw = $this->allows($arr);
		return $col->findOne($alw);
	}
	
	function filter($arr,$alw=['_id','tme','name','title','step','header']){
		$row=array();
		foreach($alw as $al){
			if(isset($arr[$al]))
				$row[$al]=$arr[$al];
			else
				$row[$al]='';
		}
		return $row;
	}

	function allows($scope)
	{
		global $_REQUEST;
		$alw=array();
		foreach ($scope as $key){
			if(isset($_REQUEST[$key]))
				$alw[$key]=	$_REQUEST[$key];
			else
				$alw[$key]=	'';
		}
		return $alw;
	}
	
    function template($tna){	//2016.8.2 getProfile增加avatar图片链接
    	$tpl=['customer'=>['_id'=>'genecard','tme'=>'','name'=>'','gender'=>'','sets'=>'','tel'=>'','title'=>'','item'=>'','weix'=>'','step'=>''],
    	'weixin'=>['_id'=>'weixin','tme'=>'','user'=>'','name'=>'','sex'=>'','tel'=>'','province'=>'','city'=>'','addr'=>'','avatar'=>''],    			
    	'admin'=>['_id'=>'name','tme'=>'','usr'=>'','pwd'=>'','logip'=>'0.0.0.0'],
    	'logs'=>['_id'=>'tme','day'=>'tme','opt'=>'','act'=>'','descr'=>''],
    	'alisms'=>['_id'=>'time','tme'=>'','tel'=>'','code'=>'']
    	];
    	return $tpl[$tna];
    }
  
    function sms()
    {
    	$tel=preg_replace('/[^\d]/', '', $_REQUEST['tel']);
    	if(strlen($tel)<11) return ['sig'=>1,'msg'=>"Please Check the Cell number"];
    	$tme=time();
    	$id = microtimes();
    	//$cod =strval(rand(10000,99999));
    	$col = $this->cli->selectCollection("genetk","alisms");
    	$col->insert(array("_id"=>$id,"tme"=>$tme,"tel"=>$tel,"code"=>'55555'));
    	return ['sig'=>0,'res'=>['tel'=>$tel,'code'=>'55555']];
    }
    
    function wx()
    {
    	if(DEBUG && isset($_REQUEST["openid"])){
    		$opi =$_REQUEST["openid"];
    		$_SESSION['WEIXINOPENID']=$opi;
    		$col = $this->cli->selectCollection("genetk","weixin");
    		$row = $col->findOne(array("_id"=>"$opi"));
    		if(empty($row)){
    			$tme=strval(time());
    			$col->insert(['_id'=>$opi,'tme'=>$tme,'tel'=>'','user'=>'','name'=>'']);
    		}else {
    			if(isset($row['tel'])&&$row['tel']!=''){
    				$_SESSION['CALLNUMBER']=$row['tel'];
    			}
    		}
    		return ['sig'=>0,'msg'=>'OPENID:'.$opi];
    	}
    }
}
?>