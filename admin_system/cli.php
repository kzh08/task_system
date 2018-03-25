<?php
header("Content-type: text/html; charset=utf-8");

//-----------初始化开始------------


// 配置框架的目录路径
define('X_PATH', dirname(__FILE__).'/xy');

// 配置用户目录的路径
define('USER_PATH', dirname(__FILE__));

$ownConfig = array();

// 载入公有异常接口
require(X_PATH."/base/XyExceptionInterface.php");
	
try{
	// 开始初始化进程
	require(X_PATH."/y.php");		
}catch(XyExceptionInterface $e){
	// 记录异常相关日志
	LOG::w('request exception: '.$e->getMessage(), 'exception');
		
	if($GLOBALS['X_G']["debug"]){
		exception_output_cli($e);
	}else{
		exit($e->getMessage());
	}
}

//-----------初始化结束------------



