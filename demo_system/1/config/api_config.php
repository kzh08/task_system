<?php
/**
 * API 相关设置
 */

return array(
    'debug' => true,

    'auth_on' => false,  // API安全验证

    'filter_on' => true,  //是否开启sql注入过滤

    'is_app_examined' => false,

    'language' => 'cn',

    'log' => array(
        'file' => array(
            'path'  => LOG_PATH, // 文件日志相关配置地址
            'level' => '5',      // 日志存储等级
        ),
    ),

    // rest认证秘钥
    'authKey' => 'bl2ke20vmq1cadqwe21l0vxop2',

    // cli认证秘钥
    'clikey'  => 'v8ejrnh1289uvfg1e4kjda9f1u',

    // 应用相关配置
    'app' => array(
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

    'good_id_price_hash'    => 'good_id_price_hash',
    'good_id_sell_hash'     => 'good_id_sell_hash',
    'good_id_time_hash'     => 'good_id_time_hash',
    'good_id_default_hash'  => 'good_id_default_hash',

    'namespace_ip_is_on' => true,

    'namespace_to_ip' => array(
        'cube.ushengsheng.com' => array(
            '192.168.1.1' => array(
                'weight' => 0,
            ),
            '192.168.1.2' => array(
                'weight' => 5,
            ),
            '192.168.1.3' => array(
                'weight' => 5,
            ),
        ),
        'shop.ushengsheng.com' => array(
            '192.168.1.4' => array(
                'weight' => 3,
            ),
            '192.168.1.5' => array(
                'weight' => 2,
            ),
            '192.168.1.6' => array(
                'weight' => 2,
            ),
        ),
        'forum.ushengsheng.com' => array(
            '192.168.1.7' => array(
                'weight' => 3,
            ),
            '192.168.1.8' => array(
                'weight' => 2,
            ),
            '192.168.1.6' => array(
                'weight' => 2,
            ),
        ),
    ),
);
