<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | rest相关公用函数库	无需使用配置文件				      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-07       |
   +----------------------------------------------------------------------+
*/

/**
 * 返回数据
 *
 * @param $status        int     返回的状态值
 * @param $body          string  自定义内容返回
 * @param $content_type  string  自定义数据头
 */
function sendResponse($status = 200, $body = '', $content_type = 'text/html')
{
    $status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);

    //设置返回的状态头
    header($status_header);

    //返回值数据类型
    header('Content-type: ' . $content_type);

    //回复自定义内容
    if ($body != '') {
        echo $body;
        exit;
    } else {
        $message = '';

        //设置页面显示内容
        switch ($status) {
            case 401:
                $message = 'You must be authorized to view this page.';
                break;
            case 404:
                $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                break;
            case 500:
                $message = 'The server encountered an error processing your request.';
                break;
            case 501:
                $message = 'The requested method is not implemented.';
                break;
        }

        $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

        $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
					<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
							<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>
						</head>
						<body>
							<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>
							<p>' . $message . '</p>
							<hr />
							<address>' . $signature . '</address>
						</body>
					</html>';

        echo $body;
        exit;
    }
}
