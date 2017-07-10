<?php
	require_once("config.php");
	
	header("Content-Type: text/html; charset=utf-8");
	
	/**
	 *  获得某id的所有下级id
	 *
	 * @param     string  $id  栏目id
	 * @param     string  $channel  模型ID
	 * @param     string  $addthis  是否包含本身
	 * @return    string
	 */

	function GetSunIds($id,$addthis=true)
	{
		global $cfg_Cs;
		$GLOBALS['idArray'] = array();
		if( !is_array($cfg_Cs) )
		{
			require_once(SUNDATA."/cache/inc_catalog_base.inc");
		}
		GetIdsLogic($id,$cfg_Cs,$channel,$addthis);
		$rquery = join(',',$GLOBALS['idArray']);
		$rquery = preg_replace("/,$/", '', $rquery); 
		return $rquery;
	}

	//递归逻辑
	function GetIdsLogic($id,$sArr,$addthis=false)
	{
		if($id!=0 && $addthis)
		{
			$GLOBALS['idArray'][$id] = $id;
		}
		if(is_array($sArr))
		{
			foreach($sArr as $k=>$v)
			{
				if( $v[0]==$id)
				{
					GetIdsLogic($k,$sArr,$channel,true);
				}
			}
		}
	}

?>