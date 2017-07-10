var $j = jQuery.noConflict();
function checkall(chk){
	$j("input[name='ids[]']").attr("checked",chk);
}
function sysbar(obj){
	if (obj.type =="mouseout"){
		$j('#sysbar').hide();			
	} else {
		$j('#sysbar').show();
	}
}

$j(function(){$j('.tb_click_close').click(function(){
	$j(this).parent().hide();
	})
})

function issue(tid){
	$j.get('my_makearchives.php',{'aid':tid});
	//$j('#tip').html('makearchives');
}

function Dimensions() 
{ //获取窗口宽度 
	var topHeight=36; //顶条宽
	var winWidth = 0; 
	var winHeight = 0;
	if (window.innerWidth) winWidth = window.innerWidth;
	else if ((document.body) && (document.body.clientWidth)) winWidth = document.body.clientWidth; //获取窗口高度 
	if (window.innerHeight) winHeight = window.innerHeight; 
	else if ((document.body) && (document.body.clientHeight)) winHeight = document.body.clientHeight; 
	//通过深入Document内部对body进行检测，获取窗口大小 
	if (document.documentElement && document.documentElement.clientHeight && document.documentElement.clientWidth){
		winHeight = document.documentElement.clientHeight;
		winWidth = document.documentElement.clientWidth; 
	}
	//document.getElementById("mainFrame").style.width=winWidth+'px';
	document.getElementById("mainFrame").style.height=(winHeight-topHeight)+'px';
}
function ShowNode(id){
	var bjt=document.getElementById('d'+id);
	if(bjt.style.display =='none'){
		bjt.style.display ='';
		document.getElementById('e'+id).innerHTML="-";
	}else{
		bjt.style.display ='none';
		document.getElementById('e'+id).innerHTML="+";
	}
}
function JSMgSort(act,id){
	var tip=new Array("","输入子类名","输入新类名","输入父类号","确定删除分类");
	var	sot=window.prompt(tip[act]);
	if(sot!=null&&sot!="")
	{
		window.location.href='sortree.php?cmd='+act+'&tid='+id+'&sname='+sot+'&id='+Math.random();
	}else{
		alert("输入为空");
	}
}
