<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | xy框架的视图输出类									      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

class XyView {
    // 模板输出变量
	protected $templateVar		=  array();

    /**
     * 模板传值
     *
     * @param $valueName string|object 模板中使用的变量名
     * @param $value     string       变量值
     */
	public function assign($valueName, $value){

		if(is_array($valueName)) {
            $this->templateVar   =  array_merge($this->templateVar, $valueName);
        }elseif(is_object($valueName)){
            foreach($valueName as $key =>$val){
                $this->templateVar[$key] = $val;
            }
        }else {
            $this->templateVar[$valueName] = $value;
        }
    }

    /**
     * 模板显示
     *
     * @param $template string 模板名称
     */
	public function display($template){
		//模板阵列变量分解成为独立变量
        extract($this->templateVar, EXTR_OVERWRITE);
		
		//包含模板文件
		include($this->show($template));
	}

    /**
     * 输出内容文本
     *
     * @param $template string 模板文件名
     * @throws FileNoFoundException 模板不存在异常
     * @return string
     */
	public function show($template) {
		$from = 'template/'.$template.'.html';

		if(!file_exists($from)){
            require(X_PATH.'/exception/FileNoFoundException.php');

            //抛出无法找到文件的异常
            throw new FileNoFoundException('模板文件'.$from.'不存在!');
		}

        $to 	= 'template_c/'.$template.'.php' ;

        $dir	= dirname($to);

        if(!file_exists($dir)){
            mkDirs($dir);
        }
		
		// 页面缓存
        ob_start();
        ob_implicit_flush(0);
		
		$this->template_compile($from, $to);
		return $to;
	}

    /**
     * 模板编译
     *
     * @param $from string 需要编译的静态文件
     * @param $to   string 输出的php文件
     */
	public function template_compile($from, $to) {
		$content = template_parse(file_get_contents($from));
		file_put_contents($to, $content);
	}
}