<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 视图表格显示列控制插件			    				     	          |
   +----------------------------------------------------------------------+
   | Authors: lvjf <lvjunfa11@gamil.com>       CreateTime:2014-04-08      |
   +----------------------------------------------------------------------+
*/
final class RowPlugin {
	public $menuId		= 0;
	public $max			= array();
	public $available	= array();
    
	/**
	 *	设置最大范围列名
	 *	@param param		列名数组 键为英文 值为中文
	 */
    public function setAll($param = array()){       
        $this->max	= $param;
    }   
	
	/**
	 *	设置显示列名
	 *	@param param		列名数组
	 */
	public function set($array = array()){
		//获取cookie
		$cookie	= $_COOKIE['menu_show_'.$this->menuId.'_'.$_SESSION[$GLOBALS['X_G']["session"]['prefix'].'adminid']];
		if(!empty($cookie)){
			$array	= explode(",", $cookie);
		}
		
		return $array;
	}
	

	
	/**
	 *	获取需要显示的列名
	 *	@return arr			返回许显示列名数组，键为英文，值为中文
	 */
	/*public function get(){
		foreach($this->max AS $key => $value){
			if(in_array($key, $this->available)){
				$arr[$key]	= $value;
			}
		}
		
		return $arr;
	}*/
}
?>