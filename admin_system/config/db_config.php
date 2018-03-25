<?php

/**
 * MySQL及Redis配置
 */

return array(
    // 数据库连接配置
    'db' => array(
        'driver' 	=> 'mysql',   			// 驱动
        'host' 		=> '10.10.10.5', 	// 地址
        'port' 		=> 3306,        		// 端口
        'username' 	=> 'root',     			// 用户名
        'password' 	=> '123456',      	// 密码
        'database' 	=> 'task_center',			    // 默认库
        'cachePath' => USER_PATH.'/cache/', // 数据库缓存路径
        'cacheTime' => 60,  				// 数据库缓存周期
    ),

);
