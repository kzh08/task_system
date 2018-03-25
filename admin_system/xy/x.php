<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy的入口地址										      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2013-10-22       |
   +----------------------------------------------------------------------+
*/
define('VERSION', '2.0'); // 当前版本号

// 定义路径
if(!defined('EXTEND_PATH'))		define('EXTEND_PATH', X_PATH.'/extend/');
if(!defined('SERVICE_PATH')) 	define('SERVICE_PATH', USER_PATH.'/service/');

$GLOBALS['X_G'] = array();

// 载入配置文件
$configArray = array(
    'db_config.php',
    'api_config.php',
    'soa_config.php',
    'server_config.php',
);

foreach($configArray as $value){
    if (is_readable(USER_PATH . '/config/' . $value)) {
        $config      = require(USER_PATH . '/config/' . $value);
        $GLOBALS['X_G'] = array_merge($GLOBALS['X_G'], $config);
    }
}

// 获取控制器、参数
$__controller 	= isset($_REQUEST['xycontroller']) 	? ucfirst($_REQUEST['xycontroller']) : $GLOBALS['X_G']["defaultController"];
$__action 		= isset($_REQUEST['xyaction']) 		? $_REQUEST['xyaction'] 	 		 : $GLOBALS['X_G']["defaultAction"];
$__param		= $_REQUEST['xyparam'];

// 根据配置文件定义其它路径
if(!defined('__APP__')) 		define('__APP__', str_replace("/index.php", "", $_SERVER['PHP_SELF']));
if(!defined('__URL__')) 		define('__URL__', __APP__.'/'.$__controller);
if(!defined('__CURRENT__')) 	define('__CURRENT__', __URL__.'/'.$__action);
if(!defined('__PUBLIC__')) 		define('__PUBLIC__', __APP__."/Public");

// 载入公有库
require(X_PATH."/base/XyFunction.php");

// 载入基础类
require(X_PATH."/base/XyDb.php");
require(X_PATH."/base/XyLog.php");
require(X_PATH."/base/XyRedis.php");
require(X_PATH."/base/XyService.php");
require(X_PATH."/base/XyView.php");
require(X_PATH."/base/XyController.php");
require(X_PATH."/base/XySinglePoint.php");
require(X_PATH . "/base/XySoaClient.php");
require(USER_PATH."/controller/CommonController.php");

//预连接数据库，初始页面可以显示数据库版本信息。
$GLOBALS['X_G']["Db"] = db();

//生成文件夹
autoDir();

// 转换多配置插件为单点扩展
if(!empty($GLOBALS['X_G']["systemXyMulitPoint"])){
	foreach($GLOBALS['X_G']["systemXyMulitPoint"] as $extendName => $pointArr){
		foreach($pointArr as $point){
			$GLOBALS['X_G']["systemXySinglePoint"][$point][] = $extendName;
		}
	}
}

// 如果是debug模式，打开警告输出
if ($GLOBALS['X_G']['debug']) {
	error_reporting(E_ERROR | E_WARNING);
	ini_set("display_errors", 1);
} else {
	error_reporting(0);
	ini_set("display_errors", 0);
}

xyPoint('init');

//对带问号的参数特殊处理
if(strpos($_SERVER["REQUEST_URI"],'?') !== false){
	$paramOfOld = explode('?',$_SERVER["REQUEST_URI"]);
    $params_old = str_replace('&','=',$paramOfOld[1]);
}

//分析参数
$__paramArr		= array();
$param_num		= 0;

$params 		= explode('/', $__param);
$param_num		= count($params);

if(!empty($params_old)){
    $params_old 	= explode('=', $params_old);
    $param_num		+= count($params_old);
    $params_merge 	= array_merge($params, $params_old);
}else{
	$params_merge	= $params;
}



for($i = 0; $i < $param_num; $i += 2){
	if($params_merge[$i]){
		$__paramArr[$params_merge[$i]] = $params_merge[$i+1];
	}
}

/** 重写REQUEST start **/
unset($_GET['xycontroller'], $_GET['xyaction'], $_GET['xyparam']);
$_GET		= array_merge($__paramArr, $_GET);
$request	= array("request" => array("controller" => $__controller, "action" => $__action));		
$post_get	= array_merge($_POST, $_GET);
$_REQUEST	= array_merge($post_get, $request);
/** 重写REQUEST end **/


//检查方法名-包含xy为系统预留字符，不予使用。
if(stripos($__action, 'xy') === 'FALSE'){
	require(X_PATH.'/exception/ActionNameIsReservedException.php');
	throw new ActionNameIsReservedException();
}

xyPoint('before_all');
xyStart();
xyPoint('after_all');






