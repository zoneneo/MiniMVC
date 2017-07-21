<?php   if(!defined('SUNINC')) exit('Request Error!');

require_once(SUNINC."/suntag.class.php");
require_once(SUNINC.'/typelink.class.php');

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
    
    function __construct($typeid, $uppage=1)
    {
        global $dsql,$ftp;

        $this->TypeID = $typeid;
        $this->dsql = &$dsql;
        $this->IsReplace = false;
        $this->IsError = false;
        $this->dtp = new SunTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("sun", "{", "}");
        $this->dtp2 = new SunTagParse();            
        $this->dtp2->SetNameSpace("field","[","]");
        $this->TypeLink = new TypeLink($typeid);
        $this->upPageType = $uppage;
        $this->TotalResult = is_numeric($this->TotalResult)? $this->TotalResult : "";
        //$this->Fields=$this->dsql->GetOne("SELECT id,typedir,tempindex,templist,namerule,namerule2 FROM #@__arctype WHERE id={$typeid}");
        if(!is_array($this->TypeLink->TypeInfos))
        {
            $this->IsError = true;
        }
        if(!$this->IsError)
        {       
            //$this->ChannelUnit = new ChannelUnit($this->TypeLink->TypeInfos['channeltype']);
            $this->Fields = $this->TypeLink->TypeInfos;
            $this->Fields['id'] = $typeid;
            // $this->Fields['position'] = $this->TypeLink->GetPositionLink(true);
            // $this->Fields['title'] = preg_replace("/[<>]/", " / ", $this->TypeLink->GetPositionLink(false));    
        }//!error 
    }


    //php4构造函数
    function ListView($typeid,$uppage=0){
        $this->__construct($typeid,$uppage);
    }
    
    //关闭相关资源
    function Close()
    {

    }

    function CountRecord()
    {
        //统计数据库记录
        $this->TotalResult = -1;
        if(isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if(isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;

        //$sonids = GetSonIds($this->TypeID,$this->Fields['channeltype']);
        $sonids = GetSonIds($this->TypeID); 
        if(!preg_match("/,/", $sonids)) {
            $sonidsCon = " arc.typeid = '$sonids' ";
        }
        else {
            $sonidsCon = " arc.typeid IN($sonids) ";
        }
        if($this->TotalResult==-1)
        {
            $cquery = "SELECT COUNT(*) AS dd FROM `#@__archives` arc WHERE ".$sonidsCon;
            $row = $this->dsql->GetOne($cquery);
            if(is_array($row))
            {
                $this->TotalResult = $row['dd'];
            }
            else
            {
                $this->TotalResult = 0;
            }
        }
        #$tempfile = SUNTPL."/default/list_default.htm";
        $tempfile = SUNTPL."/".$this->TypeLink->TypeInfos['templist'];
        $tempfile = str_replace("{tid}", $this->TypeID, $tempfile);
        echo 'temp file: '.$tempfile;

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
        // $tempfile = SUNTPL."/default/archives_list.htm";       
        // $this->dtp->LoadTemplate($tempfile);
        $this->ParseDMFields($this->PageNo,0);     
        $this->dtp->Display();

        
    }

    function ParseDMFields($PageNo,$ismake=1)
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

    function GetArcList($limitstart=0,$row=10,$col=1,$titlelen=30,$infolen=250,
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
        $sonids = GetSonIds($this->TypeID); 
        if(!preg_match("/,/", $sonids)) {
            $sonidsCon = " arc.typeid = '$sonids' ";
        }
        else {
            $sonidsCon = " arc.typeid IN($sonids) ";
        }
        //$query = "SELECT *  FROM `#@__archives` arc WHERE $sonidsCon $ordersql LIMIT $limitstart,$row";
        $query = "SELECT arc.*,tp.namerule,tp.namerule2,tp.isdefault,tp.defaultname,tp.typedir FROM `#@__archives` arc  
        LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id WHERE $sonidsCon $ordersql LIMIT $limitstart,$row";

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
                    //function GetFileUrl($aid,$typeid,$timetag,$title,$ismake=0,$rank=0,$namerule='',$typedir='', $money=0, $filename='',$moresite=0,$siteurl='',$sitepath='')
                    $row['filename'] = $row['arcurl'] = GetFileUrl($row['id'],$row['typeid'],$row['senddate'],$row['title'],0,0,$row['namerule'],$row['typedir'],$row['filename']);
                    $row['typeurl'] = GetTypeUrl($row['typeid'],MfTypedir($row['typedir']),$row['isdefault'],$row['defaultname'],$row['namerule2'],$row['siteurl']);
                    if($row['litpic'] == '-' || $row['litpic'] == '')
                    {
                        $row['litpic'] = $GLOBALS['cfg_cmspath'].'/images/defaultpic.gif';
                    }
                    if(!preg_match("/^http:\/\//i", $row['litpic']))
                    {
                        $row['litpic'] = $GLOBALS['cfg_cmspath'].$GLOBALS['cfg_mediasurl'].'/'.$row['litpic'];
                    }
                    if(preg_match('/c/', $row['flag']))
                    {
                        $row['title'] = "<b>".$row['title']."</b>";
                    }

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
                }//if hasRow

            }//Loop Col

            if($col>1)
            {
                $i += $col - 1;
                $artlist .= "    </div>\r\n";
            }
        }//Loop Line
        //$this->dsql->FreeResult('al');
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

    /**
     *  列表创建HTML
     *
     * @access    public
     * @param     string  $startpage  开始页面
     * @param     string  $makepagesize  创建文件数目
     * @param     string  $isremote  是否为远程
     * @return    string
     */
    function MakeHtml($startpage=1, $makepagesize=0, $isremote=0)
    {
        global $cfg_remote_site;
        if(empty($startpage))
        {
            $startpage = 1;
        }

        $this->CountRecord();
        //初步给固定值的标记赋值
        //$this->ParseTempletsFirst();
        $totalpage = ceil($this->TotalResult/$this->PageSize);
        if($totalpage==0)
        {
            $totalpage = 1;
        }
        echo '<br>typedir is:'.MfTypedir($this->Fields['typedir']);
        CreateDir(MfTypedir($this->Fields['typedir']));
        $murl = '';
        if($makepagesize > 0)
        {
            $endpage = $startpage+$makepagesize;
        }
        else
        {
            $endpage = ($totalpage+1);
        }
        if( $endpage >= $totalpage+1 )
        {
            $endpage = $totalpage+1;
        }
        if($endpage==1)
        {
            $endpage = 2;
        }
        for($this->PageNo=$startpage; $this->PageNo < $endpage; $this->PageNo++)
        {
            $this->ParseDMFields($this->PageNo,1);
            $makeFile = $this->GetMakeFileRule($this->Fields['id'],'list',$this->Fields['typedir'],'',$this->Fields['namerule2']);
            $makeFile = str_replace("{page}", $this->PageNo, $makeFile);
            $murl = $makeFile;
            if(!preg_match("/^\//", $makeFile))
            {
                $makeFile = "/".$makeFile;
            }
            $makeFile = $this->GetTruePath().$makeFile;
            $makeFile = preg_replace("/\/{1,}/", "/", $makeFile);
            $murl = $this->GetTrueUrl($murl);
            $this->dtp->SaveTo($makeFile);
        }
        if($startpage==1)
        {
            //如果列表启用封面文件，复制这个文件第一页
            if($this->TypeLink->TypeInfos['isdefault']==1
            && $this->TypeLink->TypeInfos['ispart']==0)
            {
                $onlyrule = $this->GetMakeFileRule($this->Fields['id'],"list",$this->Fields['typedir'],'',$this->Fields['namerule2']);
                $onlyrule = str_replace("{page}","1",$onlyrule);
                $list_1 = $this->GetTruePath().$onlyrule;
                $murl = MfTypedir($this->Fields['typedir']).'/'.$this->Fields['defaultname'];
                $indexname = $this->GetTruePath().$murl;
                copy($list_1,$indexname);
            }
        }
        return $murl;
    }

    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    public
     * @return    string
     */
    function ParseTempletsFirst()
    {
        // if(isset($this->TypeLink->TypeInfos['reid']))
        // {
        //     $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        // }
        // $GLOBALS['envs']['typeid'] = $this->TypeID;
        // $GLOBALS['envs']['topid'] = GetTopid($this->Fields['typeid']);
        // MakeOneTag($this->dtp,$this);
    }

    /**
     *  获得站点的真实根路径
     *
     * @access    public
     * @return    string
     */
    function GetTruePath()
    {
        $truepath = $GLOBALS["cfg_basedir"];
        return $truepath;
    }

    /**
     *  获得真实连接路径
     *
     * @access    public
     * @param     string  $nurl  地址
     * @return    string
     */
    function GetTrueUrl($nurl)
    {
        if($this->Fields['moresite']==1)
        {
            if($this->Fields['sitepath']!='')
            {
                $nurl = preg_replace("/^".$this->Fields['sitepath']."/", '', $nurl);
            }
            $nurl = $this->Fields['siteurl'].$nurl;
        }
        return $nurl;
    }

    /**
     *  获得要创建的文件名称规则
     *
     * @access    public
     * @param     int  $typeid  栏目ID
     * @param     string  $wname
     * @param     string  $typedir  栏目目录
     * @param     string  $defaultname  默认名称
     * @param     string  $namerule2  栏目规则
     * @return    string
     */
    function GetMakeFileRule($typeid,$wname,$typedir,$defaultname,$namerule2)
    {
        $typedir = MfTypedir($typedir);
        if($wname=='index')
        {
            return $typedir.'/'.$defaultname;
        }
        else
        {
            $namerule2 = str_replace('{tid}',$typeid,$namerule2);
            $namerule2 = str_replace('{typedir}',$typedir,$namerule2);
            return $namerule2;
        }
    }

}