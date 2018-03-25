<?php

/**
 * MySQL及Redis配置
 */

return array(
    // 数据库连接配置
    'db' => array(
        'driver'    => 'mysql', // 驱动
        'host'      => '10.10.10.5', // 地址
        'port'      => 3306, // 端口
        'username'  => 'root', // 用户名
        'password'  => '123456', // 密码
        'database'  => 'task_center', // 默认库
        'prefix'    => '', // 表的前缀
        'cachePath' => '', // 数据库缓存路径
        'cacheTime' => 60, // 数据库缓存周期
    ),

    // 用于API安全验证的redis+防止表单重复提交
    'auth_redis' => array(
        'host'      => '10.10.10.5',
        'port'      => 8000,
        'timeout'   => 2, // 超时时间
    ),
    //任务系统redis
    'task_redis' => array(
        'host'    => '10.10.10.5',
        'port'    => 8000,
        'auth'    => '',
        'timeout' => 1.5
    ),
);
