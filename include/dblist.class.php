<?php   if(!defined('SUNINC')) exit('Request Error!');

require_once(SUNINC."/suntag.class.php");


class ListView
{
    var $dsql;
    var $dtp;
    var $dtp2;
    var $TypeID;
    var $TypeLink;
    var $PageNo;
    var $TotalPage;
    var $TotalResult;
    var $PageSize;
    var $templets;
    var $ListType;
    var $Fields;
    var $upPageType;
    var $addSql;
    var $IsError;
    var $CrossID;
    var $IsReplace;
    
    function __construct($tname, $uppage=1)
    {
        global $dsql,$ftp;

        $this->TableN = $tname;
        $this->dsql = &$dsql;
        $this->IsReplace = false;
        $this->IsError = false;
        $this->dtp = new SunTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("sun", "{", "}");
        $this->dtp2 = new SunTagParse();            
        $this->dtp2->SetNameSpace("field","[","]");
        $this->upPageType = $uppage;
        $this->TotalResult = is_numeric($this->TotalResult)? $this->TotalResult : "";
        
    }

    //php4构造函数
    function ListView($tname,$uppage=0){
        $this->__construct($tname,$uppage);
    }
    
    //关闭相关资源
    function Close()
    {

    }

    function SetTemplate()
    {

    }
    
    function CountRecord()
    {
        //统计数据库记录
        $this->TotalResult = -1;
        $this->dsql->GetTotal($this->TableN)
        if(isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if(isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;

        $tempfile = SUNTPL."/default/archives_list.htm";
        if(!file_exists($tempfile)||!is_file($tempfile))
        {
            echo "模板文件不存在，无法解析文档！";
            exit();
        }
        $this->dtp->LoadTemplate($tempfile);
        $ctag = $this->dtp->GetTag("pagelist");
        if(!is_object($ctag))
        {
            $ctag = $this->dtp->GetTag("list");
        }
        if(!is_object($ctag))
        {
            $this->PageSize = 20;
        }
        else
        {
            if($ctag->GetAtt("pagesize")!="")
            {
                $this->PageSize = $ctag->GetAtt("pagesize");
            }
            else
            {
                $this->PageSize = 20;
            }
        }
        $this->TotalPage = ceil($this->TotalResult/$this->PageSize);
    }

    function Display()
    {
        $this->CountRecord();
        $this->ParseDMFields($this->TableN,$this->PageNo,0);     
        $this->dtp->Display();

        
    }

    function ParseDMFields($tname,$PageNo,$ismake=1)
    {
        foreach($this->dtp->CTags as $tagid=>$ctag)
        {
            if($ctag->GetName()=="list")
            {
                $limitstart = ($this->PageNo-1) * $this->PageSize;
                $row = $this->PageSize;
                $InnerText = trim($ctag->GetInnerText());
                $this->dtp->Assign($tagid,
                $this->GetArcList(
                $tname,
                $limitstart,
                $row,
                $ctag->GetAtt("col"),
                $ctag->GetAtt("titlelen"),
                $ctag->GetAtt("infolen"),
                $ctag->GetAtt("imgwidth"),
                $ctag->GetAtt("imgheight"),
                $ctag->GetAtt("listtype"),
                $ctag->GetAtt("orderby"),
                $InnerText,
                $ctag->GetAtt("tablewidth"),
                $ismake,
                $ctag->GetAtt("orderway")
                )
                );
            }
            else if($ctag->GetName()=="pagelist")
            {
                $list_len = trim($ctag->GetAtt("listsize"));
                $ctag->GetAtt("listitem")=="" ? $listitem="index,pre,pageno,next,end,option" : $listitem=$ctag->GetAtt("listitem");
                $this->dtp->Assign($tagid,$this->GetPageListDM($list_len,$listitem));
            }
            else if($PageNo!=1 && $ctag->GetName()=='field' && $ctag->GetAtt('display')!='')
            {
                $this->dtp->Assign($tagid,'');
            }
        }
    }

    function GetArcList($tname,$limitstart=0,$row=10,$col=1,$titlelen=30,$infolen=250,
    $imgwidth=120,$imgheight=90,$listtype="all",$orderby="default",$innertext="",$tablewidth="100",$ismake=1,$orderWay='desc')
    {
        global $cfg_list_son,$cfg_digg_update;
        
        $typeid=$this->TypeID;
        if($row=='') $row = 10;
        if($limitstart=='') $limitstart = 0;
        if($titlelen=='') $titlelen = 100;
        if($infolen=='') $infolen = 250;
        if($imgwidth=='') $imgwidth = 120;
        if($imgheight=='') $imgheight = 120;
        if($listtype=='') $listtype = 'all';
        if($orderWay=='') $orderWay = 'desc';
        
        if($orderby=='') {
            $orderby='default';
        }
        else {
            $orderby=strtolower($orderby);
        }
        
        $tablewidth = str_replace('%','',$tablewidth);
        if($tablewidth=='') $tablewidth=100;
        if($col=='') $col=1;
        $colWidth = ceil(100/$col);
        $tablewidth = $tablewidth.'%';
        $colWidth = $colWidth.'%';
        
        $innertext = trim($innertext);


        //排序方式
        $ordersql = '';

        $query = "SELECT *  FROM `#@__{$tname}` arc $ordersql LIMIT $limitstart,$row";
        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');

        $artlist = '';
        $this->dtp2->LoadSource($innertext);
        $GLOBALS['autoindex'] = 0;
        for($i=0;$i<$row;$i++)
        {
            if($col>1)
            {
                $artlist .= "<div>\r\n";
            }
            for($j=0;$j<$col;$j++)
            {
                if($row = $this->dsql->GetArray("al"))
                {
                    $GLOBALS['autoindex']++;
                    //编译附加表里的数据
                    if(is_array($this->dtp2->CTags))
                    {
                        foreach($this->dtp2->CTags as $k=>$ctag)
                        {
                            if(isset($row[$ctag->GetName()]))
                            {
                                $this->dtp2->Assign($k,$row[$ctag->GetName()]);
                            }
                            else
                            {
                                $this->dtp2->Assign($k,'');
                            }
                        }
                    }
                    $artlist .= $this->dtp2->GetResult();
                }

            }//Loop Col

            if($col>1)
            {
                $i += $col - 1;
                $artlist .= "    </div>\r\n";
            }
        }
        return $artlist;
    }
}