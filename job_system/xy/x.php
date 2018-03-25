<?php
/*
   +----------------------------------------------------------------------+
   |                  			  push platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 初始化开始											      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-03       |
   +----------------------------------------------------------------------+
*/
// 载入rest公有库
require(X_PATH . "/base/XyRestFunction.php");

// 获取版本号
$version  = !empty($_REQUEST['version']) ? substr($_REQUEST['version'], 1) : '';
$xyClass  = !empty($_REQUEST['xyC']) ? $_REQUEST['xyC'] . 'Interface' : '';
$xyAction = !empty($_REQUEST['xyM']) ? $_REQUEST['xyM'] : '';

$GLOBALS['X_G']['uri'] = $_REQUEST['version'] . '_' . $_REQUEST['xyC'] . '_' . $_REQUEST['xyM'];

//过滤数组数据
$_REQUEST = array_map(
    function ($value) {
        if(is_scalar($value)){
            return trim($value);
        }else{
            return $value;
        }
    },
    $_REQUEST
);

$GLOBALS['X_G']['version'] = $version;


// 载入rest返回方法----用于rest相关返回
require(X_PATH . "/class/rest/RestUtil.php");

// 判断是否传送了相关方法过来
if (!$xyClass || !$xyAction) {
    sendResponse(400, '{"message": "No Action Sent!", "code": 1002}');
}

// 判断是否填写了版本号
if (!$version) {
    sendResponse(400, '{"message": "No Version Sent!", "code": 1002}');
}

// 判断相应版本是否存在
if (!is_dir(USER_PATH . '/' . $version)) {
    sendResponse(400, '{"message": "Server Does Not Have The Version!", "code": 1003}');
}

// 配置版本路径
define('VERSION_PATH', USER_PATH . '/' . $version);

// 配置版本相关service路径
define('SERVICE_PATH', VERSION_PATH . '/service');

// 配置版本相关interface路径
define('INTERFACE_PATH', VERSION_PATH . '/interface');

// 配置日志路径
define('LOG_PATH', VERSION_PATH . '/log');

// 载入配置文件
$configArray = array(
    'db_config.php',
    'api_config.php',
    'cache_config.php',
    'soa_config.php',
    'server_config.php',
);

foreach($configArray as $value){
    if (is_readable(VERSION_PATH . '/config/' . $value)) {
        $config      = require(VERSION_PATH . '/config/' . $value);
        $GLOBALS['X_G'] = array_merge($GLOBALS['X_G'], $config);
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

// 载入日志共用类
require(X_PATH . "/base/XyLog.php");

// 载入公有库
require(X_PATH . "/base/XyFunction.php");

// 载入soa客户端
require(X_PATH . "/base/XySoaClient.php");

// 载入TQCC核心类
require(X_PATH . "/base/XyCache.php");

// 载入数据相关基础类
require(X_PATH . "/base/XyDb.php");
require(X_PATH . "/base/XyRedis.php");

// 载入interface和service基类
require(X_PATH . "/base/XyBaseInterface.php");
require(X_PATH . "/base/XyBaseService.php");
require(X_PATH . "/base/XyProxyService.php");

// rest相关预处理以及安全校验
xyStart();

// 初始化基本参数
init_basic_params();

// 生成请求唯一ID
if($_REQUEST['distinctRequestId']){
    $GLOBALS['X_G']['soa']['bugFinderDistinct'] = $_REQUEST['distinctRequestId'];
}else{
    $GLOBALS['X_G']['soa']['bugFinderDistinct'] = substr(md5($version . $xyClass . $xyAction . time()), 0, 16) . rand(0, 99999999);
}

// soa传递基本参数
if($_REQUEST['soa_basic']){
    $GLOBALS['basic_params'] = unserialize(base64_decode($_REQUEST['soa_basic']));
}

// 配置相关值用于文件存在性判断
$class = $xyClass . '.php';
$classPath = USER_PATH . '/' . $version . '/interface/' . $class;

// 文件存在性判断
if (!is_readable($classPath)) {
    require(X_PATH . '/exception/FileNotFoundException.php');
    throw new FileNotFoundException('无法找到该文件！文件路径为：' . $classPath . '，请检查该文件是否存在！');
} else {
    require_once($classPath);
}

// 类存在性判断
if (!class_exists($xyClass)) {
    require(X_PATH . '/exception/ClassNotFoundException.php');
    throw new ClassNotFoundException('该类不存在:' . $class);
} else {
    $newObject = new $xyClass();
}

// 实例化类是否成功判断
if (!is_object($newObject)) {
    require(X_PATH . '/exception/NewClassFailedException.php');
    throw new NewClassFailedException('无法实例化类:' . $class . '.php');
}

// 方法是否存在判断
if (!method_exists($newObject, $xyAction)) {
    require(X_PATH . '/exception/MethodNotFoundException.php');
    throw new MethodNotFoundException('该方法不存在:' . $xyAction);
}


//调用相应的类和方法来进行处理
$newObject->$xyAction();








