function doprint() {
   //保留客户打印机设置
   var h = factory.printing.header;
   var f = factory.printing.footer;
   var t = factory.printing.topMargin;
   var b = factory.printing.bottomMargin;
   var l = factory.printing.leftMargin;
   var r = factory.printing.rightMargin;

   document.all("printbtn").style.visibility = 'hidden';//打印时隐藏打印按钮
   //设置页眉页脚上下左右边距
   factory.printing.header = "页眉+_+ohiolee的打印世界";
   factory.printing.footer = "想设置页脚么，这里哦";
   factory.printing.topMargin="6";//存在最小默认值5.02
   factory.printing.bottomMargin="6";//存在最小默认值4.13
   factory.printing.leftMargin="2";//存在最小默认值5.08
   factory.printing.rightMargin="2";//存在最小默认值6.79。。。本人机子上测出来是这样的，不知道普遍是否如此。
   // 直接打印
   factory.DoPrint(false);//true时弹出打印对话框
   //返回到原来的打印设置
   factory.printing.header = h;
   factory.printing.footer = f;
   factory.printing.topMargin=t;
   factory.printing.bottomMargin=b;
   factory.printing.leftMargin=l;
   factory.printing.rightMargin=r;
   //显示打印按钮
   document.all("printbtn").style.visibility = 'visible';//通过document.all("printbtn").来指定页面中的任何类，并给以进一步属性设置
}
function printpr()   //预览函数
{
	var OLECMDID = 7;
	var PROMPT = 1; 
	var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
	document.body.insertAdjacentHTML('beforeEnd', WebBrowser); 
	WebBrowser1.ExecWB(OLECMDID, PROMPT);
	WebBrowser1.outerHTML = "";
} 

function printTure()   //打印函数
{
    document.all('qingkongyema').click();//同上
    document.all("dayinDiv").style.display="none";//同上
    window.print();
    document.all("dayinDiv").style.display="";
}
function doPage()
{
    layLoading.style.display = "none";//同上
}
function preview() 
{ 
	pagesetup_null();
	bdhtml=window.document.body.innerHTML; 
	sprnstr="<!--startprint-->"; 
	eprnstr="<!--endprint-->"; 
	prnhtml=bdhtml.substr(bdhtml.indexOf(sprnstr)+17); 
	prnhtml=prnhtml.substring(0,prnhtml.indexOf(eprnstr)); 
	window.document.body.innerHTML=prnhtml; 
	window.print(); 
	window.document.body.innerHTML=bdhtml;
}

function pagesetup_null(){//打印前运行函数，清空页眉页脚       
	var hkey_root,hkey_path,hkey_key;
	hkey_root="HKEY_CURRENT_USER"
	hkey_path="\\Software\\Microsoft\\Internet Explorer\\PageSetup\\";
	try{
		var RegWsh = new ActiveXObject("WScript.Shell");
		hkey_key="header";
		RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"");
		hkey_key="footer";
		RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"");
	}catch(e){}
}