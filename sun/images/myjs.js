function JSshow(id){
	var bjt=document.getElementById(id);
	if(bjt.style.display ==''){
		bjt.style.display ='none';
	}else{
		bjt.style.display ='';
	}
}

function JSwith(id,pt){
	var bjt=document.getElementById(pt);
	var v=bjt.value;
	bjt.value=id;
	var bjt=document.getElementById(v);
	bjt.style.display = 'none';
	var bjt=document.getElementById(id);
	bjt.style.display = '';
}

function setTab(name,cursel,n)
{
	for(i=1;i<=n;i++){
	//var menu=document.getElementById(name+i);/* zzjs1 */
	var con=document.getElementById("con_"+name+"_"+i);/* con_zzjs_1 */
	//menu.className=i==cursel?"hover":"";/*三目运算 等号优先*/
	con.style.display=i==cursel?"block":"none";
	}
}

function addshopitem()
{
	var svrobj=document.getElementById("wsvr");
	var svrid=svrobj.options[svrobj.selectedIndex].text;
	svrid="World of Warcraft-US_"+svrid;
	var pdcobj=document.getElementById("Product");
	var pdtid=pdcobj.options[pdcobj.selectedIndex].value;	
	var strs =pdcobj.options[pdcobj.selectedIndex].text;
	var pd =strs.split("G ==\$");
	addToCart(svrid,pdtid,pd[0],pd[1]);
}

function putswind(pid,kname,pdm){
	var opid = document.getElementById('Payid');
	var onam = document.getElementById('Nname');
	var opdm = document.getElementById('Problem');
	opid.value =pid;
	onam.value =kname;
	opdm.value =pdm;
	var bjt=document.getElementById('ddwin');
	bjt.style.display = '';
}

function rstWind(pid,kname,pdm){
	var wobj=document.getElementById('ddwin');
	wobj.style.display = 'none';
}

function addFavorites(){
    window.external.addFavorite(window.location.href,document.title); 
}

function addEmail(){
	if(window.confirm("Your Cart already has Items,Click \"OK\" to add Current Items"))
	{
		location = "shopping_cart.php?sid=" + sid + "&pid=" + pid ;
	}
}

function chkSubForm()
{
  var vEmail=regfrm.Email.value
  var vPass=regfrm.Pass.value
  var vTel=regfrm.Tel.value
  var vReg = /^([a-zA-Z0-9._-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/
  var rTel = /\d{3,3}-\d{3,3}-\d{4,4}/
  var fag=vReg.test(vEmail)
  var fag2=rTel.test(vTel)
  if(vUser==null||vUser==""){
    alert('Nickname must be input')
    regfrm.User.focus()
    return false
  }else if(!fag){
    alert('Email address format is illegal')
    regfrm.User.focus()
    return false
  }else if(!fag2){
    alert('Telephone Format (666-666-6666)')
    regfrm.Tel.focus()
    return false
  }else if(vPass==null||vPass==""){
    alert('Password must be input')
    regfrm.Tel.focus()
    return false
  }else if(vPass != forms.gPass.value){
    alert('Confirm Password Error')
    regfrm.Pass.focus()
    return false
  }
  return true
}

function JSfilform(id,v)
{
	var bjt=document.getElementById(id);
	bjt.value=v;
}

function filText(id,v,id2,v2)
{
	var bjt=document.getElementById(id);
	bjt.value=v;
	var bjt2=document.getElementById(id2);
	bjt2.value +="-"+v2;
}