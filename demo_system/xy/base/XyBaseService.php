<?php

class BaseService
{
    /**
     * @var object db的空实例
     */
    protected $db;

    /**
     * @var array 错误信息，储存code和msg
     */
    protected $error;

    public function __construct()
    {
        
    }

    public function setError($error_code = '', $error_msg = '')
    {
        if($error_msg == ''){
            $error_msg  = l($error_code);
        }

        $this->error = array(
            'code' => $error_code,
            'msg'  => $error_msg
        );

        return $this->error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getErrorCode()
    {
        return $this->error['code'];
    }

    public function getErrorMsg()
    {
        return $this->error['msg'];
    }

    /**
     * 判断是否有错误
     *
     * @author Sam Lu
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 清空错误信息
     *
     * @author Sam Lu
     * @return bool
     */
    public function flushError()
    {
        $this->error = null;
    }

    /**
     * 判断应用是否为审核版本
     * 平台+渠道+版本号+应用别名
     * 若Redis里有记录，则为审核版本
     *
     * @author Sam Lu
     * @return bool
     */
    public function isAppExamined()
    {
        // 先将应用版本号（数字）转换为版本号
        $app_version_str = $this->generateAppVersionStr(b('app_version'));
        $redis_key = redis_key('app_examine', b('platform_id'), b('channel'), $app_version_str, b('alias'));
        $cache = XyRedis::getRedis('common_redis');
        $cache->select(c('common_redis.databases.config'));
        $is_app_examined = $cache->get($redis_key);

        return $is_app_examined ? true : false;
    }

    /**
     * 由数字版本号生成带「.」号的版本号
     *
     * @author Sam Lu
     * @param $app_version
     * @return string
     */
    protected function generateAppVersionStr($app_version)
    {
        $app_version = (string) $app_version;
        $app_version_str = '';
        $length = strlen($app_version);
        for ($i = 0; $i < $length; $i++) {
            $app_version_str .= '.' . $app_version[$i];
        }

        return trim($app_version_str, '.');
    }
}
