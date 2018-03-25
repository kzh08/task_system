<?php

namespace Easemob\AccessToken;

use Easemob\Request;

class AccessToken
{
    private $accessTokenString;

    private $expireAt;

    public function __construct($accessTokenString, $expireAt)
    {
        $this->accessTokenString = $accessTokenString;
        $this->expireAt          = $expireAt;
    }

    public static function renew($appKey, $clientID, $clientSecret)
    {
        $params = array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientID,
            'client_secret' => $clientSecret,
        );
        $request     = new Request($appKey, 'POST', 'token', $params);
        $response    = $request->execute();
        $accessToken = new self($response->access_token, $response->expires_in + time());

        return $accessToken;
    }

    public function getString()
    {
        if ($this->expireAt < time()) {
            return '';
        }

        return (string) $this->accessTokenString;
    }

    public function __toString()
    {
        return $this->getString();
    }
}