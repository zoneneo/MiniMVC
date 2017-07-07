-- MySQL
--
-- Database: `zone`
--

-- --------------------------------------------------------

--
-- 表的结构 `sun_admin`
--

CREATE TABLE `sun_admin` (
  `id` tinyint(3) UNSIGNED AUTO_INCREMENT,
  `typ` tinyint(3) UNSIGNED DEFAULT '0',
  `tme` int(10) UNSIGNED DEFAULT '0',
  `usr` char(30) DEFAULT '',
  `pwd` char(32) DEFAULT '',
  `name` varchar(20) DEFAULT '',
  `email` varchar(30) DEFAULT '',
  `logip` varchar(20) DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `sun_admin`
--

--permissions --permi --typ 
INSERT INTO `sun_admin` (`id`, `typ`, `tme`, `usr`, `pwd`, `name`, `email`, `logip`) VALUES
(100, 100, 1499321278, 'master', '654321', 'jun', 'jun@xxx.com', '127.0.0.1'),
(100, 100, 1499312380, 'admin', '654321', '孙朝辉', 'szh21@21cn.com', '127.0.0.1');

-- --------------------------------------------------------

--
-- 表的结构 `sun_arctiny`
--

CREATE TABLE `sun_arctiny` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `typeid` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `typeid2` varchar(90) NOT NULL DEFAULT '0',
  `arcrank` smallint(6) NOT NULL DEFAULT '0',
  `channel` smallint(5) NOT NULL DEFAULT '1',
  `senddate` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sortrank` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `mid` mediumint(8) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `sun_member`
--

CREATE TABLE `sun_member` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `typ` varchar(10) NOT NULL DEFAULT '个人',
  `tme` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pwd` char(32) NOT NULL DEFAULT '',
  `rank` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `name` char(36) NOT NULL DEFAULT '',
  `address` varchar(200) NOT NULL DEFAULT '',
  `sex` enum('男','女','保密') NOT NULL DEFAULT '保密',
  `phone` char(11) NOT NULL DEFAULT '0',
  `money` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `email` char(50) NOT NULL DEFAULT '',
  `scores` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `face` char(50) NOT NULL DEFAULT '',
  `joinip` char(16) NOT NULL DEFAULT '',
  `logintime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `loginip` char(16) NOT NULL DEFAULT '',
  `checkmail` smallint(6) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `sun_order`
--

CREATE TABLE `sun_order` (
  `id` mediumint(8) NOT NULL,
  `tid` char(20) DEFAULT '',
  `aid` smallint(6) NOT NULL,
  `qty` smallint(5) DEFAULT '0',
  `prc` float UNSIGNED DEFAULT '0',
  `amt` float NOT NULL,
  `itm` varchar(50) DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- 表的结构 `sun_smscheck`
--

CREATE TABLE `sun_smscheck` (
  `id` int(10) UNSIGNED NOT NULL,
  `chk` tinyint(3) DEFAULT '-1',
  `sed` int(10) DEFAULT '0',
  `tme` int(10) DEFAULT '0',
  `cod` varchar(10) DEFAULT '',
  `mob` varchar(15) DEFAULT '',
  `typ` varchar(50) DEFAULT '',
  `txt` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- 表的结构 `sun_arccache`
--

CREATE TABLE `sun_arccache` (
  `md5hash` char(32) NOT NULL DEFAULT '',
  `uptime` int(11) NOT NULL DEFAULT '0',
  `cachedata` mediumtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `sun_arccache`
--

INSERT INTO `sun_arccache` (`md5hash`, `uptime`, `cachedata`) VALUES
('8c19dbd7ec4a8cce13b64741225cd78c', 1460714374, '0'),
('757f4c6125b20030a4733ce3ddaeef22', 1460714374, '0'),
('12d8858063518e095f206120c8273662', 1460714375, '0'),
('078f5d3a520c5f1590a07807d9d072f4', 1460714375, '53,54,55,56,57'),
('75be231c38f02b2ea252ae5d72abdfbf', 1460714375, '0'),
('be5a89015edd7119ba2d0f8c7acd4584', 1460714381, '0');

-- --------------------------------------------------------

--
-- 表的结构 `sun_archives`
--

CREATE TABLE `sun_archives` (
  `id` smallint(6) UNSIGNED AUTO_INCREMENT,
  `typ` smallint(6) DEFAULT '0',
  `typeid` smallint(5) DEFAULT '1',
  `typeid2` smallint(5) DEFAULT NULL,
  `sendate` int(10) DEFAULT NULL,
  `pubdate` int(10) NOT NULL,
  `sortrank` smallint(5) DEFAULT '100',
  `trade` int(10) DEFAULT '2000',
  `market` decimal(10,2) DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT '0.00',
  `sale` varchar(20) DEFAULT '',
  `flag` set('c','h','p','f','s') DEFAULT 'p',
  `code` varchar(20) DEFAULT '',
  `brand` varchar(20) DEFAULT '',
  `spec` varchar(50) DEFAULT '',
  `weight` varchar(20) DEFAULT '',
  `unit` varchar(6) DEFAULT '',
  `rqcode` varchar(100) DEFAULT '',
  `litpic` varchar(100) DEFAULT '',
  `picture` varchar(100) DEFAULT '',
  `title` varchar(100) DEFAULT '',
  `words` varchar(100) DEFAULT '',
  `descr` varchar(200) DEFAULT '',
  `gbody` text,
  PRIMARY KEY  (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- 表的结构 `sun_arctype`
--

CREATE TABLE `sun_arctype` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `reid` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `topid` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `sortrank` smallint(5) UNSIGNED NOT NULL DEFAULT '50',
  `typename` char(30) NOT NULL DEFAULT '',
  `typedir` char(60) NOT NULL DEFAULT '',
  `isdefault` smallint(6) NOT NULL DEFAULT '0',
  `defaultname` char(15) NOT NULL DEFAULT 'index.html',
  `issend` smallint(6) NOT NULL DEFAULT '0',
  `channeltype` smallint(6) DEFAULT '1',
  `maxpage` smallint(6) NOT NULL DEFAULT '-1',
  `ispart` smallint(6) NOT NULL DEFAULT '0',
  `corank` smallint(6) NOT NULL DEFAULT '0',
  `tempindex` char(50) NOT NULL DEFAULT '{style}/index_article.htm',
  `templist` char(50) NOT NULL DEFAULT '{style}/list_article.htm',
  `temparticle` char(50) NOT NULL DEFAULT '{style}/article_article.htm',
  `namerule` char(50) NOT NULL DEFAULT '{typedir}/{Y}{M}{D}/{aid}.html',
  `namerule2` char(50) NOT NULL DEFAULT '{typedir}/list_{tid}_{page}.html',
  `modname` char(20) NOT NULL DEFAULT 'default',
  `description` char(150) NOT NULL DEFAULT '',
  `keywords` varchar(60) NOT NULL DEFAULT '',
  `seotitle` varchar(80) NOT NULL DEFAULT '',
  `ishidden` smallint(6) NOT NULL DEFAULT '0',
  `content` text,
  `smalltypes` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- 表的结构 `sun_channeltype`
--

CREATE TABLE `sun_channeltype` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `nid` varchar(20) NOT NULL DEFAULT '',
  `typename` varchar(30) NOT NULL DEFAULT '',
  `maintable` varchar(50) NOT NULL DEFAULT 'sun_archives',
  `addtable` varchar(50) NOT NULL DEFAULT '',
  `addcon` varchar(30) NOT NULL DEFAULT '',
  `mancon` varchar(30) NOT NULL DEFAULT '',
  `editcon` varchar(30) NOT NULL DEFAULT '',
  `useraddcon` varchar(30) NOT NULL DEFAULT '',
  `usermancon` varchar(30) NOT NULL DEFAULT '',
  `usereditcon` varchar(30) NOT NULL DEFAULT '',
  `fieldset` text,
  `listfields` text,
  `allfields` text,
  `issystem` smallint(6) NOT NULL DEFAULT '0',
  `isshow` smallint(6) NOT NULL DEFAULT '1',
  `issend` smallint(6) NOT NULL DEFAULT '0',
  `arcsta` smallint(6) NOT NULL DEFAULT '-1',
  `usertype` char(10) NOT NULL DEFAULT '',
  `sendrank` smallint(6) NOT NULL DEFAULT '10',
  `isdefault` smallint(6) NOT NULL DEFAULT '0',
  `needdes` tinyint(1) NOT NULL DEFAULT '1',
  `needpic` tinyint(1) NOT NULL DEFAULT '1',
  `titlename` varchar(20) NOT NULL DEFAULT '标题',
  `onlyone` smallint(6) NOT NULL DEFAULT '0',
  `dfcid` smallint(5) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `sun_channeltype`
--

INSERT INTO `sun_channeltype` (`id`, `nid`, `typename`, `maintable`, `addtable`, `addcon`, `mancon`, `editcon`, `useraddcon`, `usermancon`, `usereditcon`, `fieldset`, `listfields`, `allfields`, `issystem`, `isshow`, `issend`, `arcsta`, `usertype`, `sendrank`, `isdefault`, `needdes`, `needpic`, `titlename`, `onlyone`, `dfcid`) VALUES
(1, 'article', '普通文章', 'sun_archives', 'sun_addonarticle', 'article_add.php', 'content_list.php', 'article_edit.php', 'article_add.php', 'content_list.php', 'article_edit.php', '<field:body itemname="文章内容" autofield="0" notsend="0" type="htmltext" isnull="true" islist="1" default=""  maxlength="" page="split">	\n</field:body>	\n', '', '', 1, 1, 1, -1, '', 10, 0, 1, 1, '标题', 0, 0),
(2, 'image', '图片集', 'sun_archives', 'sun_addonimages', 'album_add.php', 'content_i_list.php', 'album_edit.php', 'album_add.php', 'content_list.php', 'album_edit.php', '<field:pagestyle itemname="页面风格" type="number" isnull="true" default="2" rename="" notsend="1" />	\n<field:imgurls itemname="图片集合" type="img" isnull="true" default="" rename="" page="split"/>	\n<field:body itemname="图集内容" autofield="0" notsend="0" type="htmltext" isnull="true" islist="0" default=""  maxlength="250" page=""></field:body>', '', '', 1, 1, 1, -1, '', 10, 0, 1, 1, '标题', 0, 0),
(3, 'soft', '软件', 'sun_archives', 'sun_addonsoft', 'soft_add.php', 'content_i_list.php', 'soft_edit.php', '', '', '', '<field:filetype islist="1" itemname="文件类型" type="text" isnull="true" default="" rename="" />	\n<field:language islist="1" itemname="语言" type="text" isnull="true" default="" rename="" />	\n<field:softtype islist="1" itemname="软件类型" type="text" isnull="true" default="" rename="" />	\n<field:accredit islist="1" itemname="授权方式" type="text" isnull="true" default="" rename="" />	\n<field:os islist="1" itemname="操作系统" type="text" isnull="true" default="" rename="" />	\n<field:softrank  islist="1" itemname="软件等级" type="int" isnull="true" default="3" rename="" function="GetRankStar(@me)" notsend="1"/>	\n<field:officialUrl  itemname="官方网址" type="text" isnull="true" default="" rename="" />	\n<field:officialDemo itemname="演示网址" type="text" isnull="true" default="" rename="" />	\n<field:softsize  itemname="软件大小" type="text" isnull="true" default="" rename="" />	\n<field:softlinks  itemname="软件地址" type="softlinks" isnull="true" default="" rename="" />	\n<field:introduce  itemname="详细介绍" type="htmltext" isnull="trnue" default="" rename="" />	\n<field:daccess islist="1" itemname="下载级别" type="int" isnull="true" default="0" rename="" function="" notsend="1"/>	\n<field:needmoney islist="1" itemname="需要金币" type="int" isnull="true" default="0" rename="" function="" notsend="1" />', 'filetype,language,softtype,os,accredit,softrank', '', 1, 1, 1, -1, '', 10, 0, 1, 1, '标题', 0, 0),
(-1, 'spec', '专题', 'sun_archives', 'sun_addonspec', 'spec_add.php', 'content_s_list.php', 'spec_edit.php', '', '', '', '<field:note type="specialtopic" isnull="true" default="" rename=""/>', '', '', 1, 1, 0, -1, '', 10, 0, 1, 1, '标题', 0, 0),
(6, 'shop', '商品', 'sun_archives', 'sun_addonshop', 'archives_add.php', 'content_list.php', 'archives_edit.php', 'archives_add.php', 'content_list.php', 'archives_edit.php', '<field:body itemname="详细介绍" autofield="1" notsend="0" type="htmltext" isnull="true" islist="0" default=""  maxlength="" page="split">	\n</field:body>	\n<field:price itemname="市场价" autofield="1" notsend="0" type="float" isnull="true" islist="1" default=""  maxlength="" page="">	\n</field:price>	\n<field:trueprice itemname="优惠价" autofield="1" notsend="0" type="float" isnull="true" islist="1" default=""  maxlength="" page="">	\n</field:trueprice>	\n<field:brand itemname="品牌" autofield="1" notsend="0" type="text" isnull="true" islist="1" default=""  maxlength="250" page="">	\n</field:brand>	\n<field:units itemname="计量单位" autofield="1" notsend="0" type="text" isnull="true" islist="1" default=""  maxlength="250" page="">	\n</field:units>	\n\n	\n\n<field:vocation itemname="行业" autofield="1" notsend="0"type="stepselect" isnull="true" islist="0" default=""  maxlength="250" page="">	\n</field:vocation>	\n\n<field:infotype itemname="信息类型" autofield="1" notsend="0" type="stepselect" isnull="true" islist="0" default=""  maxlength="250" page="">	\n</field:infotype>	\n\n<field:uptime itemname="上架时间" autofield="1" notsend="0" type="datetime" isnull="true" islist="0" default=""  maxlength="250" page="">	\n</field:uptime>	\n', 'price,trueprice,brand,units', '', 0, 1, 1, -1, '', 10, 0, 1, 1, '商品名称', 0, 0),
(-8, 'infos', '分类信息', 'sun_archives', 'sun_addoninfos', 'archives_sg_add.php', 'content_sg_list.php', 'archives_sg_edit.php', 'archives_sg_add.php', 'content_sg_list.php', 'archives_sg_edit.php', '<field:channel itemname="频道id" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="10" page=""></field:channel>	\n<field:arcrank itemname="浏览权限" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="5" page=""></field:arcrank>	\n<field:mid itemname="会员id" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="8" page=""></field:mid>	\n<field:click itemname="点击" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="10" page=""></field:click>	\n<field:title itemname="标题" autofield="0" notsend="0" type="text" isnull="true" islist="1" default="0"  maxlength="60" page=""></field:title>	\n<field:senddate itemname="发布时间" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="10" page=""></field:senddate>	\n<field:flag itemname="推荐属性" autofield="0" notsend="0" type="checkbox" isnull="true" islist="1" default="0"  maxlength="10" page=""></field:flag>	\n<field:litpic itemname="缩略图" autofield="0" notsend="0" type="text" isnull="true" islist="1" default="0"  maxlength="60" page=""></field:litpic>	\n<field:userip itemname="会员IP" autofield="0" notsend="0" type="text" isnull="true" islist="0" default="0"  maxlength="15" page=""></field:userip>	\n<field:lastpost itemname="最后评论时间" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="10" page=""></field:lastpost>	\n<field:scores itemname="评论积分" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="8" page=""></field:scores>	\n<field:goodpost itemname="好评数" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="8" page=""></field:goodpost>	\n<field:badpost itemname="差评数" autofield="0" notsend="0" type="int" isnull="true" islist="1" default="0"  maxlength="8" page=""></field:badpost>	\n<field:nativeplace itemname="地区" autofield="1" notsend="0" type="stepselect" isnull="true" islist="1" default="0"  maxlength="250" page="">	\n</field:nativeplace>	\n<field:infotype itemname="信息类型" autofield="1" notsend="0" type="stepselect" isnull="true" islist="1" default="0"  maxlength="250" page="">	\n</field:infotype>	\n<field:body itemname="信息内容" autofield="1" notsend="0" type="htmltext" isnull="true" islist="0" default=""  maxlength="250" page="">	\n</field:body>	\n<field:endtime itemname="截止日期" autofield="1" notsend="0" type="datetime" isnull="true" islist="1" default=""  maxlength="250" page="">	\n</field:endtime>	\n<field:linkman itemname="联系人" autofield="1" notsend="0" type="text" isnull="true" islist="0" default=""  maxlength="50" page="">	\n</field:linkman>	\n<field:tel itemname="联系电话" autofield="1" notsend="0" type="text" isnull="true" islist="0" default="" maxlength="50" page="">	\n</field:tel>	\n<field:email itemname="电子邮箱" autofield="1" notsend="0" type="text" isnull="true" islist="0" default=""  maxlength="50" page="">	\n</field:email>	\n<field:address itemname="地址" autofield="1" notsend="0" type="text" isnull="true" islist="0" default=""  maxlength="100" page="">	\n</field:address>	\n', 'channel,arcrank,mid,click,title,senddate,flag,litpic,lastpost,scores,goodpost,badpost,nativeplace,infotype,endtime', '', -1, 1, 1, -1, '', 0, 0, 0, 1, '信息标题', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `sun_comment`
--

CREATE TABLE `sun_comment` (
  `id` int(10) NOT NULL,
  `tme` int(10) DEFAULT '0',
  `gid` mediumint(8) DEFAULT '0',
  `cid` mediumint(8) DEFAULT '0',
  `post` varchar(300) DEFAULT NULL,
  `reply` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;