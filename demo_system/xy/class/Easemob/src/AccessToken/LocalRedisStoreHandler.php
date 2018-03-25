<?php

namespace Easemob\AccessToken;

class LocalRedisStoreHandler implements LocalStoreHandlerInterface
{
    private $redis;

    private $key;

    public function __construct($redis, $key)
    {
        $this->redis = $redis;
        $this->key   = $key;
    }

    /**
     * get access token object stored in redis
     *
     * @author Sam Lu
     * @return null | AccessToken
     */
    public function get()
    {
        $accessToken = $this->redis->get($this->key);
        if (empty($accessToken)) {
            return null;
        }

        return unserialize($accessToken);
    }

    /**
     * update access token stored in redis
     *
     * @author Sam Lu
     *
     * @param  AccessToken $accessToken
     *
     * @return mixed
     */
    public function update(AccessToken $accessToken)
    {
        return $this->redis->set($this->key, serialize($accessToken));
    }
}