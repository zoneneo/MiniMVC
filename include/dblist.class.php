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
    function ListView($dbsql,$tname,$uppage=0){
        $this->__construct($dbsql,$tname,$uppage);
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
        $this->dsql->GetTotal($this->TableN);
        if(isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if(isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;

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

        $tempfile = SUNTPL."/default/".$this->TableN."_list.htm";
        if(!file_exists($tempfile)||!is_file($tempfile))
        {
            echo "模板文件不存在，无法解析文档！";
            exit();
        }
        $this->dtp->LoadTemplate($tempfile);

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
                    $row['to_table']=$GLOBALS['cfg_to_table'];
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
    function GetPageListDM($list_len=3,$listitem="index,end,pre,next,pageno")
    {
        global $cfg_rewrite;
        $prepage = $nextpage = '';
        $prepagenum = $this->PageNo-1;
        $nextpagenum = $this->PageNo+1;
        if($list_len=='' || preg_match("/[^0-9]/", $list_len))
        {
            $list_len=3;
        }
        $totalpage = ceil($this->TotalResult/$this->PageSize);
        if($totalpage<=1 && $this->TotalResult>0)
        {
            return "<li><span class=\"pageinfo\">共 1 页/".$this->TotalResult." 条记录</span></li>\r\n";
        }
        if($this->TotalResult == 0)
        {
            return "<li><span class=\"pageinfo\">共 0 页/".$this->TotalResult." 条记录</span></li>\r\n";
        }
        $maininfo = "<li><span class=\"pageinfo\">共 <strong>{$totalpage}</strong>页<strong>".$this->TotalResult."</strong>条</span></li>\r\n";
        
        $purl = $this->GetCurUrl();
        // 如果开启为静态,则对规则进行替换
        if($cfg_rewrite == 'Y')
        {
            $nowurls = preg_replace("/\-/", ".php?", $purl);
            $nowurls = explode("?", $nowurls);
            $purl = $nowurls[0];
        }

        $geturl = "tid=".$this->TypeID."&TotalResult=".$this->TotalResult."&";
        $purl .= '?'.$geturl;
        
        $optionlist = '';

        //获得上一页和下一页的链接
        if($this->PageNo != 1)
        {
            $prepage.="<li><a href='".$purl."PageNo=$prepagenum'>上一页</a></li>\r\n";
            $indexpage="<li><a href='".$purl."PageNo=1'>首页</a></li>\r\n";
        }
        else
        {
            $indexpage="<li><a>首页</a></li>\r\n";
        }
        if($this->PageNo!=$totalpage && $totalpage>1)
        {
            $nextpage.="<li><a href='".$purl."PageNo=$nextpagenum'>下一页</a></li>\r\n";
            $endpage="<li><a href='".$purl."PageNo=$totalpage'>末页</a></li>\r\n";
        }
        else
        {
            $endpage="<li><a>末页</a></li>\r\n";
        }


        //获得数字链接
        $listdd="";
        $total_list = $list_len * 2 + 1;
        if($this->PageNo >= $total_list)
        {
            $j = $this->PageNo-$list_len;
            $total_list = $this->PageNo+$list_len;
            if($total_list>$totalpage)
            {
                $total_list=$totalpage;
            }
        }
        else
        {
            $j=1;
            if($total_list>$totalpage)
            {
                $total_list=$totalpage;
            }
        }
        for($j;$j<=$total_list;$j++)
        {
            if($j==$this->PageNo)
            {
                $listdd.= "<li class=\"thisclass\"><a>$j</a></li>\r\n";
            }
            else
            {
                $listdd.="<li><a href='".$purl."PageNo=$j'>".$j."</a></li>\r\n";
            }
        }

        $plist = '';
        if(preg_match('/index/i', $listitem)) $plist .= $indexpage;
        if(preg_match('/pre/i', $listitem)) $plist .= $prepage;
        if(preg_match('/pageno/i', $listitem)) $plist .= $listdd;
        if(preg_match('/next/i', $listitem)) $plist .= $nextpage;
        if(preg_match('/end/i', $listitem)) $plist .= $endpage;
        if(preg_match('/option/i', $listitem)) $plist .= $optionlist;
        if(preg_match('/info/i', $listitem)) $plist .= $maininfo;
        
        return $plist;
    }

    function GetCurUrl()
    {
        if(!empty($_SERVER['REQUEST_URI']))
        {
            $nowurl = $_SERVER['REQUEST_URI'];
            $nowurls = explode('?', $nowurl);
            $nowurl = $nowurls[0];
        }
        else
        {
            $nowurl = $_SERVER['PHP_SELF'];
        }
        return $nowurl;
    }

}