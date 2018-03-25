<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 扩展程序，过滤类，对参数进行过滤	      	 	  	  				          |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

Class FilterParams extends xySinglePoint{

	private $getFilter		= "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	
	private $postFilter		= "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	
	private $cookieFilter	= "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

	function doing(){
        //引入跳转插件
        plugin('jump');

		/*防注入开始*/
		foreach($_GET AS $key => $value){
			$this->stopAttack($key, $value, $this->getFilter);
		}
		
		foreach($_POST AS $key => $value){
			$this->stopAttack($key, $value, $this->postFilter);
		}
		
		foreach($_COOKIE AS $key => $value){
			$this->stopAttack($key, $value, $this->cookieFilter);
		}
		/*防注入结束*/
		
		/*特殊字段判断开始*/
		foreach($_GET AS $key => $value){
			$this->special2Normal($key, $value, "_GET");
		}
		
		foreach($_POST AS $key => $value){
			$this->special2Normal($key, $value, "_POST");
		}
		/*特殊字段判断结束*/
	}
	
	/**
	 * 参数检查并写日志
	 */
	public function stopAttack($strFiltKey, $strFiltValue, $arrFiltReq){
		if(is_array($strFiltValue))
			$strFiltValue = implode($strFiltValue);
		
		if(preg_match("/".$arrFiltReq."/is", $strFiltValue) == 1){
			//写日志
			Log::e("恶意用户通过".$_SERVER["REQUEST_METHOD"]."方法，以".$strFiltKey." : '".$strFiltValue."' 进行SQL注入");
            JumpPlugin::e('您提交的参数非法,系统已记录您的本次操作！');
		}
	}
	
	/**
	 * 表单字段类型特殊判断  支持string、int、float、bool
	 */
	public function special2Normal($key, $value, $method){
		global $$method;
		if(stripos($key, "is_string") !== false){
			if(!is_string($value)){
                JumpPlugin::e($key."的值应为字符串");
			}else{
				unset($_REQUEST[$key]);
				unset(${$method}[$key]);
				$newKey				= str_replace("is_string_", "", $key);
				$_REQUEST[$newKey]	= $value;
				${$method}[$newKey]	= $value;
			}
		}elseif(stripos($key, "is_int") !== false){
			if(!is_numeric($value) || strpos($value, ".") !== false){
                JumpPlugin::e($key."的值应为整数");
			}else{
				unset($_REQUEST[$key]);
				unset(${$method}[$key]);
				$newKey				= str_replace("is_int_", "", $key);
				$_REQUEST[$newKey]	= $value;
				${$method}[$newKey]	= $value;
			}
		}elseif(stripos($key, "is_float") !== false){
			if(!is_numeric($value)){
                JumpPlugin::e($key."的值应为数字");
			}else{
				unset($_REQUEST[$key]);
				unset(${$method}[$key]);
				$newKey				= str_replace("is_float_", "", $key);
				$_REQUEST[$newKey]	= $value;
				${$method}[$newKey]	= $value;
			}
		}elseif(stripos($key, "is_bool") !== false){
			if(!in_array(strtolower($value), array("true", "false"))){
                JumpPlugin::e($key."的值应为布尔值");
			}else{
				unset($_REQUEST[$key]);
				unset(${$method}[$key]);
				$newKey				= str_replace("is_bool_", "", $key);
				$_REQUEST[$newKey]	= $value;
				${$method}[$newKey]	= $value;
			}
		}
	}
}