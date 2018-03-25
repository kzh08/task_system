<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架的父控制器，所有控制器均需继承它				      	 	  	          |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

class XyController {
	// 视图实例对象
    protected $view		= null;

    // 类名
	private	$className	= null;
	
	/**
	 * 初始化
	 */
	public function __construct(){
		//取得当前子类名称
		$this->className	= str_replace("Controller", "", get_class($this));
	
		//实例化视图类
        $this->view       	= new XyView();

        //控制器初始化
        if(method_exists($this, '_initialize')){
            $this->_initialize();
        }
		
		$this->init();
	}
	
	/**
	 * 默认Action
	 */
	public function indexAction(){
		
	}

    /**
     * 模板变量赋值
     *
     * @param $name     string 模板中使用的变量名
     * @param $value    string 变量值
     */
	public function assign($name, $value = '') {
        $this->view->assign($name, $value);
    }

    /**
     * 显示页面
     *
     * @param string $template 模板路径/名称
     */
	public function display($template = ''){
        //调用预置方法
		$this->beforeDisplay();

		//获取当前继承类的类名和方法名
		$method		= str_replace("Action", "", $this->_method);

		if(empty($template)){
			$template	= $this->className."/".$method;
		}
		
		//调用内置的模板引擎显示方法
		$this->view->display($template);

        //调用预置方法
		$this->afterDisplay();
	}
	
	/**
	 * 显示Ajax请求的值
	 *
     * @param $message array|string 值的数组
	 */
	public function displayAjax($message){
		$this->beforeDisplay();

        if(is_array($message)){
            echo json_encode($message);
        }else{
            echo $message;
        }
		
		$this->afterDisplay();
	}
	
	/**
	 * 默认添加页面
	 */
	public function addAction(){
		$this->display();
	}
	
	/**
	 * 默认添加处理
	 */
	public function insertAction(){
		$data	= $_POST;
		$res	= s($this->className)->I($data);
        d($res);
	}
	
	/**
	 * 默认修改页面
	 */
	public function editAction(){
		$id	= $_REQUEST['id'];
		$vo	= s($this->className)->get($id);	
		
		$this->assign("vo", $vo);
		$this->display();
	}
	
	/**
	 * 默认修改处理
	 */
	public function updateAction(){
		$data	= $_POST;
		$id		= $data['id'];
		unset($data[$id]);
		
		$res	= s($this->className)->U($data, $id);
		d($res);
	}
	
	/**
	 * 默认删除处理
	 *
	 */
	public function deleteAction(){
		$id		= $_REQUEST['id'];
		$res	= s($this->className)->D($id);
		d($res); 
	}
	
	/**
	 * 控制器初始化
	 */
	public function init(){
		
	}
	
	/**
	 * 显示视图前
	 */
	public function beforeDisplay(){
		
	}
	
	/**
	 * 显示视图后
	 */
	public function afterDisplay(){
		
	}
	
	/**
	 * 魔术方法，设置参数值
	 */
	public function __set($key, $value)
	{
		if(!empty($key))
			$this->$key = $value;
	}
	
	/**
	 * 魔术方法，取得参数值
	 */
	public function __get($key)
	{
		return $this->$key;
	}

    /**
     * 魔术方法，当调用到不存在的方法时的处理
     *
     * @param $method   string 方法名或者对象方法数组  "bar", array("three", "four") || array($foo, "bar"), array("three", "four")
     * @param $params   string 参数数组
     * @throws MethodNotFoundException
     * @return boolean 失败返回false
     */
	public function __call($method, $params)
    {
        if($method == '_call_'){
            //用于获取该控制器的方法名
			$method 		= $params[0];
			$this->_method	= $method;
			$this->$method();  
		}elseif(method_exists($this,'notFoundAction')){
            //增加当存在notFoundAction时直接显示定义的错误
			$this->notFoundAction();
		}else{
			//当都没有定义时,根据开发模式进行不同的输出
			if($GLOBALS['X_G']["debug"]){
				require(X_PATH.'/exception/MethodNotFoundException.php');
				throw new MethodNotFoundException('相关方法不存在:'.$method);
			}else{
				//todo:需要定义类似404的友好页面，然后进行跳转
			}
		}
	}
	
	/**
	 * 魔术方法，打印一些类的相关属性以便查错
	 *
	 * @return string 返回类的相关属性
	 */
	public function __toString()
    {
        return 'controller';
	}
}