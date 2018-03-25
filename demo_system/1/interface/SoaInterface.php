<?php

/*
   +----------------------------------------------------------------------+
   |                  		     xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | soa相关的接口										      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-11-11       |
   +----------------------------------------------------------------------+
*/

class SoaInterface
{
    /**
     * 返回yar的文档，用于get请求
     *
     * param object $class 对象
     */
    public function yar(){
        $this->checkIp();

        $serviceName = ucfirst(getParam("s")) . "Service";

        in(VERSION_PATH . "/service/" . $serviceName . ".php");

        $service = new Yar_Server(new $serviceName());

        $service->handle();
    }

    /**
     * 返回yar相关操作，用于post请求
     *
     * param object $class 对象
     */
    public function yarService(){
        $this->checkIp();

        $service = new Yar_Server(new SoaInterface());

        $service->handle();
    }

    /**
     * 远程soa调用的的方法
     *
     * @param $service string 调用的服务
     * @param $method  string 调用的方法
     * @param $param   array  调用的参数
     *
     * @return mixed 相关方法的返回值
     */
    public function xySoaMethod($service, $method, $param){
        LOG::i($service . ' # ' . $method . ' # ' . b('token') . ' # ' . b('ip'));

        $serviceClass       = ucfirst($service . 'Service');

        in(VERSION_PATH . "/service/" . $serviceClass . ".php");

        $serviceObject      = new $serviceClass();

        $reflectionMethod   = new ReflectionMethod($serviceClass, $method);

        return $reflectionMethod->invokeArgs($serviceObject, $param);
    }

    /**
     * 检测安全性
     */
    private function checkIp(){
        $ip         = GetIP();
        $eIpArray   = explode('.', $ip);

        if(!in_array($ip, $GLOBALS['X_G']['soa_auth_ip']) &&
            !in_array($eIpArray[0] . '.*.*.*', $GLOBALS['X_G']['soa_auth_ip']) &&
            !in_array($eIpArray[0] . '.' . $eIpArray[1] . '.*.*', $GLOBALS['X_G']['soa_auth_ip']) &&
            !in_array($eIpArray[0] . '.' . $eIpArray[1] . '.' . $eIpArray[2] . '.*', $GLOBALS['X_G']['soa_auth_ip'])){

            LOG::w("soa ip is not allow!");

            $service = new Yar_Server(new YarIpNotAllow());

            $service->handle();

            exit();
        }
    }
}