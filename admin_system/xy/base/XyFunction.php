<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架的公用函数库									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

/**
 * 类实例化，返回对象
 *
 * @param $class string 类名称
 * @throws ClassNotFoundException 类不存在异常
 * @throws FileNoFoundException   文件不存在异常
 * @return object
 */
function xyNew($class){
	in($class.'.php',true);
	
	if (!class_exists($class)) {
		require(X_PATH.'/exception/ClassNotFoundException.php');
		throw new ClassNotFoundException('该类不存在:'.$class);
	}
	
	$newObject = new $class();
	
	if( !is_object($newObject) ){
		require(X_PATH.'/exception/NewClassFaildException.php');
		throw new FileNoFoundException('无法实例化类:'.$class.'.php');
	}
	
	return $newObject;
}

/**
 * 分派程序开始
 */
function xyStart(){
	xyPoint('before_controller');

	GLOBAL $__controller, $__action, $__paramArr;
	
	$method = $__action.$GLOBALS['X_G']["actionExtension"];
	// 实例化
	$handle_controller = xyNew($__controller.$GLOBALS['X_G']["controllerExtension"]);

	// 参数
	foreach($__paramArr as $paramKey => $paramValue){
		$handle_controller -> $paramKey = $paramValue;
	}

    // 执行方法，让控制器父类获取子类的当前方法
	$handle_controller->_call_($method);
	xyPoint('after_controller');
	// 模板自动输出
}

/**
 * 服务层实例化
 *
 * @param $tableName string 表名
 * @param $isNew     int    是否重新实例化service类
 * @return object
 */
function s($tableName, $isNew = 0){
	//转化命名
	$class = convert2CamelCase($tableName).'Service';
	//组合路径
	$path = SERVICE_PATH.$class.'.php';
	
	if($isNew == 0 && $GLOBALS['X_G']['service'][$tableName]){
		return $GLOBALS['X_G']['service'][$tableName];
	}else{
		if(TRUE == is_readable($path)){
			require_once($path);
			$newService = new $class($tableName);
		}else{
			$newService = new XyService($tableName);
		}
		
		$GLOBALS['X_G']['service'][$tableName]	= $newService;
		return $newService;
	}
}

/**
 * 返回SQL语句执行链
 *
 */
function getBackSql(){
	$sqlArr	= $GLOBALS['X_G']['backSql'];
	
	return $sqlArr;
}

/**
 * 返回最后一条SQL语句
 *
 */
function getLastSql(){
	$sqlArr	= $GLOBALS['X_G']['Db']->backSqlArr;
	
	$sqlNum	= count($sqlArr);
	
	return $sqlArr[$sqlNum - 1];
}

/**
 * 转化下划线命名法至驼峰命名法
 *
 * @param $str string 要转化的字符串
 * @return string
 */
function convert2CamelCase($str){
	if(strpos($str, "_") !== false){
		$strArr	= explode("_", $str);
		$newStr	= $strArr[0];
		for($i = 1; $i < count($strArr); $i++){
			$firstLetter	= strtoupper(substr($strArr[$i], 0, 1));
			$lettersBehind	= substr($strArr[$i], 1);
			$newStr			.= $firstLetter.$lettersBehind;
		}
		return ucfirst($newStr);
	}else{
		return ucfirst($str);
	}
}

/**
 * 扩展调用方法
 *
 * @param $pointName  string         扩展点标识
 * @param $pointParam array|bool     扩展调用时的参数 array('key'=>'value')
 */
function xyPoint($pointName, $pointParam = false){

	//读出当前扩展点的扩展项
	foreach($GLOBALS['X_G']["systemXySinglePoint"][$pointName] as $className){
		$pointClass = xyNew($className);
		
		if($pointParam){
			//载入相关参数
			foreach($pointParam as $pKey => $pValue){
				$pointClass->$pKey = $pValue;
			}
		}
		
		$pointClass->_fromPoint = $pointName;
		$pointClass->doing();
	}
}

/**
 * 引入文件
 *
 * @param filename    string    需要载入的文件名
 * @param $auto       bool      是否进行搜索
 * @throws FileNoFoundException 文件未找到异常
 * @return bool
 */
function in($path, $auto = FALSE){

    // 检查是否直接可读
	if(true == is_readable($path)){
		require_once($path); // 载入文件
		return true;
	}else{
		if(true == $auto){ // 需要搜索文件
			foreach($GLOBALS['X_G']['autoSearchPath'] as $searchPath){
				if( is_readable( $searchPath.'/'.$path ) ){
					require_once($searchPath.'/'.$path);// 载入文件
					return true;
				}
			}
		}
			
		require(X_PATH.'/exception/FileNoFoundException.php');
		//抛出无法找到文件的异常
		throw new FileNoFoundException('自动引入功能无法找到该文件！文件路径为：' . $path . '，请检查该文件是否存在！');
	}
}

/**
 * 引入配置文件
 *
 * @param $filename string 配置文件名
 * @param $glob     string 全局参数名
 * @throws FileNoFoundException 文件未找到异常
 * @return bool
 */
function in_config($filename, $glob='X_U'){

	if($filename == ''){
		return true;
	}

	if(array_key_exists($glob, $GLOBALS)){
		require(X_PATH.'/exception/KeyIsByUseException.php');

		throw new FileNoFoundException('全局变量键值[' . $glob . ']已存在，请更换其他键值！');
	}

	$path = USER_PATH . '/config/' . $filename . 'Config.php';

    // 检查$filename 是否直接可读
	if(TRUE == is_readable($path)){
		$GLOBALS[$glob] = require_once($path);
		return TRUE;
	}else{
		require(X_PATH.'/exception/FileNoFoundException.php');
		//抛出无法找到文件的异常
		throw new FileNoFoundException('自动引入功能无法找到该文件！文件路径为：' . $path .  '，请检查该文件是否存在！');
	}
}

/**
 * 检查时打印
 *
 * @param $value array|string 需要检查的值
 */
function d($value){
	echo '<pre>';
	var_dump($value);
	echo '</pre>';
}

/**
 * 自动生成文件夹
 *
 */
function autoDir(){
	if(!is_dir(USER_PATH."/controller")){
		mkdir(USER_PATH."/controller");
	}
	if(!is_dir(USER_PATH."/log")){
		mkdir(USER_PATH."/log");
	}
	if(!is_dir(USER_PATH."/Public")){
		mkdir(USER_PATH."/Public");
	}
	if(!is_dir(USER_PATH."/Public/js")){
		mkdir(USER_PATH."/Public/js");
	}
	if(!is_dir(USER_PATH."/Public/css")){
		mkdir(USER_PATH."/Public/css");
	}
	if(!is_dir(USER_PATH."/Public/images")){
		mkdir(USER_PATH."/Public/images");
	}
	if(!is_dir(USER_PATH."/service")){
		mkdir(USER_PATH."/service");
	}
	if(!is_dir(USER_PATH."/template")){
		mkdir(USER_PATH."/template");
	}
	if(!is_dir(USER_PATH."/template_c")){
		mkdir(USER_PATH."/template_c");
	}
}

/**
 * 打印异常信息
 *
 * @param $exceptionObject object 需要检查的值
 */
function exception_output($exceptionObject){
	echo '异常名称为: <font color=blue>'.get_class($exceptionObject).'</font><br/><br/>';
	echo '异常消息为: <font color=blue> '.$exceptionObject->getMessage().'</font><br/><br/>';
	echo '发生错误的位置为: <font color=red>'.$exceptionObject->getFile().'</font> 中的第<font color=red>'.$exceptionObject->getLine().'</font>行<br/><br/>';
	if($exceptionObject->getCode() != 0){
		echo '异常代码为: <font color=red>'.$exceptionObject->getCode().'</font><br/><br/>';
	}
	echo '异常链: '.str_replace('#','<br/>#',$exceptionObject->getTraceAsString()).'<br/>';
	
}

/**
 * URL拼接函数
 *
 * @param $path     string  url地址 例:Index/index
 * @param $param    array   参数
 * @return string
 */
function U($path = '', $param = array()){
	$dir	= dirname(rtrim($_SERVER['SCRIPT_NAME'], '/'))."/";

	if(empty($path)){
		//若未传参则以当前地址为默认值
		$path	= $_REQUEST['request']['controller']."/".$_REQUEST['request']['action'];
	}

    $params = '';

	if(!empty($param)){
		$params	= "/";
		foreach($param AS $k => $v){
			$params	.= $params == "/"	? $k."^".$v : "^".$k."^".$v;
		}
	}

	return $dir.$path.$params;
}

/**
 * 插件工厂-用于引入插件
 *
 * @param $name    string 插件名称
 * @param $param   array  参数
 * @return object
 */
function plugin($name, $param = array()){
	// 实例化插件类
	$handle_plugin = xyNew(ucfirst($name) . $GLOBALS['X_G']["pluginExtension"]);

	// 参数
	foreach($param as $paramKey => $paramValue){
		$handle_plugin -> $paramKey = $paramValue;
	}
	
	return $handle_plugin;
}

/**
 * 模板转置
 *
 * @param $var string 需要转置的字符串
 * @return string
 */
function templateAddQuote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

/**
 * 模板解析 将自定义的标签转换成php代码
 *
 * @param $str string  需要解析的静态文件代码
 * @return string
 */
function template_parse($str) {
	$str = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $str);
	$str = preg_replace("/\{template\s+([^\}]+)\}/", "<?php include template(\\1);?>", $str);
	$str = preg_replace("/\{php\s+(.+)\}/", "<?php \\1?>", $str);
	$str = preg_replace("/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str);
	$str = preg_replace("/\{else\}/", "<?php } else { ?>", $str);
	$str = preg_replace("/\{elseif\s+(.+?)\}/", "<?php } else if(\\1) { ?>", $str);
	$str = preg_replace("/\{\/if\}/", "<?php } ?>", $str);
	$str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>", $str);
	$str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>", $str);
	$str = preg_replace("/\{\/loop\}/", "<?php } } ?>", $str);
	$str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str);
	$str = preg_replace("/<\?php([^\?]+)\?>/es", "templateAddQuote('<?php\\1?>')", $str);
	$str = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str);
	$str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "templateAddQuote('<?php echo \\1;?>')", $str);
	$str = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str);
	$str = preg_replace("/\'([A-Za-z]+)\[\'([A-Za-z\.]+)\'\](.?)\'/s", "'\\1[\\2]\\3'", $str);
	$str = preg_replace("/(\r?\n)\\1+/", "\\1", $str);
	$str = str_replace("\t", '', $str);
	$str = str_replace("__APP__", __APP__, $str);
	$str = str_replace("__URL__", __URL__, $str);
	$str = str_replace("__PUBLIC__", __PUBLIC__, $str);
	$str = str_replace("__CURRENT__", __CURRENT__, $str);
	
	return $str;
}

/**
 * 引入子模板方法
 *
 * @param $path     string 模板路径 如Public/header
 * @throws FileNoFoundException 模板文件未找到的异常
 * @return string
 */
function template($path){
	$paths	= USER_PATH."/template/".$path.".html";

	if(!file_exists($paths)){
        require(X_PATH.'/exception/FileNoFoundException.php');

        //抛出无法找到文件的异常
        throw new FileNoFoundException('模板文件'.$path.'不存在!');
	}

    $str	= template_parse(file_get_contents($paths));

    $path2	= "template_c/".$path.".php";

    $dir	= dirname($path2);
    if(!file_exists($dir)){
        mkdir($dir, '0755');
    }

    ob_start();
    ob_implicit_flush(0);
    file_put_contents($path2, $str);

    return $path2;
}

/**
 * 获取参数
 *
 * @param    $name string 名称
 * @param    $dev  string 默认值
 * @return object
 */
function request($name, $dev = '')
{
    $val = '';

    if (isset($_REQUEST[$name])) {
        $val = $_REQUEST[$name];
    }

    if ($val == '' || empty($val) || $val == null) {
        $val = $dev;
    }

    return $val;
}

/*
 * 返回IP地址
 *
 * @return IP地址，无法取得则返回0.0.0.0
 */
function getIP()
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

/**
 * 写文件
 *
 * @param    string $file 文件路径
 * @param    string $str  写入内容
 * @param    string $mode 写入模式
 *
 * @return boolean 是否成功
 */
function writeFile($file, $str, $mode = 'w')
{
    $oldMask = @umask(0);
    $fp = @fopen($file, $mode);
    @flock($fp, 3);
    if (!$fp) {
        return false;
    } else {
        @fwrite($fp, $str);
        @fclose($fp);
        @umask($oldMask);
        return true;
    }
}

/**
 * 创建文件夹目录
 *
 * @param $dir  string  文件夹路径
 * @param $mode int
 * @return bool
 */
function mkDirs($dir, $mode = 0777)
{
    if (is_dir($dir)) {
        return true;
    }

    if(!is_dir(dirname($dir))){
        mkDirs(dirname($dir), $mode);
    }

    @mkdir($dir);
    @chmod($dir, $mode);

    return true;
}

/**
 * 获取使用浏览器内核
 *
 * @return string 内核
 */
function getBrowser()
{
    //浏览信息
    $web = $_SERVER['HTTP_USER_AGENT'];

    $val = 'IE';

    $parr = array(
        array('MSIE 5'),
        array('MSIE 6'),
        array('MSIE 7'),
        array('MSIE 8'),
        array('MSIE 9'),
        array('MSIE 10'),
        array('MSIE 11'),
        array('rv:11', 'MSIE 11'),
        array('MSIE 12'),
        array('MSIE 13'),
        array('Firefox'),
        array('OPR/', 'Opera'),
        array('Chrome'),
        array('Safari')
    );

    foreach ($parr as $wp) {
        if (strpos($web, $wp[0]) > 0) {

            $val = $wp[0];

            if (isset($wp[1])) {
                $val = $wp[1];
            }

            break;
        }
    }

    return $val;
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

/**
 * 发送相关请求
 *
 * @param $url       string 请求的链接
 * @param $param     array 参数
 * @return bool|mixed
 */
function request_post($url = '', $param) {
    if ($url == '') {
        return false;
    }

    $param    =http_build_query($param);
    $postUrl  = $url;
    $curlPost = $param;

    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}

/**
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
    $path = empty($path) ? USER_PATH . '/~runtime/' : $path;
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
 * 获取数据库实例
 *
 * @return object 数据库对象
 */
function db()
{
    $db = new Db();

    return $db;
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