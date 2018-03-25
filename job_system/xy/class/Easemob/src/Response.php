<?php

namespace Easemob;

class Response
{
    private $request;

    private $rawResponseBody;

    private $decodedResponseBody;

    private $responseHTTPStatusCode;

    public function __construct($request, $rawResponseBody, $responseHTTPStatusCode)
    {
        $this->request = $request;
        $this->rawResponseBody     = $rawResponseBody;
        $this->decodedResponseBody = json_decode($rawResponseBody, true);
        $this->responseHTTPStatusCode = $responseHTTPStatusCode;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getHTTPStatusCode()
    {
        return $this->responseHTTPStatusCode;
    }

    public function getBody()
    {
        return $this->decodedResponseBody;
    }

    public function getRawBody()
    {
        return $this->rawResponseBody;
    }

    public function __get($field)
    {
        return isset($this->decodedResponseBody[$field]) ? $this->decodedResponseBody[$field] : null;
    }

    public function hasError()
    {
        return isset($this->decodedResponseBody['error']);
    }

    public function getError()
    {
        return isset($this->decodedResponseBody['error']) ? $this->decodedResponseBody['error'] : '';
    }

    public function getErrorDescription()
    {
        return isset($this->decodedResponseBody['error_description']) ? $this->decodedResponseBody['error_description'] : '';
    }
}