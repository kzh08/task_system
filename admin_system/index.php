<?php
header("Content-type: text/html; charset=utf-8");

session_start();
error_reporting(0);
// 配置框架的目录路径
define('X_PATH', dirname(__FILE__).'/xy');

// 配置用户目录的路径
define('USER_PATH', dirname(__FILE__));

// 载入公有异常接口
require(X_PATH."/base/XyExceptionInterface.php");

try {
    // 开始初始化进程
    require(X_PATH . "/x.php");
} catch (Exception $e) {
    // 记录异常相关日志
    LOG::e('request exception: ' . $e->getMessage() . ' exception' . '发生错误的位置为: ' . $e->getFile() . ' 中的第' . $e->getLine() . '行' );

    if ($GLOBALS['X_G']["debug"]) {
        exception_output($e);
    } else {
        //todo:页面美化
        echo '出了点小问题， 0 0';
        exit();
    }
}

