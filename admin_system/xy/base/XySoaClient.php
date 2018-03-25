<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | soa相关的客户端       								      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2015-02-02       |
   +----------------------------------------------------------------------+
*/

class SoaClient
{
    private static $clientList = array();

    /**
     * 获取相关yar客户端
     *
     * @param $server       string      要调用的SOA服务端名称
     * @param $service      string      要调用的SOA服务
     * @return object      相关yar客户端
     * 
     * soa_config配置需要增加相关地址
     *   'soa_client' => array(
     *        'tqco' => 'http://192.168.20.205:1009/tqco/v1/Soa/"
     *   )
     * 
     *  调用方法为
     *  $soa    = SoaClient::getSoa('bbs', 'postInfo');
     *  $result = $soa->getAndSet($appcode, $platform, $token);
     *  
     */
    public static function getSoa($server, $service)
    {
        if(self::$clientList[$server][$service] != null){
            return self::$clientList[$server][$service];
        }

        $baseUrl = $GLOBALS['X_G']['soa_client'][$server];

        self::$clientList[$server][$service] = new Yar($baseUrl, $service);

        return self::$clientList[$server][$service];
    }
}

class Yar
{
    /**
     * @var object yar对象
     */
    private $yarClient;

    /**
     * @var string 服务名称
     */
    private $service;

    /**
     * @var string url地址
     */
    private $url;

    /**
     * 构造函数
     *
     * @param $baseUrl string 基本的url
     * @param $service string 调用的服务
     */
    public function __construct($baseUrl, $service){
        $this->service = $service;

        $this->url         = $baseUrl . 'yarService';
        $bugFinderDistinct = substr(md5($_REQUEST['xycontroller'] . $_REQUEST['xyaction'] . time()), 0, 16) . rand(0, 99999999);
        $basicParam        = array(
            'token' => c('website.projectEnName'),
            'ip'    => getIp(),
        );
        $this->yarClient = new Yar_Client($this->url . '?distinctRequestId=' . $bugFinderDistinct . '&soa_basic=' . base64_encode(serialize($basicParam)));

        $this->yarClient->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 2000);
    }

    /**
     * 魔术方法，用于远程方法调用
     *
     * @param $method string 调用的方法名
     * @param $params array  调用的参数
     *
     * @return mixed 远程方法返回的值
     */
    public function __call($method, $params){

        $result = '';

        for($i = 1; $i <= 3; $i++){
            $isException = false;

            try{
                $result = $this->yarClient->xySoaMethod($this->service, $method, $params);
            }catch (Exception $e){
                if($i < 3){
                    LOG::n('retry soa ' . $i . ' times # ' . $this->service . ' # ' . $method . ' # ' . $this->url);
                }else{
                    LOG::e('soa exception ' . $e->getMessage() . ' # ' . $this->service . ' # ' . $method . ' # ' . ' ,url: ' . $this->url);
                }

                $isException = true;
            }

            if(!$isException){
                break;
            }
        }

        return $result;
    }
}