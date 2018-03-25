<?php
/*
   +----------------------------------------------------------------------+
   |                  			  soa platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 初始化开始											      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-25       |
   +----------------------------------------------------------------------+
*/
// 获取版本号 $argv     xxx.php key version c m

$key = $argv[1] ? $argv[1] : '';
$version = $argv[2] ? intval(substr($argv[2], 1)) : '';
$xyClass = $argv[3] ? $argv[3] : '';
$xyAction = $argv[4] ? $argv[4] : '';


// 判断是否填写了版本号
if (!$version || !is_int($version)) {
    exit("参数异常，未指定版本号或者版本号不为【v数字】类型\n");
}

// 配置版本路径
define('VERSION_PATH', USER_PATH . '/' . $version);

// 配置版本相关service路径
define('SERVICE_PATH', VERSION_PATH . '/service');

// 配置日志路径
define('LOG_PATH', VERSION_PATH . '/log');

$GLOBALS['X_G'] = array();

// 载入配置文件
$configArray = array(
    'db_config.php',
    'api_config.php',
    'soa_config.php',
    'server_config.php',
);

foreach($configArray as $value){
    if (is_readable(VERSION_PATH . '/config/' . $value)) {
        $config      = require(VERSION_PATH . '/config/' . $value);
        $GLOBALS['X_G'] = array_merge($GLOBALS['X_G'], $config);
    }

}

// 配置全局版本号
$GLOBALS['X_G']['version'] = $version;

// 配置相关请求参数
$GLOBALS['X_G']['request'] = $argv;

// 载入日志共用类
require(X_PATH . "/base/XyLog.php");

// 载入公有库
require(X_PATH . "/base/XyFunction.php");

// 载入soa客户端
require(X_PATH . "/base/XySoaClient.php");

// 载入数据相关基础类
require(X_PATH . "/base/XyDb.php");
require(X_PATH . "/base/XyRedis.php");

// 载入interface和service基类
require(X_PATH . "/base/XyBaseService.php");
require(X_PATH . "/base/XyProxyService.php");

// 生成请求唯一ID
$GLOBALS['X_G']['soa']['bugFinderDistinct'] = substr(md5($version . $xyClass . $xyAction . time()), 0, 16) . rand(0, 99999999);

// 生成请求的部分参数，用于soa调用
$GLOBALS['basic_params']['ip']      = '0.0.0.0';
$GLOBALS['basic_params']['token']   = 'cli';

// 验证key是否存在
if ($key != $GLOBALS['X_G']['clikey']) {
    require(X_PATH . '/exception/AuthFaildException.php');
    throw new AuthFaildException('密钥错误，请查证后重试！');
}

// 判断是否传送了相关方法过来
if (!$xyClass || !$xyAction) {
    require(X_PATH . '/exception/ParamNotExistException.php');
    throw new ParamNotExistException('参数异常，没有接收到相关类及方法参数！');
}

// 判断是否同名，同名可能出现PHP向下兼容导致多次执行的异常
if ($xyClass == $xyAction) {
    require(X_PATH . '/exception/ClassMethodNameCanNotBeSameException.php');
    throw new ClassMethodNameCanNotBeSameException('类名与方法名不能相同！');
}

// 判断相应版本是否存在
if (!is_dir(USER_PATH . '/' . $version)) {
    require(X_PATH . '/exception/VersionNotExistException.php');
    throw new VersionNotExistException('无法找到相应版本的程序！');
}


// 配置相关值用于文件存在性判断
$class = $xyClass . '.php';
$classPath = USER_PATH . '/' . $version . '/cli/' . $class;

// 文件存在性判断
if (!is_readable($classPath)) {
    require(X_PATH . '/exception/FileNotFoundException.php');
    throw new FileNotFoundException('自动引入功能无法找到该文件！文件路径为：' . $classPath . '，请检查该文件是否存在！');
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








