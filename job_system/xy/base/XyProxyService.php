<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | serivce代理类     									      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-11-05       |
   +----------------------------------------------------------------------+
*/

class ProxyService
{
    private $serviceClass;

    private $serviceObject;

    public function __construct($class, $params){
        $this->serviceClass  = $class;

        $this->serviceObject = new $class($params);
    }

    public function __get($property_name){
        return $this->serviceObject->$property_name;
    }

    public function __set($property_name, $property_value){
        $this->serviceObject->$property_name = $property_value;
    }

    public function __call($method, $params){
        $reflectionMethod       = new ReflectionMethod($this->serviceClass, $method);

        $paramsName             = $reflectionMethod->getParameters();

        //TQCC注入service中
        $this->isCacheChange($this->serviceClass, $method, $paramsName, $params);

        return $reflectionMethod->invokeArgs($this->serviceObject, $params);
    }

    /**
     * 转化参数为有key的方式
     *
     * @param $paramsName       array  参数名称
     * @param $params           array  参数值
     * @return array 参数对应值
     */
    private function paramChange($paramsName, $params){
        $paramsArray = array();

        foreach($paramsName as $key => $value){
            $paramsArray[$value->name] = $params[$key];
        }

        return $paramsArray;
    }

    /**
     * 判断是否需要进行版本变更
     *
     * @param $serviceName      string 服务名称
     * @param $serviceMethod    string 服务方法
     * @param $paramsName       array  参数名称
     * @param $params           array  参数值
     * @throws TQCCMainParamNotExistException 主要参数未定义异常
     */
    private function isCacheChange($serviceName, $serviceMethod, $paramsName, $params){

        //取得会影响的配置文件
        $cacheLostConfig = c($serviceName . '_' . $serviceMethod);

        if(!empty($cacheLostConfig)){

            //转化参数
            $params      = $this->paramChange($paramsName, $params);

            foreach($cacheLostConfig as $value){
                //主要参数
                $mainParams  = array();

                //取得相关配置文件
                $cacheConfig     = c($value['uri']);

                if(!empty($cacheConfig['mainParam'])){
                    //判断是否传了主要参数，如果没传则抛出异常
                    foreach($cacheConfig['mainParam'] as $paramName){
                        if($params[$paramName] == "" && $params[$cacheConfig['serviceParam'][$paramName]] == ""){
                            require(X_PATH . '/exception/TQCCMainParamNotExistException.php');
                            throw new TQCCMainParamNotExistException('service主要参数未传或者TQCC配置异常！');
                        }

                        if($params[$cacheConfig['serviceParam'][$paramName]] != ""){
                            $mainParams[$paramName] = $params[$cacheConfig['serviceParam'][$paramName]][$paramName];
                        }else{
                            $mainParams[$paramName] = $params[$paramName];
                        }
                    }
                }else{
                    $mainParams = array(
                        'no_mainParam'
                    );
                }

                $cacheConfig['uri'] = $value['uri'];

                $cache = new Cache($cacheConfig);

                $cache->updateCacheVersion($mainParams);

                if($value['method'] != ''){

                    $cacheConfig['uri'] = $value['uri'];

                    $cache = new Cache($cacheConfig);

                    $this->serviceObject->$value['method']($cache, $mainParams);
                }
            }
        }
    }
}