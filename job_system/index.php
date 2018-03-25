<?php
/*
   +----------------------------------------------------------------------+
   |                  			  soa platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | rest相关入口文件									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-25       |
   +----------------------------------------------------------------------+
*/

// 性能分析
//xhprof_enable();

//set_time_limit(0);

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
    require(X_PATH . "/x.php");
} catch (Exception $e) {
    // 记录异常相关日志
    LOG::e('request exception: ' . $e->getMessage() . ' exception' . '发生错误的位置为: ' . $e->getFile() . ' 中的第' . $e->getLine() . '行' );

    if ($GLOBALS['X_G']["debug"]) {
        exception_output($e);
    } else {
        $system_index = empty($GLOBALS['X_G']['system_index']) ? 'x' : $GLOBALS['X_G']['system_index'];
        $response = array(
            'info'            => array(
                'extra' => null,
                'data'  => !empty($data) ? $data : null,
            ),
            'response_status' => 'system_error',
            'msg'             => "系统异常，请稍后再试(错误码：{$system_index}9999)"
        );
        echo json_encode($response);
        exit();
    }
}

//-----------初始化结束------------

// 性能分析
// $xhprof_data = xhprof_disable();
// $XHPROF_ROOT = realpath(dirname(__FILE__).'/xhprof');
// include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
// include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
// $file_dir    = $XHPROF_ROOT.'/tmp';
// $xhprof_runs = new XHProfRuns_Default($file_dir);
// $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
// echo "<a href='http://localhost/push/xhprof/xhprof_html/?run=$run_id&source=xhprof_foo'>分析</a>";



