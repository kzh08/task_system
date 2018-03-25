<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | Redis封装类										      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-03       |
   +----------------------------------------------------------------------+
*/

class XyRedis
{
    /**
     * Redis实例
     */
    private static $_connection;

    /**
     * 构造函数
     */
    private function __construct()
    {

    }

    public static function resetConnection($type, $connection){
        self::$_connection[$type] = $connection;
    }

    /**
     * 取得redis实例
     *
     * @param     $type        string          连接的配置
     * @param     $db_index    int             第几个实例，不建议使用！
     * @throws RedisAuthException
     * @throws RedisConnectionException
     * @return Redis        redis实例
     */
    public static function getRedis($type = 'redis', $db_index = -1)
    {
        $connection  = self::$_connection[$type];

        if (!$connection) {
            $redisConfig = $GLOBALS['X_G'][$type];

            $connection = new Redis();
            $result = $connection->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);

            if(!$result){
                require_once(X_PATH . '/exception/RedisConnectionException.php');
                throw new RedisConnectionException('redis无法连接！ ' . $redisConfig['host'] . ':' .  $redisConfig['port']);
            }

            //如果配置了密钥则进行密钥认证
            if ($redisConfig['auth'] != '') {
                $res = $connection->auth($redisConfig['auth']);
                if (!$res) {
                    require_once(X_PATH . '/exception/RedisAuthException.php');
                    throw new RedisAuthException('密钥认证不通过！');
                }
            }

            if($db_index != -1){
                $connection->select($db_index);
            }

            self::$_connection[$type] = new XyRedisProxy($connection, $type);
        }

        return self::$_connection[$type];
    }
}

class XyRedisProxy
{
    private $redisObject;

    private $type;

    public function __construct($redis, $type){
        $this->redisObject = $redis;
        $this->type        = $type;
    }

    public function __call($method, $params){
        $result = false;

        $reflectionMethod   = new ReflectionMethod('Redis', $method);

        for($i = 1; $i <= 3; $i++){
            $isException = false;

            try{
                $result             =  $reflectionMethod->invokeArgs($this->redisObject , $params);
            }catch (Exception $e){
                //以下为重试机制，当redis抛异常的时候，正常情况下redis会断连，所以在这里需要重建连接，同时为了后面的调用，必须更新缓存的连接。
                $redisConfig = $GLOBALS['X_G'][$this->type];

                if($i < 3){
                    LOG::n('retry redis ' . $i . ' times # ' . $method);
                }else{
                    LOG::e('redis exception ' . $e->getMessage() . ' # ' . $method);

                    require_once(X_PATH . '/exception/RedisExecException.php');
                    throw new RedisExecException('redis命令执行异常！' . $redisConfig['port'] . '-' . $method . '!');
                }

                $connection = new Redis();
                $result = $connection->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);

                XyRedis::resetConnection($this->type, $connection);

                $this->redisObject = $connection;

                $isException    = true;
            }

            if(!$isException){
                break;
            }
        }

        return $result;
    }
}