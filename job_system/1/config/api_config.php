<?php
/**
 * API 相关设置
 */

return array(
    'debug'            => false,

    'auth_on'          => false,
    // API安全验证

    'filter_on'        => true,
    //是否开启sql注入过滤

    'is_app_examined'  => false,

    'language'         => 'cn',

    'log'              => array(
        'file' => array(
            'path'  => LOG_PATH,
            // 文件日志相关配置地址
            'level' => '1',
            // 日志存储等级
        ),
    ),

    // rest认证秘钥
    'authKey'          => 'bl2ke20vmq1cadqwe21l0vxop2',

    // cli认证秘钥
    'clikey'           => 'v8ejrnh1289uvfg1e4kjda9f1u',

    // 应用相关配置
    'app'              => array(
        '1'       => '性价比',
        '2'       => '淘色记',
        '3'       => '夜合欢',
        '4'       => '蓝色妖姬',
        '5'       => '情色佳人',
        '6'       => '性价比HD',
        '7'       => '性价比360版本',
        'default' => '未知',
    ),

    'platform_id_name' => array(
        1 => 'android',
        2 => 'iphone',
        3 => 'ipad',
    ),

    'platform_name_id' => array(
        'android' => 1,
        'iphone'  => 2,
        'ipad'    => 3,
    ),
    'redis_key'        => array(
        //任务执行时间的 集合
        'task_zset'    => 'task_center:task_zset',
        //存储任务的详细信息
        'hash_info'    => 'task_center:task_hash_info',
        //任务版本信息
        'version_info' => 'task_center:task_version_info',

    ),
    //每次脚本执行 取多少秒后的数据
    'get_interval'     => 3600,
    //脚本执行时去从多少秒后开始取
    'pre_time'   => 300,

);
