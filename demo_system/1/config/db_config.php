<?php

/**
 * MySQL及Redis配置
 */

return array(
    // 数据库连接配置
    'db' => array(
        'driver'    => 'mysql', // 驱动
        'host'      => '192.168.10.5', // 地址
        'port'      => 3306, // 端口
        'username'  => 'root', // 用户名
        'password'  => '123456', // 密码
        //'host'      => '117.28.255.177', // 地址
        //'port'      => 3307, // 端口
        //'username'  => 'Hb_zS_tmp', // 用户名
        //'password'  => 'hB_dPsyOjw1d#Vflj*5', // 密码
        'database'  => 'account', // 默认库
        'prefix'    => '', // 表的前缀
        'cachePath' => '', // 数据库缓存路径
        'cacheTime' => 60, // 数据库缓存周期
    ),

    // 只读库连接配置(涉及主从一致性等问题，勿滥用)
    'read_db' => array(
        array(
            'driver'    => 'mysql', // 驱动
            'host'      => '10.10.10.5', // 地址
            'port'      => 3306, // 端口
            'username'  => 'root', // 用户名
            'password'  => '123456', // 密码
            'database'  => 'account', // 默认库
        ),
        array(
            'driver'    => 'mysql', // 驱动
            'host'      => '10.10.10.5', // 地址
            'port'      => 3306, // 端口
            'username'  => 'root', // 用户名
            'password'  => '123456', // 密码
            'database'  => 'account', // 默认库
        ),
    ),

    // 用于API安全验证的redis+防止表单重复提交
    'auth_redis' => array(
        'host'      => '10.10.10.10',
        'port'      => 8000,
        'timeout'   => 2, // 超时时间
    ),

);
