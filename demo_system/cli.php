<?php
/*
   +----------------------------------------------------------------------+
   |                  			  soa platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | cli相关入口文件									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-25       |
   +----------------------------------------------------------------------+
*/

header("Content-type: text/html; charset=utf-8");

//-----------初始化开始------------

// 配置项目的路径
define('USER_PATH', dirname(__FILE__));
// 配置基库的目录路径
define('X_PATH', USER_PATH . '/xy');

// 载入公有异常接口
require(X_PATH . "/base/XyExceptionInterface.php");

try {
    // 开始初始化进程
    require(X_PATH . "/y.php");
} catch (Exception $e) {
    // 记录异常相关日志
    LOG::e('request exception: ' . $e->getMessage() . 'exception' . '发生错误的位置为: ' . $e->getFile() . ' 中的第' . $e->getLine() . '行' );

    if ($GLOBALS['X_G']["debug"]) {
        exception_output_cli($e);
    } else {
        exit($e->getMessage());
    }
}

//-----------初始化结束------------



