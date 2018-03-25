<?php
/**
 * 个推推送服务
 */

require(__DIR__ . '/IGt.Push.php');
require(__DIR__ . '/GetuiPushTemplate.php');

class GetuiPush {

    const PUSH_URL                   = 'http://sdk.open.api.igexin.com/apiex.htm';
    const PUSH_RESULT_URL            = 'http://sdk.open.api.igexin.com/api.htm';
    const USER_TAG_LIMIT             = 100;  // 每个用户最多可设置多少个标签
    const PUSH_USER_LIMIT            = 100;  // 每次最多可推送多少个用户

    const USER_TAG_OVER_LIMIT_ERROR  = '用户标签超过最大值!';
    const PUSH_USER_OVER_LIMIT_ERROR = '推送用户数量超过最大值';
    const SUCCESS                    = 0;
    const FAILURE                    = 1;

    protected $isOffline         = true;
    protected $offlineExpireTime = 259200;
    protected $pushNetworkType   = 0;

    protected $errorCode = 0;
    protected $errorMsg  = '';

    protected $iGeTui;
    protected $getuiTemplate;
    protected $appId;
    protected $appKey;
    protected $masterSecret;

    /**
     * Constructor
     */
    public function __construct() {
        $this->getuiTemplate = new GetuiPushTemplate();

        $config              = c('getui_key');

        $this->appKey        = $config['app_key'];
        $this->masterSecret  = $config['master_secret'];
        $this->appId         = $config['app_id'];

        $this->getuiTemplate->setAppId($config['app_id']);
        $this->getuiTemplate->setAppKey($config['app_key']);

        $this->iGeTui        = new IGeTui(self::PUSH_URL, $this->appKey, $this->masterSecret);
    }

    /**
     * 用户状态查询
     *
     * @param  string $clientId 用户标识id
     * @return mixed
     */
    public function getUserStatus($clientId) {
        return $this->iGeTui->getClientIdStatus($this->appId, $clientId);
    }

    /**
     * 推送任务停止
     *
     * @param  string $taskId 推送任务id
     * @return mixed
     */
    public function stoptask($taskId){
        return $this->iGeTui->stop($taskId);
    }

    /**
     * 设置用户标签
     *
     * @param   string     $clientId 用户标识id
     * @param   int|array  $tag      标签
     * @return mixed
     */
    public function setUserTag($clientId, $tag) {
        if (empty($clientId) || empty($tag)) {
            return false;
        }

        $tagArray = is_array($tag) ? $tag : array($tag);
        if ($this->isTagOverLimit($tagArray)) {
            $this->setError(self::FAILURE, self::USER_TAG_OVER_LIMIT_ERROR);
            return false;
        }

        return  $this->iGeTui->setClientTag($this->appId ,$clientId, $tagArray);
    }

    /**
     * 通过clientId推送信息给用户,支持多个
     *
     * @param  int|array $clientId
     * @param  string    $template      模板
     * @param  array     $message       消息详情
     * @param  string    $platform      平台
     * @param  int       $apns          是否需要apns
     * @return mixed
     */
    public function pushMessageToUserByClientId($clientId, $template, $message, $platform, $apns = 1) {
        return $this->pushMessageToUser('client_id', $clientId, $template, $message, $platform, $apns);
    }

    /**
     * 通过alias推送信息给用户,支持多个
     *
     * @param  int|array $alias
     * @param  string    $template      模板
     * @param  array     $message       消息详情
     * @param  string    $platform      平台
     * @param  int       $apns          是否需要apns
     * @return mixed
     */
    public function pushMessageToUserByAlias($alias, $template, $message, $platform, $apns = 1) {
        return $this->pushMessageToUser('alias', $alias, $template, $message, $platform, $apns);
    }

    /**
     * 推送信息给用户
     *
     * @param  string    $userIdentifier 用户标识client_id或alias
     * @param  string    $user           用户
     * @param  string    $template       模板
     * @param  string    $message        推送的消息内容
     * @param  string    $platform       平台
     * @param  int       $apns           是否需要apns
     * @return mixed
     */
    private function pushMessageToUser($userIdentifier, $user, $template, $message, $platform, $apns = 1) {
        if (empty($userIdentifier) || empty($user) || empty($template) || empty($message)) {
            return false;
        }

        $userArray = is_array($user) ? $user : array($user);
        if ($this->isPushUserOverLimit($userArray)) {
            $this->setError(self::FAILURE, self::PUSH_USER_OVER_LIMIT_ERROR);
            return false;
        }

        $this->setMessagePropertyDefaultValue($message);

        return  $this->pushMessageToList($userIdentifier, $userArray, $template, $message, $platform, $apns);
    }

    /**
     * 设置消息属性默认值
     */
    private function setMessagePropertyDefaultValue(&$message) {
        if (!isset($message['is_offline'])) {
            $message['is_offline'] = $this->isOffline;
        }
        if (!isset($message['offline_expire_time'])) {
            $message['offline_expire_time'] = $this->offlineExpireTime;
        }
        if (!isset($message['push_network_type'])) {
            $message['push_network_type'] = $this->pushNetworkType;
        }
    }

    /**
     * 对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
     *
     * @param  string $template    模板
     * @param  array  $message     消息内容
     * @param  array  $tagList     标签列表
     * @param  array  $proviceList 省份列表
     * @return mixed
     */
    public function pushMessageToApp($template, $message, $tagList = array(),  $proviceList = array()) {
        $appPush = new IGtAppMessage();
        $this->setMessagePropertyDefaultValue($message);
        $appPush->set_isOffline($message['is_offline']);
        $appPush->set_offlineExpireTime($message['offline_expire_time']);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $templateObject = $this->getuiTemplate->getTemplateObject($template, $message);
        $appPush->set_data($templateObject);
        $networkTypeId = $this->getPushNetworkTypeId($message['push_network_type']);
        $appPush->set_PushNetWorkType($networkTypeId);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $appPush->set_phoneTypeList(array('ANDROID'));
        $appIdArray = is_array($this->appId) ? $this->appId : array($this->appId);
        $appPush->set_appIdList($appIdArray);
        if (!empty($proviceList)) {
            $appPush->set_provinceList($proviceList);
        }
        if (!empty($tagList)) {
            $appPush->set_tagList($tagList);
        }

        return $this->iGeTui->pushMessageToApp($appPush);
    }

    /**
     * 绑定别名,支持给批量的clientId,绑定同一个别名
     *
     * @param  string|array  $clientId 用户标识id
     * @param  string $alias 别名
     * @return mixed
     */
    public function bindAlias($clientId, $alias) {
        $targetList    = array();
        $clientIdArray = is_array($clientId) ? $clientId : array($clientId);
        $target        = new IGtTarget();
        $target->set_alias($alias);
        foreach ($clientIdArray as $cid) {
            $target->set_clientId($cid);
            $targetList[] = $target;
        }

        return $this->iGeTui->bindAliasBatch($this->appId, $targetList);
    }

    /**
     * 解除clientId与alias之间的绑定
     *
     * @param  string $clientId 用户标识id
     * @param  string $alias    别名
     * @return mixed
     */
    public function unbindAlias($clientId, $alias) {
        return $this->iGeTui->unBindAlias($this->appId, $alias, $clientId);
    }

    /**
     * 通过别名获取clientId
     *
     * @param  string $alias 别名
     * @return mixed
     */
    public function getClientIdByAlias($alias) {
       return $this->iGeTui->queryClientId($this->appId, $alias);
    }

    /**
     * 获取clientId的别名
     *
     * @param  string $clientId 用户标识id
     * @return mixed
     */
    public function getAliasByClientId($clientId) {
       return $this->iGeTui->queryAlias($this->appId, $clientId);
    }

    /**
     * 获取推送结果
     *
     * @param  string $taskId 任务id
     * @return mixed
     */
    public function getPushResult($taskId){
        $params           = array();
        $params["action"] = "getPushMsgResult";
        $params["appkey"] = $this->appKey;
        $params["taskId"] = $taskId;
        $sign             = $this->createSign($params);
        $params["sign"]   = $sign;
        $data             = json_encode($params);
        $result           = $this->httpPost(self::PUSH_RESULT_URL, $data);

        return $result;
    }

    /**
     * 对多个用户进行推送，建议为50个用户
     *
     * @param  string    $userIdentifier 用户标识，clientId、alias
     * @param  array     $userList
     * @param  string    $template      模板类型
     * @param  array     $message       消息详情
     * @param  string    $platform      平台
     * @param  int       $apns          是否需要apns
     * @return mixed
     */
    private function pushMessageToList($userIdentifier, $userList, $template, $message, $platform, $apns = 1) {
        $multiPush = new IGtListMessage();

        //$multiPush->set_isOffline($message['is_offline']);//是否离线
        //$multiPush->set_offlineExpireTime($message['offline_expire_time']);//离线时间
        $multiPush->set_isOffline(true);//是否离线
        $multiPush->set_offlineExpireTime(0);//离线时间
        $templateObject = $this->getuiTemplate->getTemplateObject($template, $message);

        if($platform == 'ios' && $apns){
            $apn = new IGtAPNPayload();

            $simpleObject           = new SimpleAlertMsg();
            $simpleObject->alertMsg = $message['content'];
            $apn->alertMsg          = $simpleObject;
            $apn->badge             = 1;
            $apn->sound             = "default";
            $apn->add_customMsg("payload", $message['transmission_content']);
            $apn->contentAvailable  = 1;
            $apn->category          = "action";

            $templateObject->set_apnInfo($apn);
        }


        $multiPush->set_data($templateObject);//设置推送消息类型

        $networkTypeId = $this->getPushNetworkTypeId($message['push_network_type']);
        $multiPush->set_PushNetWorkType($networkTypeId);
        $taskId = $this->iGeTui->getContentId($multiPush);

        $targetList = $this->getTargetList($userIdentifier, $userList);

        return $this->iGeTui->pushMessageToList($taskId, $targetList);
    }

    /**
     * 获取推送目标用户列表
     *
     * @param  string   $userIdentifier  用户标识
     * @param  array    $userList
     * @return array
     */
    private function getTargetList($userIdentifier, $userList) {
        $targetList = array();
        $target     = new IGtTarget();
        $method     = $userIdentifier == 'client_id' ? 'set_clientId' : 'set_alias';
        foreach ($userList as $uid) {
            $target->set_appId($this->appId);
            $target->$method($uid);
            $targetList[] = $target;
        }

        return $targetList;
    }

    /**
     * 获取要推送的网络类型id,根据WIFI推送消息，1为wifi推送，0为不限制推送
     *
     * @param  string $networkType 网络类型
     * @return int
     */
    private function getPushNetworkTypeId($networkType) {
        return 0;
        //return $networkType == 'wifi' ? 1 : 0;
    }

    /**
     * 判断标签是否超过最大值
     *
     * @param  array $tag
     * @return bool
     */
    private function isTagOverLimit($tag) {
        return count($tag) > self::USER_TAG_LIMIT;
    }

    /**
     * 判断推送用户的数量是否超过限制
     */
    private function isPushUserOverLimit($userList) {
        return count($userList) > self::PUSH_USER_LIMIT;
    }

    /**
     * 创建签名
     *
     * @param  array  $params
     * @return string
     */
    private function createSign($params){
        $sign = $this->masterSecret;
        foreach ($params as $key => $val){
            if (isset($key)  && isset($val) ){
                if(is_string($val) || is_numeric($val) ) {
                    $sign .= $key . ($val);
                }
            }
        }

        return md5($sign);
    }

    /**
     * 发送post请求
     *
     * @param  string $url
     * @param  array  $data
     * @return mixed
     */
    private function httpPost($url,$data) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'GeTui PHP/1.0');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    private function setError($errorCode, $errorMsg) {
        $this->errorCode = $errorCode;
        $this->errorMsg  = $errorMsg;
    }

    public function getError() {
        return array(
          'code' => $this->errorCode,
          'msg'  => $this->errorMsg,
        );
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }

}
