<?php
/**
 * 环信api
 *
 * @date    2015-03-12
 * @author  xiaoy
 */
class Hx {
    /**
     * @var string 账号id
     */
    private $client_id;

    /**
     * @var string 账号密钥
     */
    private $client_secret;

    /**
     * @var string 账号空间
     */
    private $org_name;

    /**
     * @var string 应用名称
     */
    private $app_name;

    /**
     * @var string 调用地址
     */
    private $url;

    /**
     * 初始化参数
     */
    public function __construct() {
        $config = c("huanxin_key");

        $this->client_id        = $config["client_id"];
        $this->client_secret    = $config["client_secret"];
        $this->org_name         = $config["org_name"];
        $this->app_name         = $config["app_name"];
        if (! empty ( $this->org_name ) && ! empty ( $this->app_name )) {
            $this->url = 'https://a1.easemob.com/' . $this->org_name . '/' . $this->app_name . '/';
        }
    }

    /**
     * 批量注册【建议批量发送的数量不要过多, 建议在20-60之间】
     *
     * @param $userArray array 用户名密码数组
     *
     * @return mixed
     */
    public function accreditRegister($userArray) {
        $url            = $this->url . "users";
        $access_token   = $this->getToken ();
        $header[]       = 'Authorization: Bearer ' . $access_token;
        $result         = $this->postCurl ( $url, $userArray, $header );
        $result         = json_decode($result, true);

        return $result;
    }

    /**
     * 修改用户的昵称
     *
     * @param $username string 用户的uid
     * @param $nickname string 昵称
     *
     * @return array
     */
    public function changeNickname($username, $nickname) {
        $url = $this->url . "users/" . $username;
        $access_token   = $this->getToken ();
        $header []      = 'Authorization: Bearer ' . $access_token;
        $dataArray      = array(
            "nickname" => $nickname
        );
        $result         = $this->postCurl ( $url, $dataArray, $header, $type = 'PUT' );
        $result         = json_decode($result, true);

        return $result;
    }


//    /**
//     * 获取指定用户详情
//     *
//     * @param $username 用户名
//     */
//    public function userDetails($username) {
//        $url = $this->url . "users/" . $username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = 'GET' );
//        return $result;
//    }

//    /**
//     * 重置用户密码
//     *
//     * @param $options['username'] 用户名
//     * @param $options['password'] 密码
//     * @param $options['newpassword'] 新密码
//     */
//    public function editPassword($options) {
//        $url = $this->url . "users/" . $options ['username'] . "/password";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, $options, $header, $type = 'PUT');
//        return $result;
//    }

//    /**
//     * 删除用户
//     *
//     * @param $username 用户名
//     */
//    public function deleteUser($username) {
//        $url = $this->url . "users/" . $username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = 'DELETE' );
//    }

//    /**
//     * 批量删除用户
//     * 描述：删除某个app下指定数量的环信账号。上述url可一次删除300个用户,数值可以修改 建议这个数值在100-500之间，不要过大
//     *
//     * @param $limit="300" 默认为300条
//     * @param $ql 删除条件
//     *        	如ql=order+by+created+desc 按照创建时间来排序(降序)
//     */
//    public function batchDeleteUser($limit = "300", $ql = '') {
//        $url = $this->url . "users?limit=" . $limit;
//        if (! empty ( $ql )) {
//            $url = $this->url . "users?ql=" . $ql . "&limit=" . $limit;
//        }
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = 'DELETE' );
//    }
//
//    /**
//     * 给一个用户添加一个好友
//     *
//     * @param
//     *        	$owner_username
//     * @param
//     *        	$friend_username
//     */
//    public function addFriend($owner_username, $friend_username) {
//        $url = $this->url . "users/" . $owner_username . "/contacts/users/" . $friend_username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header );
//    }
//    /**
//     * 删除好友
//     *
//     * @param
//     *        	$owner_username
//     * @param
//     *        	$friend_username
//     */
//    public function deleteFriend($owner_username, $friend_username) {
//        $url = $this->url . "users/" . $owner_username . "/contacts/users/" . $friend_username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "DELETE" );
//    }
//    /**
//     * 查看用户的好友
//     *
//     * @param
//     *        	$owner_username
//     */
//    public function showFriend($owner_username) {
//        $url = $this->url . "users/" . $owner_username . "/contacts/users/";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "GET" );
//    }
//    // +----------------------------------------------------------------------
//    // | 聊天相关的方法
//    // +----------------------------------------------------------------------
//    /**
//     * 查看用户是否在线
//     *
//     * @param
//     *        	$username
//     */
//    public function isOnline($username) {
//        $url = $this->url . "users/" . $username . "/status";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "GET" );
//        return $result;
//    }
    /**
     * 发送消息
     *
     * @param $from_user   string       发送方用户名
     * @param $username    array        接收方用户名或群组id，array('1','2')
     * @param $target_type string       默认为：users 描述：给一个或者多个用户(users)或者群组发送消息(chatgroups)
     * @param $action      string       动作
     * @param $ext         array|bool   自定义参数
     *
     * @return array
     */
    function sendCmdMsg($from_user = "admin", $username, $action, $target_type = "users", $ext = false) {
        if(is_string($username)){
            $username = array($username);
        }

        $option ['target_type'] = $target_type;
        $option ['target']      = $username;
        $params ['type']        = "cmd";
        $params ['action']      = $action;
        $option ['msg']         = $params;
        $option ['from']        = $from_user;

        if($ext){
            $option ['ext']     = $ext;
        }

        $url = $this->url . "messages";

        $access_token = $this->getToken ();
        $header[]     = 'Authorization: Bearer ' . $access_token;

        $result = $this->postCurl ( $url, $option, $header );
        $result = json_decode($result, true);

        if(!empty($result['error_description'])){
            LOG::w("环信api异常-发送消息：" . $result['error_description']);
        }

        return $result;
    }

    /**
     * 发送消息
     *
     * @param $from_user   string       发送方用户名
     * @param $username    array        接收方用户名或群组id，array('1','2')
     * @param $target_type string       默认为：users 描述：给一个或者多个用户(users)或者群组发送消息(chatgroups)
     * @param $content     string       消息内容
     * @param $ext         array|bool   自定义参数
     *
     * @return array
     */
    function sendTxtMsg($from_user = "admin", $username, $content, $target_type = "users", $ext = false) {
        if(is_string($username)){
            $username = array($username);
        }

        $option ['target_type'] = $target_type;
        $option ['target']      = $username;
        $params ['type']        = "txt";
        $params ['msg']         = $content;
        $option ['msg']         = $params;
        $option ['from']        = $from_user;

        if($ext){
            $option ['ext']     = $ext;
        }

        $url = $this->url . "messages";

        $access_token = $this->getToken();
        $header[]     = 'Authorization: Bearer ' . $access_token;

        $result = $this->postCurl($url, $option, $header);
        $result = json_decode($result, true);

        if(!empty($result['error_description'])){
            LOG::w("环信api异常-发送消息：" . $result['error_description']);
        }

        return $result;
    }
//    /**
//     * 获取app中所有的群组
//     */
//    public function chatGroups() {
//        $url = $this->url . "chatgroups";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "GET" );
//        return $result;
//    }
//    /**
//     * 创建群组
//     *
//     * @param $option['groupname'] //群组名称,
//     *        	此属性为必须的
//     * @param $option['desc'] //群组描述,
//     *        	此属性为必须的
//     * @param $option['public'] //是否是公开群,
//     *        	此属性为必须的 true or false
//     * @param $option['approval'] //加入公开群是否需要批准,
//     *        	没有这个属性的话默认是true, 此属性为可选的
//     * @param $option['owner'] //群组的管理员,
//     *        	此属性为必须的
//     * @param $option['members'] //群组成员,此属性为可选的
//     */
//    public function createGroups($option) {
//        $url = $this->url . "chatgroups";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, $option, $header );
//        return $result;
//    }
//    /**
//     * 获取群组详情
//     *
//     * @param
//     *        	$group_id
//     */
//    public function chatGroupsDetails($group_id) {
//        $url = $this->url . "chatgroups/" . $group_id;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "GET" );
//        return $result;
//    }
//    /**
//     * 删除群组
//     *
//     * @param
//     *        	$group_id
//     */
//    public function deleteGroups($group_id) {
//        $url = $this->url . "chatgroups/" . $group_id;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "DELETE" );
//        return $result;
//    }
//    /**
//     * 获取群组成员
//     *
//     * @param
//     *        	$group_id
//     */
//    public function groupsUser($group_id) {
//        $url = $this->url . "chatgroups/" . $group_id . "/users";
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "GET" );
//        return $result;
//    }
//    /**
//     * 群组添加成员
//     *
//     * @param
//     *        	$group_id
//     * @param
//     *        	$username
//     */
//    public function addGroupsUser($group_id, $username) {
//        $url = $this->url . "chatgroups/" . $group_id . "/users/" . $username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "POST" );
//        return $result;
//    }
//    /**
//     * 群组删除成员
//     *
//     * @param
//     *        	$group_id
//     * @param
//     *        	$username
//     */
//    public function delGroupsUser($group_id, $username) {
//        $url = $this->url . "chatgroups/" . $group_id . "/users/" . $username;
//        $access_token = $this->getToken ();
//        $header [] = 'Authorization: Bearer ' . $access_token;
//        $result = $this->postCurl ( $url, '', $header, $type = "DELETE" );
//        return $result;
//    }

    /**
     * 聊天消息记录
     *
     * @param $ql     string 查询条件如：$ql
     *                = "select+*+where+from='" . $uid . "'+or+to='". $uid ."'+order+by+timestamp+desc&limit=" . $limit . $cursor;
     *                默认为order by timestamp desc
     * @param $cursor string 游标,默认为空
     * @param $limit  int 条数,默认20
     *
     * @return mixed
     */
    public function chatRecord($ql = '', $cursor = '', $limit = 20) {
        $ql     = ! empty( $ql )       ? "ql=" . $ql            : "order+by+timestamp+desc";
        $cursor = ! empty( $cursor )   ? "&cursor=" . $cursor   : '';
        $url    = $this->url . "chatmessages?" . $ql . "&limit=" . $limit . $cursor;

        $access_token   = $this->getToken();
        $header[]       = 'Authorization: Bearer ' . $access_token;

        $result = $this->postCurl($url, '', $header, $type = "GET");
        return $result;
    }

    /**
     * 获取Token
     */
    public function getToken() {
        $option ['grant_type']      = "client_credentials";
        $option ['client_id']       = $this->client_id;
        $option ['client_secret']   = $this->client_secret;
        $url = $this->url . "token";

        $redis = r("huanxin_redis");
        $token = $redis->get(c("redis_key.sns_token"));

        if(!$token){
            $result = $this->postCurl($url, $option, null);
            $result = json_decode($result, true);
            if($result['access_token'] != ''){
                $redis->set(c("redis_key.sns_token"), $result['access_token']);
                $redis->expire(c("redis_key.sns_token"), $result['expires_in']);
            }else{
                LOG::e("无法获取环信管理员token！");
            }

            return $result['access_token'];
        }

        return $token;
    }

    /**
     * CURL Post
     */
    private function postCurl($url, $option, $header, $type = 'POST') {
        $curl = curl_init (); // 启动一个CURL会话
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
        if (! empty ( $option )) {
            $options = json_encode ( $option );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $options ); // Post提交的数据包
        }
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
        if(!empty($header)){
            curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header ); // 设置HTTP头
        }
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $type );
        $result = curl_exec ( $curl ); // 执行操作
        //$res = object_array ( json_decode ( $result ) );
        //$res ['status'] = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
        //pre ( $res );
        curl_close ( $curl ); // 关闭CURL会话
        return $result;
    }
}