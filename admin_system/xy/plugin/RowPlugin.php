<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | ��ͼ�����ʾ�п��Ʋ��			    				     	          |
   +----------------------------------------------------------------------+
   | Authors: lvjf <lvjunfa11@gamil.com>       CreateTime:2014-04-08      |
   +----------------------------------------------------------------------+
*/
final class RowPlugin {
	public $menuId		= 0;
	public $max			= array();
	public $available	= array();
    
	/**
	 *	�������Χ����
	 *	@param param		�������� ��ΪӢ�� ֵΪ����
	 */
    public function setAll($param = array()){       
        $this->max	= $param;
    }   
	
	/**
	 *	������ʾ����
	 *	@param param		��������
	 */
	public function set($array = array()){
		//��ȡcookie
		$cookie	= $_COOKIE['menu_show_'.$this->menuId.'_'.$_SESSION[$GLOBALS['X_G']["session"]['prefix'].'adminid']];
		if(!empty($cookie)){
			$array	= explode(",", $cookie);
		}
		
		return $array;
	}
	

	
	/**
	 *	��ȡ��Ҫ��ʾ������
	 *	@return arr			��������ʾ�������飬��ΪӢ�ģ�ֵΪ����
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