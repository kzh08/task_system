<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | TQCC核心类											      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-11-05       |
   +----------------------------------------------------------------------+
*/

class Cache
{
    /**
     * @var array 缓存配置
     */
    private $cacheConfig;

    /**
     * @var object redis对象
     */
    private $redis;

    /**
     * @var string 内容的hash键，带次要参数
     */
    private $contentHashKey;

    /**
     * @var string 内容的键，带主要参数
     */
    private $contentKey;


    /**
     * @var string 设置全局版本key，因为不公用等原因，不写到配置文件
     */
    private $versionKey = "tqcc_version";

    /**
     * 构造函数
     *
     * @param $cacheConfig array 缓存配置
     */
    public function __construct($cacheConfig){
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * 取得缓存的内容
     *
     * @return string 序列化的内容
     */
    public function getCacheContent(){
        //取得唯一的key值  uri_mainParam
        $hashKey = $this->getInterfaceHashKey();

        //取得版本号
        $version = $this->getVersion($hashKey);

        $this->getExistRedis();

        $this->contentKey     = $hashKey . '_' . $version;

        $this->contentHashKey = $this->getOtherHashKey();

        //缓存的值
        $cacheContent   = $this->redis->hGet($this->contentKey, $this->contentHashKey);

        //假如没有保存相关的缓存，则在此记录后，将重新生成缓存
        if($cacheContent == ""){
            $GLOBALS['X_G']['tqcc_version'] = $version;
        }

        return $cacheContent;
    }

    /**
     * 设置缓存的内容
     *
     * @param $content string 序列化的内容
     */
    public function setCacheContent($content){
        $this->redis->hSet($this->contentKey, $this->contentHashKey, $content);

        //过期时间随机设为20-28小时间，防止同时失效导致异常
        $ttl = rand(20*3600,28*3600);

        $this->redis->expire($this->contentKey, $ttl);
    }

    /**
     * 更新缓存版本
     *
     * @param $params array 参数数组，有顺序
     */
    public function updateCacheVersion($params){
        //取得唯一的key值  uri_mainParam
        $hashKey = $this->getServiceHashKey($params);

        //增加版本号
        $this->setVersion($hashKey);
    }

    public function extendUpdateCacheVersion($uri, $param){
        $config = c($uri);

        $params = array();

        if(empty($config['mainParam'])){
            $params[] = 'no_mainParam';
        }else{
            foreach($config['mainParam'] as $value){
                $params[] = $param[$value] ? $param[$value] : $param[$config['serviceParam'][$value]][$value];
            }
        }

        $this->updateCacheVersion($params);
    }

    /**
     * 取得当前key对应的redis对象
     *
     * @return object redis对象
     */
    private function getExistRedis(){
        $redisHash          = hash('sha256', $this->cacheConfig['uri']);

        $redisConfigList    = c("tqcc_redis");

        $redisNum           = count($redisConfigList);

        $catchRedis         = $redisHash % $redisNum;

        $this->redis        = r($redisConfigList[$catchRedis]);
    }

    /**
     * 取得相关缓存所处于的版本号
     *
     * @param $hashKey string hash key
     * @return int 版本号
     */
    private function getVersion($hashKey){
        //取得版本库redis
        $redis      = r("tqcc_version_redis");

        $version    = $redis->hGet($this->versionKey, $hashKey);

        $version    = $version ? $version : 0;

        return $version;
    }

    /**
     * 取得相关缓存所处于的版本号
     *
     * @param $hashKey string hash key
     * @return int 版本号
     */
    private function setVersion($hashKey){
        //取得版本库redis
        $redis      = r("tqcc_version_redis");

        $version    = $redis->hIncrBy($this->versionKey, $hashKey, 1);

        return $version;
    }

    /**
     * 取得相关的hash key，带主要参数
     *
     * @return string
     */
    private function getInterfaceHashKey(){
        //设置版本存放所用的key
        $key = $this->cacheConfig['uri'];

        if(!empty($this->cacheConfig['mainParam'])){
            foreach($this->cacheConfig['mainParam'] as $value){
                $key .= '_' . $_REQUEST[$value];
            }
        }

        return $key;
    }

    /**
     * 取得相关的hash key，带主要参数
     *
     * @param $params array 主要参数。默认为request带来的参数
     * @return string
     */
    private function getServiceHashKey($params = array()){
        //设置版本存放所用的key
        $key = $this->cacheConfig['uri'];

        if(!empty($params)){
            foreach($params as $value){
                $key .= '_' . $value;
            }
        }

        return $key;
    }

    /**
     * 取得相关的hash key，带次要参数
     *
     * @return string
     */
    private function getOtherHashKey(){
        $hashKey = 'cache';

        //将主要参数加入版本控制
        if(!empty($this->cacheConfig['otherParam'])){
            foreach($this->cacheConfig['otherParam'] as $value){
                $value      = $value ? $value : 'null';

                $hashKey .= '_' . $_REQUEST[$value];
            }
        }

        return $hashKey;
    }
}