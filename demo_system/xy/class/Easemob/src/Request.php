<?php

namespace Easemob;

class Request
{
    const BASE_URL = 'https://a1.easemob.com';

    private $appKey = '';

    private $method = 'GET';

    private $params = array();

    private $accessToken = '';

    private static $httpClient;

    public function __construct(
        $appKey,
        $method,
        $path,
        $params = array(),
        $accessToken = ''
    )
    {
        $this->appKey      = $appKey;
        $this->method      = strtoupper($method);
        $this->path        = $path;
        $this->params      = $params;
        $this->accessToken = $accessToken;
    }

    public static function getHTTPClient()
    {
        if (!empty(static::$httpClient)) {
            return static::$httpClient;
        }

        return new CURLHTTPClient();
    }

    private function getURL()
    {
        return self::BASE_URL . '/' . str_replace('#', '/', $this->appKey) . '/' . $this->path;
    }

    public function execute()
    {
        $url    = $this->getURL();
        $params = $this->params;

        if ($this->method == 'GET') {
            $url    = self::appendParamsToURL($url, $params);
            $params = array();
        }

        $httpClient = self::getHTTPClient();
        $httpClient->addRequestHeader('Content-Type', 'application/json');
        // some requests may not require access token
        if (!empty($this->accessToken)) {
            $httpClient->addRequestHeader('Authorization', 'Bearer ' . $this->accessToken);
        }

        $httpClient->send($url, $this->method, json_encode($params));

        return new Response($this, $httpClient->getResponseBody(), $httpClient->getResponseHTTPStatusCode());
    }

    public static function appendParamsToURL($url, array $params = array())
    {
        if (empty($params)) {
            return $url;
        }

        if (strpos($url, '?') === false) {
            $path = $url;
        } else {
            list($path, $query_string) = explode('?', $url, 2);
            parse_str($query_string, $query_array);
            // favor params from the original URL over $params
            $params = array($params, $query_array);
        }

        return $path . '?' . http_build_query($params, null, '&');
    }
}