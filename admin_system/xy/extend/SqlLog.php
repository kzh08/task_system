<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 某此操作所使用的sql语句写入日志	      	 	  	  				          |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

Class SqlLog extends xySinglePoint{

	function doing(){
        $sessionName    = $_SESSION[$GLOBALS['X_G']['website']['projectEnName'] . 'adminName'];

		$user 		    = !empty($sessionName) ? $sessionName : '未登录';

		$message	    = '操作人：'.$user.' 操作时间：'.date("Y-m-d H:i:s") . "\n";

		$arr 		    = $GLOBALS['X_G']['backSql'];

		if($arr[0] == 'set names utf8'){
			array_shift($arr);
		}elseif($arr[0] == ''){
			return;
		}

		if(!empty($arr)){
			$message   .= implode(";\n", $arr);

			$message   .=  "\n" . "*********************************************************************************" . "\n";

            //日志文件名
            $filename = 'user-'.date('d').'.log';

            //细分到 年/月/日 的目录结构
            $dir = $GLOBALS['X_G']['log']['file']['path'] . '/' . date("Y") . '/' . date("m") . '/';

            if (!is_dir($dir)) {
                mkDirs($dir);
            }

            writeFile($dir . $filename, $message, 'a');
		}
	}

}