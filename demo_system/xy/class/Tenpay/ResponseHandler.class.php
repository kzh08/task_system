<?php

/**
 * 即时到帐支付应答类
 * 源自demo
 *
 * @modified by Sam Lu 2014-11-5
 */
class ResponseHandler
{
    // 财付通密钥
    private $partnerKey;

    // 应答的参数
    private $params = array();

    private $extraParams = array();

    private $debugInfo = array();

    public function __construct($partner_key)
    {
        $this->partnerKey = $partner_key;
        /* GET */
        foreach ($_GET as $k => $v) {
            $this->params[$k] = $v;
        }
        $this->setDebugInfo('GET', $_GET);

        /* POST */
        $post_data = file_get_contents("php://input");
        $this->setDebugInfo('POST', $post_data);
        $xml = simplexml_load_string($post_data);
        foreach ($xml->children() as $child) {
            $this->extraParams[$child->getName()] = (string) $child;
        }

    }

    public function getParam($param)
    {
        return $this->params[$param];
    }

    public function getExtraParam($param)
    {
        return $this->extraParams[$param];
    }

    public function getAllParams()
    {
        return $this->params;
    }

    /**
     * 验证财付通签名,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     */
    public function validateSign()
    {
        $sign = $this->createMd5Sign($this->params);
        $tenpay_sign = strtoupper($this->getParam("sign"));

        $debug_info = array(
            'tenpay_sign' => $tenpay_sign,
            'sign'        => $sign
        );
        $this->setDebugInfo('validateSign', $debug_info);

        return $sign == $tenpay_sign;
    }

    /**
     * 生成md5签名
     */
    public function createMd5Sign($params)
    {
        $sign_params_str = '';

        ksort($params);
        foreach ($params as $k => $v) {
            if ($v != "" && 'sign' != $k) {
                $sign_params_str .= $k . '=' . $v . '&';
            }
        }
        $sign_params_str .= 'key=' . $this->partnerKey;
        $sign = strtoupper(md5($sign_params_str)); // md5运算后，字符串的字符要转换为大写

        $debug_info = array(
            'params'     => $this->params,
            'params_str' => $sign_params_str,
            'sign'       => $sign
        );
        $this->setDebugInfo('createMd5Sign', $debug_info);

        return $sign;
    }

    private function setDebugInfo($action, $debug_info)
    {
        $this->debugInfo[] = array(
            'action'    => $action,
            'time'      => date('Y-m-d-H:i:s'),
            'info'      => $debug_info
        );
    }

    public function getDebugInfo()
    {
        return $this->debugInfo;
    }
}