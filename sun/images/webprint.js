function doprint() {
   //�����ͻ���ӡ������
   var h = factory.printing.header;
   var f = factory.printing.footer;
   var t = factory.printing.topMargin;
   var b = factory.printing.bottomMargin;
   var l = factory.printing.leftMargin;
   var r = factory.printing.rightMargin;

   document.all("printbtn").style.visibility = 'hidden';//��ӡʱ���ش�ӡ��ť
   //����ҳüҳ���������ұ߾�
   factory.printing.header = "ҳü+_+ohiolee�Ĵ�ӡ����";
   factory.printing.footer = "������ҳ��ô������Ŷ";
   factory.printing.topMargin="6";//������СĬ��ֵ5.02
   factory.printing.bottomMargin="6";//������СĬ��ֵ4.13
   factory.printing.leftMargin="2";//������СĬ��ֵ5.08
   factory.printing.rightMargin="2";//������СĬ��ֵ6.79���������˻����ϲ�����������ģ���֪���ձ��Ƿ���ˡ�
   // ֱ�Ӵ�ӡ
   factory.DoPrint(false);//trueʱ������ӡ�Ի���
   //���ص�ԭ���Ĵ�ӡ����
   factory.printing.header = h;
   factory.printing.footer = f;
   factory.printing.topMargin=t;
   factory.printing.bottomMargin=b;
   factory.printing.leftMargin=l;
   factory.printing.rightMargin=r;
   //��ʾ��ӡ��ť
   document.all("printbtn").style.visibility = 'visible';//ͨ��document.all("printbtn").��ָ��ҳ���е��κ��࣬�����Խ�һ����������
}
function printpr()   //Ԥ������
{
	var OLECMDID = 7;
	var PROMPT = 1; 
	var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
	document.body.insertAdjacentHTML('beforeEnd', WebBrowser); 
	WebBrowser1.ExecWB(OLECMDID, PROMPT);
	WebBrowser1.outerHTML = "";
} 

function printTure()   //��ӡ����
{
    document.all('qingkongyema').click();//ͬ��
    document.all("dayinDiv").style.display="none";//ͬ��
    window.print();
    document.all("dayinDiv").style.display="";
}
function doPage()
{
    layLoading.style.display = "none";//ͬ��
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

function pagesetup_null(){//��ӡǰ���к��������ҳüҳ��       
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