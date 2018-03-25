<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 日志记录类											      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-03       |
   +----------------------------------------------------------------------+
*/

final class LOG
{
    // 日志等级
    private static $levelArr = array(
        '0' => 'debug',
        '1' => 'info',
        '3' => 'notice',
        '5' => 'warning',
        '7' => 'error',
    );

    /**
     * SeasLog存储日志文件
     *
     * @param    string $level   存储等级
     * @param    string $message 写入内容
     *
     * @return boolean 是否成功
     */
    private static function saveFile($level, $message)
    {
        $pathConfig     = $GLOBALS['X_G']['log']['file']['path'];
        $levelConfig    = $GLOBALS['X_G']['log']['file']['level'];

        //全局唯一日志标示
        if($GLOBALS['X_G']['soa']['bugFinderDistinct']){
            $message        = $GLOBALS['X_G']['soa']['bugFinderDistinct'] . ' # ' . $message;
        }

        if($_REQUEST['xyC'] && $_REQUEST['xyM'] && $GLOBALS['X_G']['version']){
            $message        = $GLOBALS['X_G']['version'] . ' # ' . $_REQUEST['xyC'] . ' # ' . $_REQUEST['xyM'] . ' # ' . $message;
        }

        $message = getIp() . " # " . $message;

        $type = self::$levelArr[$level];

        SeasLog::setBasePath($pathConfig);

        //假如为debug模式下则记录所有日志
        //假如为开发模式则记录配置文件中配置的级别
        if ($GLOBALS['X_G']["debug"] || $levelConfig <= $level) {
            SeasLog::$type($message);
        }

    }

    /**
     * debug级别日志
     *
     * @param string $message 信息
     * @return boolean 是否成功
     */
    public static function d($message)
    {
        return self::saveFile(0, $message);
    }

    /**
     * info级别日志
     *
     * @param string $message 信息
     * @return boolean 是否成功
     */
    public static function i($message)
    {
        return self::saveFile(1, $message);
    }

    /**
     * notice级别日志
     *
     * @param string $message 信息
     * @return boolean 是否成功
     */
    public static function n($message)
    {
        return self::saveFile(3, $message);
    }

    /**
     * warning级别日志
     *
     * @param string $message 信息
     * @return boolean 是否成功
     */
    public static function w($message)
    {
        return self::saveFile(5, $message);
    }

    /**
     * error级别日志
     *
     * @param string $message 信息
     * @return boolean 是否成功
     */
    public static function e($message)
    {
        return self::saveFile(7, $message);
    }
}
