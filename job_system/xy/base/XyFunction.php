<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 公用函数库	需要使用相关配置文件					      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-03       |
   +----------------------------------------------------------------------+
*/

/*
 * 对发送过来的请求进行校验
 *
 * @param appid        分配的应用名
 * @param key          生成的密钥
 * @param time         生成密钥时的时间
 */
function authRequest($appid, $key, $time, $xkey, $hkey, $ikey, $okey, $tkey)
{
    $response = array(
        'info' => array(
            'extra' => null,
            'data'  => null,
        ),
        'response_status' => null,
        'msg' => null
    );

    if($xkey == '' || $hkey == '' || $ikey == '' || $okey  == '' || $tkey == ''){
        $message = 'param not true! [$appid:' . $appid . ' $key:' . $key . ' $time: ' . $time . '] $app_version ' . intval($_SERVER['HTTP_AV']);
        LOG::w($message, 'security');
        $response['response_status'] = '1004';
        $response['msg'] = l('param_not_true');
        sendResponse(401, json_encode($response));
    }

    $lkey   = getAuthValue($xkey, $hkey, $ikey, $okey, $tkey);

    $token  = $_SERVER['HTTP_TO'];

    $authKey        = md5($appid . $GLOBALS['X_G']['authKey']);
    $old_authKey    = md5($authKey . $time . $lkey);
    //升级key的规则  by xiaoy-20150812
    $authKey        = md5($authKey . $time . $lkey) . md5($authKey . $token . $lkey);

    //15分钟以上数据进行过滤
    if (time() - substr($time, 0, 11) > 60 * 15) {
        $message = 'auth timeout! [$appid:' . $appid . ' $key:' . $key . ' $time: ' . $time . '] $app_version ' . intval($_SERVER['HTTP_AV']);
        LOG::w($message, 'security');
        $response['response_status'] = '1007';
        $response['msg'] = l('auth_timeout');
        sendResponse(401, json_encode($response));
    }

    if ($key == $authKey || ($key == $old_authKey && $authKey = $old_authKey)) {
        //进一步校验，一次key只能使用一次
        $redis      = XyRedis::getRedis('auth_redis');

        //安全等相关原因，key不放于配置文件中
        $redisKey   = 'security_key_used_hash';

        $value  = $redis->get($redisKey . $authKey . $GLOBALS['X_G']['system_index']);

        //可调整的允许次数
        if($value >= 1){
            $message = 'Auth key being used! [$appid:' . $appid . ' $key:' . $key . ' $time: ' . $time . '] $app_version ' . intval($_SERVER['HTTP_AV']);
            LOG::w($message, 'security');
            $response['response_status'] = '1006';
            $response['msg'] = l('auth_key_used');
            sendResponse(401, json_encode($response));
        }else{
            //允许次数为1的时候不生效
            if($value >= 1){
                $message = 'Auth key being used! [' . $value . '] [$appid:' . $appid . ' $key:' . $key . ' $time: ' . $time . '] $app_version ' . intval($_SERVER['HTTP_AV']);
                LOG::w($message, 'security');
            }

            $redis->incr($redisKey . $authKey . $GLOBALS['X_G']['system_index']);
            $redis->setTimeout($redisKey . $authKey . $GLOBALS['X_G']['system_index'], 900);
        }
    } else {
        $message = 'auth failed! maybe attack! [$appid:' . $appid . ' $key:' . $key . ' $time: ' . $time . '] $app_version ' . intval($_SERVER['HTTP_AV']);
        LOG::w($message, 'security');
        $response['response_status'] = '1005';
        $response['msg'] = l('auth_failed');
        sendResponse(401, json_encode($response));
    }
}

/*
 * 生成认证用的随机值
 *
 */
function getAuthValue($xkey, $hkey, $ikey, $okey, $tkey){
    $keyArray = array(
        $xkey,
        $hkey,
        $ikey,
        $okey
    );

    $keys = array(
        $xkey['1'] => '0',
        $hkey['1'] => '1',
        $ikey['1'] => '2',
        $okey['1'] => '3',
    );

    krsort($keys);

    $n = 0;

    $lkey = '';

    foreach($keys as $position){

        if( $tkey == 'KFDM' && ($n == 0 || $n == 1) ){
            $lkey .= $keyArray[$position];
        }elseif( $tkey == 'SEFD' && ($n == 1 || $n == 3) ){
            $lkey .= $keyArray[$position];
        }elseif( $tkey == 'VFRS' && ($n == 0 || $n == 2) ){
            $lkey .= $keyArray[$position];
        }elseif( $tkey == 'GRSW' && ($n == 0 || $n == 2 || $n == 3) ){
            $lkey .= $keyArray[$position];
        }

        $n++;
    }
    
    return $lkey;
}

/*
 * 接收请求预处理
 *
 * @return 返回RestRequest对象
 */
function xyStart()
{
    //获取相关动作
    $request_method = strtolower($_SERVER['REQUEST_METHOD']);

    //存储发送过来的数据，不能删除，用于兼容rest请求
    $data = $_REQUEST;

    switch ($request_method) {
        case 'get':
            break;
        case 'post':
            break;
        case 'put':
            break;
    }

    if ($_SERVER['HTTP_TIME'] != '') {
        $time = $_SERVER['HTTP_TIME'];
    } else {
        $time = $data['time'];
    }

    if ($_SERVER['HTTP_VAILD'] != '') {
        $vaild = $_SERVER['HTTP_VAILD'];
    } else {
        $vaild = $data['vaild'];
    }

    if ($_SERVER['HTTP_APPID'] != '') {
        $appid = $_SERVER['HTTP_APPID'];
    } else {
        $appid = $data['appid'];
    }

    if ($_SERVER['HTTP_XKEY'] != '') {
        $xkey = $_SERVER['HTTP_XKEY'];
    } else {
        $xkey = $data['xkey'];
    }

    if ($_SERVER['HTTP_HKEY'] != '') {
        $hkey = $_SERVER['HTTP_HKEY'];
    } else {
        $hkey = $data['hkey'];
    }

    if ($_SERVER['HTTP_IKEY'] != '') {
        $ikey = $_SERVER['HTTP_IKEY'];
    } else {
        $ikey = $data['ikey'];
    }

    if ($_SERVER['HTTP_OKEY'] != '') {
        $okey = $_SERVER['HTTP_OKEY'];
    } else {
        $okey = $data['okey'];
    }

    if ($_SERVER['HTTP_TKEY'] != '') {
        $tkey = $_SERVER['HTTP_TKEY'];
    } else {
        $tkey = $data['tkey'];
    }

    // 是否排除api安全验证，主要用于callback
    $isAuthExcluded = false;

    if (isset($GLOBALS['X_G']["auth_exclusion"][$_REQUEST['xyC']])) {
        if ($GLOBALS['X_G']["auth_exclusion"][$_REQUEST['xyC']] === array()
            || in_array($_REQUEST['xyM'], $GLOBALS['X_G']["auth_exclusion"][$_REQUEST['xyC']]))
            $isAuthExcluded = 1;
    }

    // 临时写死，Soa和Callback下的接口直接排除api安全验证
    if ($_REQUEST['xyC'] == 'Soa' || $_REQUEST['xyC'] == 'Callback') {
        $isAuthExcluded = 1;
    }

    // 安全校验
    if ($GLOBALS['X_G']["auth_on"] && !$isAuthExcluded){
        authRequest($appid, $vaild, $time, $xkey, $hkey, $ikey, $okey, $tkey);
    }
}


/*
 * 返回SQL语句执行链
 *
 */
function getBackSql()
{
    $sqlArr = $GLOBALS['X_G']['backSql'];

    return $sqlArr;
}

/*
 * 返回最后一条SQL语句
 *
 */
function getLastSql()
{
    $sqlArr = $GLOBALS['X_G']['backSql'];

    $sqlNum = count($sqlArr);

    return $sqlArr[$sqlNum - 1];
}

/*
 *    返回最后一条SQL语句影响的条数
 *
 */
function getAffect()
{
    return $GLOBALS['X_G']['Db']->getAffect();
}

/*
 * 转化下划线命名法至驼峰命名法
 *
 * @param str 要转化的字符串
 */
function convert2CamelCase($str)
{
    if (strpos($str, "_") !== false) {
        $strArr = explode("_", $str);
        $newStr = $strArr[0];
        for ($i = 1; $i < count($strArr); $i++) {
            $firstLetter = strtoupper(substr($strArr[$i], 0, 1));
            $lettersBehind = substr($strArr[$i], 1);
            $newStr .= $firstLetter . $lettersBehind;
        }
        return ucfirst($newStr);
    } else {
        return ucfirst($str);
    }
}

/*
 * 引入文件
 *
 * @param filename        需要载入的文件名
 * @param auto_search     是否开启自动载入
 */
function in($path, $auto = false)
{
    if (true == is_readable($path)) { // 检查$sfilename是否直接可读
        require_once($path); // 载入文件
        return true;
    } else {
        if (true == $auto) { // 需要搜索文件
            foreach ($GLOBALS['X_G']['autoSearchPath'] as $num => $searchPath) {
                if (is_readable($searchPath . '/' . $path)) {
                    require_once($searchPath . '/' . $path); // 载入文件
                    return true;
                }
            }
        }

        require(X_PATH . '/exception/FileNotFoundException.php');
        //抛出无法找到文件的异常
        throw new FileNotFoundException('自动引入功能无法找到该文件！文件路径为：' . $path . '，请检查该文件是否存在！');
    }
}

/*
 * 检查时打印
 *
 * @param value            需要检查的值
 * @param memo             可以配置相关key来辨认
 */
function d($value, $memo = '')
{
    echo '<pre>';

    if ($memo != '') {
        echo $memo . ':<br/>';
    }

    var_dump($value);

    echo '</pre>';
}

/*
 * 打印异常信息
 *
 * @param $exceptionObject            需要检查的值
 */
function exception_output($exceptionObject)
{
    echo '异常名称为: <font color=blue>' . get_class($exceptionObject) . '</font><br/><br/>';
    echo '异常消息为: <font color=blue> ' . $exceptionObject->getMessage() . '</font><br/><br/>';
    echo '发生错误的位置为: <font color=red>' . $exceptionObject->getFile(
        ) . '</font> 中的第<font color=red>' . $exceptionObject->getLine() . '</font>行<br/><br/>';
    if ($exceptionObject->getCode() != 0) {
        echo '异常代码为: <font color=red>' . $exceptionObject->getCode() . '</font><br/><br/>';
    }
    echo '异常链: ' . str_replace('#', '<br/>#', $exceptionObject->getTraceAsString()) . '<br/>';

}

/*
 * 在命令行打印异常信息
 *
 * @param $exceptionObject            需要检查的值
 */
function exception_output_cli($exceptionObject)
{
    echo '异常名称为: ' . get_class($exceptionObject) . "\n";
    echo '异常消息为: ' . $exceptionObject->getMessage() . "\n";
    echo '发生错误的位置为: ' . $exceptionObject->getFile() . ' 中的第' . $exceptionObject->getLine() . '行' . "\n";
    if ($exceptionObject->getCode() != 0) {
        echo '异常代码为: ' . $exceptionObject->getCode() . "\n";
    }
    echo '异常链: ' . $exceptionObject->getTraceAsString() . "\n";

}

/*
 * 返回IP地址
 *
 * @return IP地址，无法取得则返回0.0.0.0
 */
function GetIP()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
        $cip = $_SERVER["REMOTE_ADDR"];
    } else {
        $cip = "0.0.0.0";
    }

    return $cip;
}

/*
 * 返回mail类
 *
 * @return mail实例
 */
function getMail()
{

    require(X_PATH . "/class/MailClass.php");

    $mail = new Mail();

    return $mail;
}

/*
 * 返回JSON格式数据，APP端使用
 *
 * @param $status              状态值
 * @param $data                数据
 * @param $msg                 错误信息
 */
function json_output($status = true, $data = null, $msg = null)
{
    $arr['info'] = array(
        'data' => $data,
    );

    if ($status) {
        $arr['response_status'] = 'success';
    } else {
        $arr['response_status'] = 'failure';
    }

    $arr['msg'] = $msg;


    echo json_encode($arr);
    exit ();
}

/*
 * 返回JSON格式错误数据
 *
 * @param $msg                 错误信息
 */
function error_output($msg = null)
{
    json_output(false, null, $msg);
}

/**
 * 处理基本参数
 *
 * @author Sam Lu
 */
function init_basic_params()
{
    $basic_params = array();

    // 时间戳
    $basic_params['timestamp'] = intval($_SERVER['HTTP_TP']);

    // app相关参数
    $basic_params['app_version'] = intval($_SERVER['HTTP_AV']);
    $basic_params['channel']     = strtolower(trim($_SERVER['HTTP_CH']));
    $basic_params['channel']     = !empty($basic_params['channel']) ? $basic_params['channel'] : strtolower(trim($_REQUEST['WAP_CH']));
    $basic_params['appcode'] = intval($_SERVER['HTTP_AP']);
    $basic_params['appcode'] = !empty($basic_params['appcode']) ? $basic_params['appcode'] : 1;
    $basic_params['alias']   = strtolower(trim($_SERVER['HTTP_AL']));
    $basic_params['gender']  = (int) trim($_SERVER['HTTP_SX']);
    $basic_params['gender']  = !empty($basic_params['gender']) ? $basic_params['gender'] : 1;

    // 设备相关参数
    // 网络情况，1:2G 2:3G 3:wifi 4:4G  5:5G
    $basic_params['access'] = intval($_SERVER['HTTP_AC']);

    switch($basic_params['access']){
        case 'NON' :
            $basic_params['access'] = 3;
            break;
        case '2G' :
            $basic_params['access'] = 1;
            break;
        case '3G' :
            $basic_params['access'] = 2;
            break;
        case '4G' :
            $basic_params['access'] = 4;
            break;
        case '5G' :
            $basic_params['access'] = 5;
            break;
        default :
            $basic_params['access'] = 3;
    }

    $basic_params['os']         = strtolower(trim($_SERVER['HTTP_OS']));
    $basic_params['os_version'] = trim($_SERVER['HTTP_OV']);
    $basic_params['mac']        = trim($_SERVER['HTTP_MA']);
    $basic_params['token']      = trim($_SERVER['HTTP_TO']);

    //平台名称
    $basic_params['platform_name'] = strtolower(trim($_SERVER['HTTP_PL']));
    $basic_params['platform_name'] = !empty($basic_params['platform_name']) ? $basic_params['platform_name'] : 'wap';

    //平台ID
    $basic_params['platform_id'] = c('platform_name_id.' . $basic_params['platform_name']);
    $basic_params['platform_id'] = !empty($basic_params['platform_id']) ? $basic_params['platform_id'] : 4;

    $basic_params['ip'] = GetIP();

    // 高度宽度
    $basic_params['height'] = intval($_SERVER['HTTP_HT']);
    $basic_params['width']  = intval($_SERVER['HTTP_WT']);

    $basic_params['partner'] = trim($_SERVER['HTTP_PN']); // 合作商参数（非必需）如：partner=jiuyi （91市场）
    if (!empty($_SERVER['HTTP_LO'])) { // 经度
        $basic_params['longitude'] = trim($_SERVER['HTTP_LO']);
    }
    if (!empty($_SERVER['HTTP_LA'])) { // 纬度
        $basic_params['latitude'] = trim($_SERVER['HTTP_LA']);
    }
	
	// 多个项目可以公用该系统，上传项目名称区分不同项目日志
    $project_name = empty($_SERVER['HTTP_PR']) ? 'taqu' : trim($_SERVER['HTTP_PR']);
    $basic_params['project_name']  = $project_name;

    $GLOBALS['basic_params'] = $basic_params;
}

/**
 * 获取基本参数的值
 *
 * @author Sam Lu
 * @param string $field 默认返还全部基本参数
 * @return mixed
 */
function b($field = '')
{
    if (!empty($field)) {
        return $GLOBALS['basic_params'][$field];
    }

    return $GLOBALS['basic_params'];
}

/**
 * 实例化service类
 *
 * @author Sam Lu
 * @param      $service_name
 * @param null $params
 * @throws ClassNotFoundException
 * @return mixed
 */
function s($service_name, $params = null)
{
    $service_class_name = $service_name . 'Service';
    $class_path = SERVICE_PATH . '/' . ucfirst($service_class_name) . '.php';
    in($class_path);
    if (!class_exists($service_class_name)) {
        require(X_PATH . '/exception/ClassNotFoundException.php');
        throw new ClassNotFoundException("class [{$service_class_name}] does not exist!");
    }

    return new ProxyService($service_class_name, $params);
    //return new $service_class_name($params);
}

/**
 * 获取配置
 *
 * @author Sam Lu
 * @param  string $field 可通过「.」号指定层级，如：redis.port
 * @return mixed
 */
function c($field)
{
    $field_levels = explode('.', $field);
    $config = $GLOBALS['X_G'];
    $size = count($field_levels);
    for ($i = 0; $i < $size; $i++) {
        $config = $config[$field_levels[$i]];
    }

    return $config;
}

/**
 * 实例化Redis
 *
 * @author Sam Lu
 * @param string $redis_name redis配置的名称
 * @param int $db_index      db序号
 * @return Redis
 */
function r($redis_name, $db_index = -1)
{
    return XyRedis::getRedis($redis_name, $db_index);
}
/**
 * 生成redis key
 * 第一个参数提供redis_key下的配置名，第二个参数起提供需要替换的值
 * 例：redis_key('portal_ad', 2, 1)  生成 shop:ad:2:1
 *
 * @author Sam Lu
 * @return mixed
 */
function redis_key()
{
    $arguments = func_get_args();
    $arguments[0] = c('redis_key.' . $arguments[0]);

    return call_user_func_array('sprintf', $arguments);
}

/**
 * 获取图片在不同网络情况下的大小设置
 * 用于API返回数据的「extra」字段
 * 从配置文件的「img_size」下获取
 *
 * @author Sam Lu
 * @return array
 */
function img_size_extra()
{
    $arguments = func_get_args();
    $config_list = array();
    foreach ($arguments as $argument) {
        $config = c('img_size.' . $argument);
        if (!empty($config)) {
            $field = $config['field'];
            $config_list[$field] = $config['sizes'];
        }
    }
    if (count($config_list) == 1) {
        return array_pop($config_list);
    }

    return $config_list;
}

/**
 * 获取数据库实例 
 * @param $db_config_name
 *
 * @return object 数据库对象
 */
function db($db_config_name = 'db')
{
    if (empty($GLOBALS['X_G']['db_instances'][$db_config_name])) {
        $GLOBALS['X_G']['db_instances'][$db_config_name] = new Db($db_config_name);
    }

    return $GLOBALS['X_G']['db_instances'][$db_config_name];
}

/**
 * 获取只读数据库实例
 *
 * @param $db_config_name
 *
 * @return object 数据库对象
 */
function readDb($db_config_name = 'db')
{
    if (empty($GLOBALS['X_G']['read_db_instance'])) {
        $GLOBALS['X_G']['read_db_instance'][$db_config_name] = new Db($db_config_name, true);
    }

    return $GLOBALS['X_G']['read_db_instance'][$db_config_name];
}

/**
 * @deprecated 由于soa支持分布式的原因，不建议使用
 *
 * 生成/操作文件缓存
 *
 * @param string       $filename 文件名
 * @param string       $content  写入的数据[不填时:读取文件内容, null时:删除文件]
 * @param string       $path     文件存储路径
 *
 * @return bool|array
 */
function f($filename, $content = '', $path = '') 
{
	$path = empty($path) ? VERSION_PATH . '/~runtime/' : $path;
    $filename = $path . $filename . '.php';
	
	if ('' !== $content) {
		// 删除文件
		if (null === $content) {
			return is_file($filename) ? unlink($filename) : false;
		} else {
			$dir = dirname($filename);
			if (! is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
			if(false === file_put_contents($filename, serialize($content))){
				$message = '文件写入失败：' . $filename;
				LOG::w($message);
				return false;
			}
		}
		return true;
	}
	
	if (! is_file($filename)) {
		return false;
	}
	
	$content = file_get_contents($filename);
	$info = array(
		'mtime'   => filemtime($filename),
		'content' => unserialize($content)
	);
	return $info;
}

/**
 * 返回配置中的文案内容，用于设置service中的error信息
 * 为了全局变量里存放的内容少一些，又因为错误信息只会出现一次，因此直接读取文件返回。
 *
 * @param $name
 *
 * @return mixed
 */
function l($name){
    if($GLOBALS['X_G']['language'] == '' || $GLOBALS['X_G']['language'] == 'cn'){
        $path       = VERSION_PATH . '/config/letter_config.php';
        $errorMsg   = '系统异常，请稍后再试(错误码：9998)';
    }else{
        $path       = VERSION_PATH . '/config/' . $GLOBALS['X_G']['language'] . '_letter_config.php';
        $errorMsg   = 'system error,please retry later(error code:9998)';
    }

    if (is_readable($path)) {
        $letter = require($path);
        return $letter[$name];
    }else{
        return $errorMsg;
    }
}

/**
 * 防sql注入方法，仅针对post、get、cookie参数，头部由于某些原因先不加
 *
 * @return array|bool 成功返回true，失败返回键值对数组
 */
function filterParams(){
    $getFilter		= "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

    $postFilter		= "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

    $cookieFilter	= "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

    /*防注入开始*/
    foreach($_GET AS $key => $value){
        $result = privateFilterIsMayBeAttack($key, $value, $getFilter);

        if(!$result){
            return array(
                'type'  => 'get',
                'key'   => $key,
                'value' => $value
            );
        }
    }

    foreach($_POST AS $key => $value){
        $result = privateFilterIsMayBeAttack($key, $value, $postFilter);

        if(!$result){
            return array(
                'type'  => 'post',
                'key'   => $key,
                'value' => $value
            );
        }
    }

    foreach($_COOKIE AS $key => $value){
        $result = privateFilterIsMayBeAttack($key, $value, $cookieFilter);

        if(!$result){
            return array(
                'type'  => 'cookie',
                'key'   => $key,
                'value' => $value
            );
        }
    }

    return true;
    /*防注入结束*/
}

/**
 * 内部方法，检测是否存在sql注入的危险
 *
 * @param $strFiltKey       string 键
 * @param $strFiltValue     string 值
 * @param $arrFiltReq       string 过滤的字符串
 */
function privateFilterIsMayBeAttack($strFiltKey, $strFiltValue, $strFiltReq){
    if(is_array($strFiltValue)){
        $strFiltValue = implode($strFiltValue);
    }

    if(preg_match("/".$strFiltReq."/is", $strFiltValue) == 1){
        return false;
    }else{
        return true;
    }
}

/**
 * 获取及处理参数
 *
 * @param $name             string 参数名
 * @param $defaultValue     string 参数的默认值
 * @param $type             string 需要强转的类型
 * @param $isFilter         bool   是否开启xss过滤
 * @param $length           int    需要留下的长度
 *
 * @return bool|float|int|string 参数值
 */
function getParam($name, $defaultValue = '', $type = null, $isFilter = false, $length = null){
    //如果已经处理过，则直接返回，加快访问速度
    if($GLOBALS['X_G']['request'][$name]['status']){
        return $GLOBALS['X_G']['request'][$name]['value'];
    }

    //获取及设置默认值
    $value = $_REQUEST[$name] ? $_REQUEST[$name] : $defaultValue;

    //进行xss过滤
    if(!is_array($value) && $isFilter){
        $value = addslashes($value);
        $value = htmlspecialchars($value);
    }

    //强转类型
    if(!empty($type)){
        switch($type){
            case 'int':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'double':
                $value = doubleval($value);
                break;
            case 'bool':
                $value = boolval($value);
                break;
            case 'array':
                $value = (array)$value;
                break;
            default:
                $value = strval($value);
        }
    }

    //截断长度
    if(!empty($length)){
        $value = substr($value, 0, $length);
    }

    //缓存处理结果
    $GLOBALS['X_G']['request'][$name] = array(
        'status'   => true,
        'value'    => $value,
    );

    return $value;
}

/**
 * 获取提交的JSON数据
 *
 * @author Sam Lu
 *
 * @param bool $return_raw true：返回原始数据；false：返回json_decode后的数组
 *
 * @return string|array
 */
function get_json_input($return_raw = false)
{
    $raw = file_get_contents('php://input');
    if ($return_raw == true) {
        return $raw;
    }

    $raw = trim($raw);
    if (empty($raw)) {
        return array();
    }

    $array = json_decode($raw, true);

    return $array ? : array();
}

/**
 * 获取cookie数据
 *
 * @author Sam Lu
 *
 * @param string $field
 * @param mixed  $default_value
 *
 * @return null
 */
function get_cookie($field, $default_value = null)
{
    return isset($_COOKIE[$field]) ? $_COOKIE[$field] : $default_value;
}