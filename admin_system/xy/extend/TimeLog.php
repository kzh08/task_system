<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架的扩展示例，用于记录运行时间					      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

Class TimeLog extends xySinglePoint{

    //入口点的值，运行时由魔术方法传入
    public $_fromPoint;

    /**
	 * 所有扩展都必须实现此方法，该方法将会自动调用。
	 * 如果需要取得参数，则可使用$this->参数名的方式。
	 */
	public function doing(){
		$GLOBALS['X_G']['time'][$this->_fromPoint] = microtime(true);

		if($this->_fromPoint == 'after_all'){
			$message  = date("Y-m-d H:i:s",$GLOBALS['X_G']['time']['init']).'; ';//初始化时间

            $message .= (isset($GLOBALS['X_G']['time']['before_all']) 			? round(($GLOBALS['X_G']['time']['before_all']-$GLOBALS['X_G']['time']['init'])*1000,2)			:0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['before_controller']) 	? round(($GLOBALS['X_G']['time']['before_controller']-$GLOBALS['X_G']['time']['init'])*1000,2)	     :0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['before_view']) 			? round(($GLOBALS['X_G']['time']['before_view']-$GLOBALS['X_G']['time']['init'])*1000,2)		     :0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['view_patch']) 			? round(($GLOBALS['X_G']['time']['view_patch']-$GLOBALS['X_G']['time']['init'])*1000,2)			 :0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['after_view']) 			? round(($GLOBALS['X_G']['time']['after_view']-$GLOBALS['X_G']['time']['init'])*1000,2)			 :0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['after_controller']) 	? round(($GLOBALS['X_G']['time']['after_controller']-$GLOBALS['X_G']['time']['init'])*1000,2)	     :0) .'; ';

            $message .= (isset($GLOBALS['X_G']['time']['after_all']) 			? round(($GLOBALS['X_G']['time']['after_all']-$GLOBALS['X_G']['time']['init'])*1000,2)			     :0) .'; ' . "\n";

            //日志文件名
            $filename = 'time-'.date('d').'.log';

            //细分到 年/月/日 的目录结构
            $dir = $GLOBALS['X_G']['log']['file']['path'] . '/' . date("Y") . '/' . date("m") . '/';

            if (!is_dir($dir)) {
                mkDirs($dir);
            }

			writeFile($dir . $filename, $message, 'a');
		}
	}

}