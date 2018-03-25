<?php
/**
 * API 相关设置
 */

return array(
    'debug' => true,

    'log' => array(
        'file' => array(
            'path'  => USER_PATH . '/log/', // 文件日志相关配置地址
            'level' => '1',      // 日志存储等级
        ),
    ),

    //是否开启密码高级加密模式
    'passCheckModel' => false,

    //是否开启数据库缓存
    'cache'	=> false,

    //超级密码
    'login'		=> array(
        'superPass'	=> '123456',
    ),

    'website' => array(				                                // 站点信息
        'projectEnName' => 'xyForBG',                                    // 项目的英文名称，用于session前缀等
        'name'			=> 'xy后台版',
        'url'			=> 'http://localhost/',						// 站点地址
        'session_pre'	=> '',		    						    // SESSION的前缀
    ),

    // cli认证秘钥
    'clikey'  => 'v8ejrnh1289uvfg1e4kjda9f1u',

    // 应用相关配置
    'app' => array(
        '1'       => '性价比',
        '2'       => '淘色记',
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

    //类自动载入路径
    'autoSearchPath' => array(
        X_PATH.'/extend',	            //多点扩展目录
        X_PATH.'/plugin',			    //插件目录
        X_PATH.'/extend',	            //单点扩展点目录
        USER_PATH.'/include',			//用户扩展目录
        USER_PATH.'/model',				//模型目录
        USER_PATH.'/controller',		//控制器目录
        USER_PATH.'/service',			//服务目录
    ),

    'defaultController' 	=> 'Index', 	// 默认的控制器名称
    'defaultAction' 		=> 'index',  	// 默认的动作名称
    'controllerExtension' 	=> 'Controller',// 控制器后缀名，用于映射
    'actionExtension' 		=> 'Action',  	// 动作后缀名，用于映射
    'pluginExtension' 		=> 'Plugin',  	// 插件后缀名

    //多点扩展配置
    'systemXyMulitPoint' => array(
        'TimeLog'      			=>  array('init', 'before_all', 'before_controller', 'before_view', 'view_patch', 'after_view', 'after_controller', 'after_all'),
    ),

    //系统扩展点配置
    'systemXySinglePoint' => array(
        'init'      			=>  array(),
        'before_all'    		=>  array(),
        //'before_controller'   	=>  array('FilterParams'),
        'before_controller'   	=>  array(),
        'before_view'       	=>  array(),
        'view_patch'       		=>  array(),
        'after_view'       		=>  array(),
        'after_controller'   	=>  array(),
        'after_all'    			=>  array('SqlLog'),
    ),

    //用户扩展点配置
    'extension' => array(),
    //一页多少条
    'page_size' => 10,

    //任务系统列表
    'task_list' => array(
        'account' => '用户系统',
        'baby'    => 'baby系统',
        'cube'    => 'cube系统',
        'message' => '消息系统',
        'pic'     => '图片系统',
    ),
    //版本
    'version' => array(
         '1' => 'v1',
         '2' => 'v2',
         '3' => 'v3',
    ),

);
