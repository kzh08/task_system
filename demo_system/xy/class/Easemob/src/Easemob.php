<?php

namespace Easemob;

use Easemob\AccessToken\AccessToken;
use Easemob\AccessToken\LocalStoreHandlerInterface;
use Easemob\AccessToken\LocalFileStoreHandler;

use Easemob\Exceptions\RequestException;

class Easemob
{
    private $appKey;

    private $clientID;

    private $clientSecret;

    private static $localAccessTokenStoreHandler;

    public function __construct($appKey, $clientID, $clientSecret)
    {
        $this->appKey       = $appKey;
        $this->clientID     = $clientID;
        $this->clientSecret = $clientSecret;
    }

    public static function setLocalAccessTokenStoreHandler(LocalStoreHandlerInterface $localAccessTokenStoreHandler)
    {
        static::$localAccessTokenStoreHandler = $localAccessTokenStoreHandler;
    }

    public static function getLocalAccessTokenStoreHandler()
    {
        if (!empty(static::$localAccessTokenStoreHandler)) {
            return static::$localAccessTokenStoreHandler;
        }

        return new LocalFileStoreHandler();
    }

    /**
     * 获取access token
     *
     * @author Sam Lu
     * @return string
     */
    public function getAccessTokenString()
    {
        // 先尝试获取本地存储的access token
        $localAccessTokenStoreHandler = self::getLocalAccessTokenStoreHandler();
        $accessToken = $localAccessTokenStoreHandler->get();
        // 如果找不到或已过期，重新从环信服务器获取并保存至本地
        if ((string) $accessToken == '') {
            $accessToken = AccessToken::renew($this->appKey, $this->clientID, $this->clientSecret);
            $localAccessTokenStoreHandler->update($accessToken);
        }

        return (string) $accessToken;
    }

    /**
     * 授权注册
     *
     * @author Sam Lu
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     * @throws Exceptions\RequestException
     */
    public function accreditedRegister($username, $password)
    {
        $params = array(
            'username' => $username,
            'password' => $password,
        );
        $request  = new Request($this->appKey, 'POST', 'users', $params, $this->getAccessTokenString());
        $response = $request->execute();
        if ($response->hasError() && $response->getError() != 'duplicate_unique_property_exists') {
            throw new RequestException($response->getErrorDescription(), $response->getError());
        }

        return $params;
    }

    /**
     * 批量授权注册
     *
     * @author Sam Lu
     *
     * @param  array $list 每个元素必须为包含username&password的数组
     *
     * @throws Exceptions\RequestException
     */
    public function batchAccreditedRegister(array $list)
    {
        $request  = new Request($this->appKey, 'POST', 'users', $list, $this->getAccessTokenString());
        $response = $request->execute();
        if ($response->hasError()) {
            throw new RequestException($response->getErrorDescription(), $response->getError());
        }
    }

    /**
     * 获取用户信息
     *
     * @author Sam Lu
     *
     * @param  string $username
     *
     * @return mixed
     * @throws Exceptions\RequestException
     */
    public function getUserInfo($username)
    {
        $request  = new Request($this->appKey, 'GET', 'users/' . $username, array(), $this->getAccessTokenString());
        $response = $request->execute();
        if ($response->hasError()) {
            throw new RequestException($response->getErrorDescription(), $response->getError());
        }

        return $response->getBody();
    }
}