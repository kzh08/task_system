<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | cli方式请求入口											      	 	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-09-03       |
   +----------------------------------------------------------------------+
*/

// 获取版本号 $argv     xxx.php key version c m

$key 			= $argv[1] 		? $argv[1] 						: '';
$xyClass	 	= $argv[2] 		? $argv[2] 						: '';
$xyAction 		= $argv[3] 		? $argv[3] 						: '';

// 配置日志地址
define('LOG_PATH', USER_PATH.'/log');

// 载入配置文件
$GLOBALS['X_G'] = array_merge(require(X_PATH."/config/xyConfig.php"), $ownConfig);

// 配置相关请求参数
$GLOBALS['X_G']['request']	= $argv;

// 载入日志共用类
require(X_PATH . "/base/XyLog.php");

// 载入公有库
require(X_PATH . "/base/XyFunction.php");

// 载入数据相关基础类
require(X_PATH . "/base/XyDb.php");
require(X_PATH . "/base/XyRedis.php");
require(X_PATH . "/base/XySoaClient.php");

// 数据库类
$GLOBALS['X_G']["Db"] = new Db();

// 验证key是否存在
if( $key != $GLOBALS['X_G']['cliKey']){
	require(X_PATH.'/exception/AuthFaildException.php');
	throw new AuthFaildException('密钥错误，请查证后重试！');
} 

// 判断是否传送了相关方法过来
if(!$xyClass || !$xyAction){
	require(X_PATH.'/exception/ParamNoExistException.php');
	throw new ParamNoExistException('参数异常，没有接收到相关类及方法参数！');
}

// 配置相关值用于文件存在性判断
$class	   = $xyClass.'.php';
$classPath = USER_PATH.'/cli/'.$class;

// 文件存在性判断
if(!is_readable($classPath)){
	require(X_PATH . '/exception/FileNoFoundException.php');
	throw new FileNoFoundException('自动引入功能无法找到该文件！文件路径为：'.$classPath.'，请检查该文件是否存在！');
}else{
	require_once($classPath);
}

// 类存在性判断
if(!class_exists($xyClass)){
	require(X_PATH . '/exception/ClassNotFoundException.php');
	throw new ClassNotFoundException('该类不存在:'.$class);
}else{
	$newObject = new $xyClass();
}

// 实例化类是否成功判断
if(!is_object($newObject)){
	require(X_PATH . '/exception/NewClassFaildException.php');
	throw new NewClassFaildException('无法实例化类:'.$class.'.php');
}

// 方法是否存在判断
if(!method_exists($newObject, $xyAction)){
	require(X_PATH . '/exception/MethodNotFoundException.php');
	throw new MethodNotFoundException('该方法不存在:'.$xyAction);
}


//调用相应的类和方法来进行处理
$newObject->$xyAction();








