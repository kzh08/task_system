<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架的跳转页面类									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/

final class JumpPlugin {

    /**
     * 正常跳转
     *
     * @param $message      string 信息内容
     * @param $jumpUrl      string 跳转链接
     * @param $waitSecond   int    等待时长
     */
    public static function s($message, $jumpUrl = '', $waitSecond = 3){
		if(empty($jumpUrl)){
			$jumpUrl	= $_SERVER['HTTP_REFERER'];
		}
		require("jump/tpl.php");
		exit;
	}

    /**
     * 异常跳转
     *
     * @param $error        string 信息内容
     * @param $jumpUrl      string 跳转链接
     * @param $waitSecond   int    等待时长
     */
	public static function e($error, $jumpUrl = '', $waitSecond = 3){
		if(empty($jumpUrl)){
			$jumpUrl	= $_SERVER['HTTP_REFERER'];
		}
		require("jump/tpl.php");
		exit;
	}
}
?>