<?php

/**
 * 微信支付请求类
 * 源自demo
 *
 * @modified by Sam Lu 2014-11-5
 */
class RequestHandler
{
    /**
     * @var string accessToken获取网关地址
     */
    private $tokenUrl;

    /**
     * @var string 预支付网关url地址
     */
    private $gateUrl;

    /** 商户参数 */
    private $appId;
    private $partnerKey;
    private $appSecret;
    private $appKey;

    private $accessToken;

    private $debugInfo = array();

    public function __construct($app_id, $app_secret, $partner_key, $app_key)
    {
        $this->tokenUrl    = 'https://api.weixin.qq.com/cgi-bin/token';
        $this->gateUrl     = 'https://api.weixin.qq.com/pay/genprepay';
        $this->appId       = $app_id;
        $this->appSecret   = $app_secret;
        $this->partnerKey  = $partner_key;
        $this->appKey      = $app_key;
        $this->setDebugInfo('GET', $_GET);
        $this->setDebugInfo('POST', $_POST);
    }

    /**
     * 发送HTTP请求
     *
     * @param $url
     * @param $method
     * @param $data
     *
     * @return string
     */
    public function httpSend($url, $method, $data)
    {
        $client = new TenpayHttpClient();
        $client->setReqContent($url);
        $client->setMethod($method);
        $client->setReqBody($data);
        $response = '';
        if ($client->call()) {
            $response = $client->getResContent();
        }

        $debug_info = array(
            'request_url'  => $url,
            'request_data' => $data,
            'response'     => $response
        );
        $this->setDebugInfo('httpSend', $debug_info);

        return $response;
    }

    /**
     * 设置access_token
     *
     * @author Sam Lu
     *
     * @param $access_token
     *
     * @return mixed
     */
    public function setAccessToken($access_token)
    {
        return $this->accessToken = $access_token;
    }

    /**
     * 获取access_token，一天最多获取200次
     *
     * @return string
     */
    public function getAccessToken()
    {
        $url = sprintf(
            "%s?grant_type=client_credential&appid=%s&secret=%s",
            $this->tokenUrl,
            $this->appId,
            $this->appSecret
        );
        $response_json = $this->httpSend($url, 'GET', '');
        if (!empty($response_json)) {
            $response = json_decode($response_json, true);
            if (!empty($response['access_token'])) {
                $this->accessToken = $response['access_token'];
            } else {
                $this->accessToken = '';
            }
        }

        return $this->accessToken;
    }

    /**
     * 生成md5签名
     * 主要用于创建package签名
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
        $sign = strtoupper(md5($sign_params_str)); // md5 运算后，字符串的字符要转换为大写

        $debug_info = array(
            'params_str' => $sign_params_str,
            'sign'       => $sign
        );
        $this->setDebugInfo('createMd5Sign', $debug_info);

        return $sign;
    }

    /**
     * 生成订单详情（package）扩展字符串
     * 例： 'bank_type=WX&body=%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81%E5%90%8D%E7%A7%B0&fee_type=1&input_charset=UTF-8&notify_url=http%3A%2F%2Flocalhost%2Fphp%2Fnotify_url.php&out_reade_no=S222333555&partner=1900000109&total_fee=100&spbill_create_ip=192.168.50.11&sign=F59BC2BE8D1FC0A498E84C1CFA2AE4E8'
     *
     * @param $package_params
     *
     * @return string
     */
    public function genPackage($package_params)
    {
        $sign = $this->createMd5Sign($package_params);
        $package = '';
        foreach ($package_params as $k => $v) {
            $package .= $k . '=' . rawurlencode($v) . '&'; // 进行urlencode时要将空格转化为%20而不是+
        }
        $package = $package . 'sign=' . $sign;

        $debug_info = array(
            'sign'    => $sign,
            'package' => $package
        );
        $this->setDebugInfo('genPackage', $debug_info);

        return $package;
    }

    /**
     * 生成SHA1签名
     * a. 对所有待签名参数按照字段名的 ASCII码从小到大排序（字典序）后，使用 URL 键值对的格式
     *  （即key1=value1&key2=value2…）拼接成字符串
     * b. 对拼接成的字符串作sha1处理
     * 主要用于：1. 支付签名（app_signature）； 2. 返回客户端的数据里的签名
     *
     * @param $params
     *
     * @return string
     */
    public function createSHA1Sign($params)
    {
        $sign_params_str = '';
        ksort($params);
        foreach ($params as $k => $v) {
            if ($sign_params_str == '') {
                $sign_params_str = $sign_params_str . $k . '=' . $v;
            } else {
                $sign_params_str = $sign_params_str . '&' . $k . '=' . $v;
            }
        }
        $sign = sha1($sign_params_str);

        $debug_info = array(
            'params_str' => $sign_params_str,
            'sign'       => $sign
        );
        $this->setDebugInfo('createSHA1Sign', $debug_info);

        return $sign;
    }

    /**
     * 提交预支付
     *
     * @param $request_params
     *
     * @return string | null
     */
    public function submitPrepay($request_params)
    {
        $prepay_id = null;

        $post_data = json_encode($request_params);
        $url = $this->gateUrl . '?access_token=' . $this->accessToken;
        $response_json = $this->httpSend($url, 'POST', $post_data);
        $response = json_decode($response_json, true);
        if ($response['errcode'] == 0) {
            $prepay_id = $response['prepayid'];
        }

        return $prepay_id;
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