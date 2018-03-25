<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | soa相关的客户端       								      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-10-28       |
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
     * @param $retryNum     int         重试次数
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
    public static function getSoa($server, $service, $retryNum = 3)
    {
        if(!empty(self::$clientList[$server][$service])){
            return self::$clientList[$server][$service];
        }

        //soa访问的地址
        $baseUrl    = '';

        $config     = $GLOBALS['X_G']['soa_client'][$server];

        //如果有多个soa节点则进行随机负载
        if(is_string($config)){
            $baseUrl = $config;
        }elseif(is_array($config)){
            $len     = count($config);
            $rand    = rand(0, $len - 1);
            $baseUrl = $config[$rand];
        }

        //选择协议
        if($GLOBALS['X_G']['soa_protocol'][$server] == 'json'){
            self::$clientList[$server][$service] = new JsonProtocol($baseUrl, $service, $retryNum);
        }else{
            self::$clientList[$server][$service] = new Yar($baseUrl, $service, $retryNum);
        }

        return self::$clientList[$server][$service];
    }
}

interface SoaService{
    public function hasError();
    public function flushError();
    public function getErrorMsg();
    public function getErrorCode();
    public function getError();
}

class Yar implements SoaService
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
     * @var array  错误信息
     */
    private $error;

    /**
     * @var array  重试次数
     */
    private $retryNum;

    /**
     * 构造函数
     *
     * @param $baseUrl      string  基本的url
     * @param $service      string  调用的服务
     * @param $retryNum     int     重试次数
     */
    public function __construct($baseUrl, $service, $retryNum){
        $this->service  = $service;

        if(!is_int($retryNum) || $retryNum < 0){
            $retryNum = 3;
        }elseif($retryNum > 10){
            $retryNum = 10;
        }

        $this->retryNum     = $retryNum;

        $this->url          = $baseUrl . 'yarService';

        $this->yarClient    = new Yar_Client($this->url . '?distinctRequestId=' . $GLOBALS['X_G']['soa']['bugFinderDistinct'] . '&soa_basic=' . base64_encode(serialize(b())));

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
        $result         = '';
        $this->error    = null;

        for($i = 1; $i <= $this->retryNum; $i++){
            $isException = false;

            try{
                $result = $this->yarClient->xySoaMethod($this->service, $method, $params);
            }catch (Exception $e){
                if($i < $this->retryNum){
                    LOG::n('retry soa ' . $i . ' times # ' . $this->service . ' # ' . $method . ' # ' . $this->url);
                }else{
                    LOG::e('soa exception ' . $e->getMessage() . ' # ' . $this->service . ' # ' . $method . ' # ' . ' ,url: ' . $this->url);

                    $this->error = array(
                        'code'  => 1013,
                        'msg'   => "soa调用失败",
                    );

                    return $this->error;
                }

                $isException = true;
            }

            if(!$isException){
                break;
            }
        }

        //假如出现错误则进行内部记录
        if(is_array($result)){
            if(!empty($result['code']) && $result['code'] != 'success'){
                $this->error = array(
                    'code'  => $result['code'],
                    'msg'   => $result['msg'],
                );
            }
        }

        return $result;
    }

    public function hasError()
    {
        return !empty($this->error);
    }

    public function flushError()
    {
        $this->error = null;
    }

    public function getErrorMsg()
    {
        return $this->error['msg'];
    }

    public function getErrorCode()
    {
        return $this->error['code'];
    }

    public function getError()
    {
        return $this->error;
    }
}

class JsonProtocol implements SoaService
{
    /**
     * @var string url地址
     */
    private $url;

    /**
     * @var array  错误信息
     */
    private $error;

    /**
     * @var array  重试次数
     */
    private $retryNum;

    /**
     * 构造函数
     *
     * @param $baseUrl      string  基本的url
     * @param $service      string  调用的服务
     * @param $retryNum     int     重试次数
     */
    public function __construct($baseUrl, $service, $retryNum){
        if(!is_int($retryNum) || $retryNum < 0){
            $retryNum = 3;
        }elseif($retryNum > 10){
            $retryNum = 10;
        }

        $this->retryNum     = $retryNum;

        $this->url    = $baseUrl . 'service' . '?service=' . $service . '&distinctRequestId=' . $GLOBALS['X_G']['soa']['bugFinderDistinct'] . '&soa_basic=' . base64_encode(serialize(b()));
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
        $result         = '';
        $this->error    = null;

        for($i = 1; $i <= $this->retryNum; $i++){
            $isException = false;

            try{
                $result = $this->request_post($this->url . '&method=' . $method, 'form=' . json_encode($params));
            }catch (Exception $e){
                if($i < $this->retryNum){
                    LOG::n('retry soa-json ' . $i . ' times # ' . ' # ' . $method . ' # ' . $this->url);
                }else{
                    LOG::e('soa-json exception ' . $e->getMessage() . ' # ' . $method . ' # ' . ' ,url: ' . $this->url);

                    $this->error = array(
                        'code'  => 1013,
                        'msg'   => "soa调用失败",
                    );

                    return $this->error;
                }

                $isException = true;
            }

            if(!$isException){
                break;
            }
        }


        //假如出现错误则进行内部记录
        if(is_array($result)){
            if(!empty($result['code']) && $result['code'] != 'success'){
                $this->error = array(
                    'code'  => $result['code'],
                    'msg'   => $result['msg'],
                );
            }
        }

        return $result;
    }

    /**
     * @param string $url   url地址
     * @param string $param 要提交的表单内容
     *
     * @return bool|mixed
     */
    private function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

        $data = curl_exec($ch);
        curl_close($ch);

        list($header, $body) = explode("\r\n\r\n", $data, 2);

        $data = json_decode($body, true);

        return $data;
    }

    public function hasError()
    {
        return !empty($this->error);
    }

    public function flushError()
    {
        $this->error = null;
    }

    public function getErrorMsg()
    {
        return $this->error['msg'];
    }

    public function getErrorCode()
    {
        return $this->error['code'];
    }

    public function getError()
    {
        return $this->error;
    }
}

class YarIpNotAllow
{
    public function xySoaMethod(){
        return array(
            'code' => 1012,
            'msg'  => 'ip is no allow!'
        );
    }
}