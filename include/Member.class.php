<?php
class Member
{
    var $userinfo;
    var $cookieId;

    function __construct()
    {
    	global $cfg_cookie_id;
        $this->cookieId = isset($cfg_cookie_id) ? $cfg_cookie_id : 'G_N_T_K';
		$info = $this->getCookie($this->cookieId);
        $this->userinfo = isset($info)? $info : '';
    }

    function Member()
    {
        $this->__construct();
    }

	function getMember()
    {
        return $this->userinfo;
    }
	
    function addMember($value)
    {
        $this->saveCookie($this->cookieId,$value);
    }

    function delMember()
    {
        setcookie($this->cookieId, "", time()-36000,"/");
    }

    //加密接口字符
    function enCrypt($txt)
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++)
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode($this->setKey($tmp));
    }

    //解密接口字符串
    function deCrypt($txt)
    {
        $txt = $this->setKey(base64_decode($txt));
        $tmp = '';
        for ($i = 0; $i < strlen($txt); $i++)
        {
            $tmp .= $txt[$i] ^ $txt[++$i];
        }
        return $tmp;
    }

    //处理加密数据
    function setKey($txt)
    {
        global $cfg_cookie_encode;
        $encrypt_key = md5(strtolower($cfg_cookie_encode));
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++)
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }

    //串行化数组
    function enCode($array)
    {
        $arrayenc = array();
        foreach($array as $key => $val)
        {
            $arrayenc[] = $key.'='.urlencode($val);
        }
        return implode('&', $arrayenc);
    }

    //创建加密的_cookie
    function saveCookie($key,$value)
    {
        if(is_array($value))
        {
            $value = $this->enCrypt($this->enCode($value));
        }
        else
        {
            $value = $this->enCrypt($value);
        }
        setcookie($key,$value,time()+99999999,'/');
    }

    //获得解密的_cookie
    function getCookie($key)
    {
        if(isset($_COOKIE[$key]) && !empty($_COOKIE[$key]))
        {
            return $this->deCrypt($_COOKIE[$key]);
        }
    }
}