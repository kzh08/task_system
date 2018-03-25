<?php
namespace Easemob;

class CURL
{
    protected $curl = null;

    public function init()
    {
        if ($this->curl === null) {
            $this->curl = curl_init();
        }
    }

    public function setOption($field, $value)
    {
        curl_setopt($this->curl, $field, $value);
    }

    public function batchSetOptions(array $options)
    {
        curl_setopt_array($this->curl, $options);
    }

    public function exec()
    {
        return curl_exec($this->curl);
    }

    public function getErrorCode()
    {
        return curl_errno($this->curl);
    }

    public function getErrorMsg()
    {
        return curl_error($this->curl);
    }

    public function getError()
    {
        return array(
            'code' => $this->getErrorCode(),
            'msg'  => $this->getErrorMsg(),
        );
    }

    public function getInfo($type)
    {
        return curl_getinfo($this->curl, $type);
    }

    public function version()
    {
        return curl_version();
    }

    public function close()
    {
        curl_close($this->curl);
        $this->curl = null;
    }
}