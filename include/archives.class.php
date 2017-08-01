<?php
if(!defined('SUNINC')) exit("Request Error!");
/**
 * 文档类
 *
 * @version        $Id: arc.archives.class.php 4 15:13 2010年7月7日Z tianya $
 * @package        SunCMS.Libraries
 * @copyright      Copyright (c) 2016 - , Inc.
 */
require_once(SUNINC."/typelink.class.php");
require_once(SUNINC."/channelunit.class.php");
//require_once(SUNINC.'/ftp.class.php');
@set_time_limit(0);
/**
 * 主文档类(Archives类)
 * @package   TypeLink
 */
class Archives
{
    var $TypeLink;
    var $ChannelUnit;
    var $dsql;
    var $Fields;
    var $dtp;
    var $ArcID;
    var $SplitPageField;
    var $SplitFields;
    var $NowPage;
    var $TotalPage;
    var $NameFirst;
    var $ShortName;
    var $FixedValues;
    var $TempSource;
    var $IsError;
    var $SplitTitles;
    var $PreNext;
    var $addTableRow;
    var $ftp;
    var $remoteDir;

    /**
     *  php5构造函数
     *
     * @access    public
     * @param     int  $aid  文档ID
     * @return    string
     */
    function __construct($aid)
    {
        global $dsql,$ftp;
        $this->IsError = FALSE;
        $this->ArcID = $aid;
        $this->PreNext = array();

        $this->dsql = $dsql;
        $query = "SELECT typeid FROM `#@__archives` WHERE id='$aid' ";
        $arr = $this->dsql->GetOne($query);
        $tid = intval($arr['typeid']);
        $this->TypeLink = new TypeLink($tid);
        // 如果当前文档不是系统模型,为单表模型
        $query = "SELECT arc.*,tp.reid,tp.typedir
        FROM `#@__archives` arc
        LEFT JOIN #@__arctype tp on tp.id=arc.typeid
        WHERE arc.id='$aid' ";
        $this->Fields = $this->dsql->GetOne($query);

        if($this->TypeLink->TypeInfos['corank'] > 0 && $this->Fields['arcrank']==0)
        {
            $this->Fields['arcrank'] = $this->TypeLink->TypeInfos['corank'];
        }

        $this->Fields['tags'] = GetTags($aid);
        $this->dtp = new SunTagParse();
        $this->dtp->SetRefObj($this);
        $this->SplitPageField =	'';	 //$this->ChannelUnit->SplitPageField;
        $this->SplitFields = '';
        $this->TotalPage = 1;
        $this->NameFirst = '';
        $this->ShortName = 'html';
        $this->FixedValues = '';
        $this->TempSource = '';
        $this->ftp = &$ftp;
        $this->remoteDir = '';
        if(empty($GLOBALS['pageno']))
        {
            $this->NowPage = 1;
        }
        else
        {
            $this->NowPage = $GLOBALS['pageno'];
        }
        //特殊的字段数据处理
        $this->Fields['aid'] = $aid;
        $this->Fields['id'] = $aid;
        //$this->Fields['position'] = $this->TypeLink->GetPositionLink(TRUE);
        $this->Fields['position'] = '';
        $this->Fields['typeid'] = $arr['typeid'];
        //设置一些全局参数的值
        foreach($GLOBALS['PubFields'] as $k=>$v)
        {
            $this->Fields[$k] = $v;
        }
        }

    //php4构造函数
    function Archives($aid)
    {
        $this->__construct($aid);
    }

    /**
     *  解析附加表的内容
     *
     * @access    public
     * @return    void
     */
    function ParAddTable()
    {
        //处理默认缩略图等
        if (isset($this->Fields['litpic']))
        {
            if($this->Fields['litpic'] == '-' || $this->Fields['litpic'] == '')
            {
                $this->Fields['litpic'] = $GLOBALS['cfg_cmspath'].'/images/defaultpic.gif';
            }
            if(!preg_match("#^http:\/\/#i", $this->Fields['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y')
            {
                $this->Fields['litpic'] = $GLOBALS['cfg_mainsite'].$this->Fields['litpic'];
            }
            $this->Fields['picname'] = $this->Fields['litpic'];
            
            //模板里直接使用{sun:field name='image'/}获取缩略图
            $this->Fields['image'] = (!preg_match('/jpg|gif|png/i', $this->Fields['picname']) ? '' : "<img src='{$this->Fields['picname']}' />");
        }
    }

    //获得当前字段参数
    function GetCurTag($fieldname)
    {
        if(!isset($this->dtp->CTags))
        {
            return '';
        }
        foreach($this->dtp->CTags as $ctag)
        {
            if($ctag->GetTagName()=='field' && $ctag->GetAtt('name')==$fieldname)
            {
                return $ctag;
            }
            else
            {
                continue;
            }
        }
        return '';
    }

    /**
     *  生成静态HTML
     *
     * @access    public
     * @param     int    $isremote  是否远程
     * @return    string
     */
    function MakeHtml($isremote=0)
    {
        global $cfg_remote_site,$fileFirst;
        $this->Fields["displaytype"] = "st";
        //预编译$th
        $this->LoadTemplet();
        $this->ParAddTable();
        $this->ParseTempletsFirst();
        $this->Fields['senddate'] = empty($this->Fields['senddate'])? '' : $this->Fields['senddate'];
        $this->Fields['title'] = empty($this->Fields['title'])? '' : $this->Fields['title'];
        $this->Fields['arcrank'] = empty($this->Fields['arcrank'])? 0 : $this->Fields['arcrank'];
        $this->Fields['ismake'] = empty($this->Fields['ismake'])? 0 : $this->Fields['ismake'];
        $this->Fields['money'] = empty($this->Fields['money'])? 0 : $this->Fields['money'];
        $this->Fields['filename'] = empty($this->Fields['filename'])? '' : $this->Fields['filename'];

        //分析要创建的文件名称
        // function GetFileNewName($aid,$typeid,$timetag,$title,$ismake=0,$rank=0,$namerule='',$typedir='',$money=0,$filename='')
        $filename = GetFileNewName($this->ArcID,$this->Fields['typeid'],$this->Fields['senddate'],$this->Fields['title'],1,0,
            $this->TypeLink->TypeInfos['namerule'],$this->TypeLink->TypeInfos['typedir'],0,$this->Fields['filename']);

        $filenames  = explode(".", $filename);
        $this->ShortName = $filenames[count($filenames)-1];
        if($this->ShortName=='') $this->ShortName = 'html';
        $fileFirst = preg_replace("/\.".$this->ShortName."$/i", "", $filename);
        $this->Fields['namehand'] = basename($fileFirst);
        $filenames  = explode("/", $filename);
        $this->NameFirst = preg_replace("/\.".$this->ShortName."$/i", "", $filenames[count($filenames)-1]);
        if($this->NameFirst=='')
        {
            $this->NameFirst = $this->arcID;
        }

    //function GetFileUrl($aid,$typeid,$timetag,$title,$ismake=0,$rank=0,$namerule='',$typedir='',$money=0, lename='',$moresite=0,$siteurl='',$sitepath='')

        //获得当前文档的全名
        $filenameFull = GetFileUrl($this->ArcID,$this->Fields['typeid'],$this->Fields["senddate"],$this->Fields["title"],1,0,
		$this->TypeLink->TypeInfos['namerule'],$this->TypeLink->TypeInfos['typedir']);

        $this->Fields['arcurl'] = $this->Fields['fullname'] = $filenameFull;
        $this->ParseDMFields(1,1);
        $this->dtp->SaveTo($this->GetTruePath().'/'.$filename);
        $this->dsql->query("Update `#@__archives` SET ismake=1 WHERE id='".$this->ArcID."'");
        return $this->GetTrueUrl($filename);
    }

    /**
     *  获得真实连接路径
     *
     * @access    public
     * @param     string    $nurl  连接
     * @return    string
     */
    function GetTrueUrl($nurl)
    {
        return GetFileUrl
        (
            $this->Fields['id'],
            $this->Fields['typeid'],
            $this->Fields['senddate'],
            $this->Fields['title'],
            $this->Fields['ismake'],
            $this->Fields['arcrank'],
            $this->TypeLink->TypeInfos['namerule'],
            $this->TypeLink->TypeInfos['typedir'],
            $this->Fields['money'],
            $this->Fields['filename'],
            $this->TypeLink->TypeInfos['moresite'],
            $this->TypeLink->TypeInfos['siteurl'],
            $this->TypeLink->TypeInfos['sitepath']
        );
    }

    /**
     *  获得站点的真实根路径
     *
     * @access    public
     * @return    string
     */
    function GetTruePath()
    {
        $TRUEpath = $GLOBALS["cfg_basedir"];
        return $TRUEpath;
    }

    /**
     *  获得指定键值的字段
     *
     * @access    public
     * @param     string  $fname  键名称
     * @param     string  $ctag  标记
     * @return    string
     */
    function GetField($fname, $ctag)
    {
        //所有Field数组 OR 普通Field
        if($fname=='array')
        {
            return $this->Fields;
        }
        //指定了ID的节点
        else if($ctag->GetAtt('noteid') != '')
        {
            if( isset($this->Fields[$fname.'_'.$ctag->GetAtt('noteid')]) )
            {
                return $this->Fields[$fname.'_'.$ctag->GetAtt('noteid')];
            }
        }
        //指定了type的节点
        else if($ctag->GetAtt('type') != '')
        {
            if( isset($this->Fields[$fname.'_'.$ctag->GetAtt('type')]) )
            {
                return $this->Fields[$fname.'_'.$ctag->GetAtt('type')];
            }
        }
        else if( isset($this->Fields[$fname]) )
        {
            return $this->Fields[$fname];
        }
        return '';
    }

    /**
     *  获得模板文件位置
     *
     * @access    public
     * @return    string
     */
    function GetTempletFile()
    {
        global $cfg_basedir,$cfg_templets_dir,$cfg_df_style;
        if(!empty($this->Fields['templet']))
        {
            $filetag = MfTemplet($this->Fields['templet']);
            if( !preg_match("#\/#", $filetag) ) $filetag = $GLOBALS['cfg_df_style'].'/'.$filetag;
        }
        else
        {
            $filetag = MfTemplet($this->TypeLink->TypeInfos["temparticle"]);
        }
        $tid = $this->Fields['typeid'];
        $filetag = str_replace('{tid}', $tid,$filetag);
        $tmpfile = $cfg_basedir.$cfg_templets_dir.'/'.$filetag;
        if(!file_exists($tmpfile))
        {
            $tmpfile = $cfg_basedir.$cfg_templets_dir."/{$cfg_df_style}/article_default.htm";
        }
        if (!preg_match("#.htm$#", $tmpfile)) return FALSE;
        return $tmpfile;
    }

    /**
     *  动态输出结果
     *
     * @access    public
     * @return    void
     */
    function display()
    {
        global $htmltype;
        $this->Fields["displaytype"] = "dm";
        if($this->NowPage > 1) $this->Fields["title"] = $this->Fields["title"]."({$this->NowPage})";
        //预编译
        $this->LoadTemplet();
        $this->ParAddTable();
        $this->ParseTempletsFirst();

        //跳转网址
        $this->Fields['flag']=empty($this->Fields['flag'])? "" : $this->Fields['flag'];
        if(preg_match("#j#", $this->Fields['flag']) && $this->Fields['redirecturl'] != '')
        {
            header("location:{$this->Fields['redirecturl']}");
        }
        $pageCount = $this->NowPage;
        $this->ParseDMFields($pageCount,0);
        $this->dtp->display();
    }

    /**
     *  载入模板
     *
     * @access    public
     * @return    void
     */
    function LoadTemplet()
    {
        if($this->TempSource=='')
        {
            $tempfile = $this->GetTempletFile();
            if(!file_exists($tempfile) || !is_file($tempfile))
            {
                echo "文档ID：{$this->Fields['id']} - {$this->TypeLink->TypeInfos['typename']} - {$this->Fields['title']}<br />";
                echo " {$tempfile} 模板文件不存在，无法解析文档！";
                exit();
            }
            $this->dtp->LoadTemplate($tempfile);
            $this->TempSource = $this->dtp->SourceString;
        }
        else
        {
            $this->dtp->LoadSource($this->TempSource);
        }
    }

    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    public
     * @return    void
     */
    function ParseTempletsFirst()
    {
        if(empty($this->Fields['keywords']))
        {
            $this->Fields['keywords'] = '';
        }

        if(empty($this->Fields['reid']))
        {
            $this->Fields['reid'] = 0;
        }

        $GLOBALS['envs']['tags'] = $this->Fields['tags'];

        if(isset($this->TypeLink->TypeInfos['reid']))
        {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }

        $GLOBALS['envs']['keyword'] = $this->Fields['keywords'];

        $GLOBALS['envs']['typeid'] = $this->Fields['typeid'];

        $GLOBALS['envs']['topid'] = GetTopid($this->Fields['typeid']);

        $GLOBALS['envs']['aid'] = $GLOBALS['envs']['id'] = $this->Fields['id'];

        $GLOBALS['envs']['adminid'] = $GLOBALS['envs']['mid'] = isset($this->Fields['mid'])? $this->Fields['mid'] : 1;

        $GLOBALS['envs']['channelid'] = $this->TypeLink->TypeInfos['channeltype'];

        if($this->Fields['reid']>0)
        {
            $GLOBALS['envs']['typeid'] = $this->Fields['reid'];
        }

        MakeOneTag($this->dtp, $this, 'N');
    }

    /**
     *  解析模板，对内容里的变动进行赋值
     *
     * @access    public
     * @param     string  $pageNo  页码数
     * @param     string  $ismake  是否生成
     * @return    string
     */
    function ParseDMFields($pageNo, $ismake=1)
    {
        global $cfg_basedir, $cfg_image_dir, $cfg_ltimg_dir;
        $this->NowPage = $pageNo;
        $this->Fields['nowpage'] = $this->NowPage;

        //解析模板
        if(is_array($this->dtp->CTags))
        {
            foreach($this->dtp->CTags as $i=>$ctag)
            {
                if($ctag->GetName()=='field')
                {
                    $this->dtp->Assign($i, $this->GetField($ctag->GetAtt('name'), $ctag) );
                }
                else if($ctag->GetName()=='albumlist')
                {
                    $innertext = trim($ctag->GetInnerText());
                    if($innertext=='') $innertext = GetSysTemplets('tag_fieldlist.htm');
                    $dtp2 = new SunTagParse();
                    $dtp2->SetNameSpace('field','[',']');
                    $dtp2->LoadSource($innertext);
                    $oldSource = $dtp2->SourceString;
                    $oldCtags = $dtp2->CTags;
                    $res = '';
					$album= explode(",", $this->Fields['album']);
					foreach($album as $v)
					{
						$dtp2->SourceString = $oldSource;
						$dtp2->CTags = $oldCtags;
						foreach($dtp2->CTags as $tid=>$ctag2)
						{
							if($ctag2->GetName()=='less')
							{
								$dtp2->Assign($tid,$cfg_ltimg_dir.'/'.$v);
							}
							else if($ctag2->GetName()=='huge')
							{
								
								$dtp2->Assign($tid,$cfg_image_dir.'/'.$v);
							}
						}
						$res .= $dtp2->GetResult();
					}

                    $this->dtp->Assign($i,$res);
                }//end case
				
            }//结束模板循环

        }
    }

    /**
     *  关闭所占用的资源
     *
     * @access    public
     * @return    void
     */
    function Close()
    {
        $this->FixedValues = '';
        $this->Fields = '';
    }

}//End Archives
?>