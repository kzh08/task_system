<?php

namespace Easemob;

use Easemob\Exceptions\CURLException;

class CURLHTTPClient
{
    protected $requestHeaders = array();

    protected $curlErrorCode = 0;

    protected $curlErrorMsg = '';

    protected $responseHTTPStatusCode = 0;

    protected $responseBody;

    protected $curl;

    protected static $disableIPv6 = false;

    public function __construct($curl = null)
    {
        $this->curl = !empty($curl) ? $curl : new CURL();
    }

    public static function disableIPv6()
    {
        self::$disableIPv6 = true;
    }

    public function addRequestHeader($field, $value)
    {
        $this->requestHeaders[$field] = $value;
    }

    public function send($url, $method = 'GET', $params = array())
    {
        $this->openConnection($url, $method, $params);
        $this->sendRequest();
        $this->curlErrorCode = $this->curl->getErrorCode();
        $this->curlErrorMsg  = $this->curl->getErrorMsg();
        $this->responseHTTPStatusCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);
        if ($this->curlErrorCode) {
            throw new CURLException($this->curlErrorMsg, $this->curlErrorCode);
        }

        $this->closeConnection();
    }

    private function openConnection($url, $method = 'GET', $params = array())
    {
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_RETURNTRANSFER => true,
        );

        if ($method !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = $params;
        }
        if ($method === 'DELETE' || $method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }
        if (!empty($this->requestHeaders)) {
            $options[CURLOPT_HTTPHEADER] = $this->compileRequestHeaders();
        }
        if (self::$disableIPv6) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }

        $this->curl->init();
        $this->curl->batchSetOptions($options);
    }

    private function closeConnection()
    {
        $this->curl->close();
    }

    private function sendRequest()
    {
        $this->responseBody = $this->curl->exec();
    }

    private function compileRequestHeaders()
    {
        $return = array();
        foreach ($this->requestHeaders as $field => $value) {
            $return[] = $field . ': ' . $value;
        }

        return $return;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getResponseHTTPStatusCode()
    {
        return $this->responseHTTPStatusCode;
    }
}