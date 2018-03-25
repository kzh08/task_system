<?php
/**
 * 七牛云存储服务
 *
 * @author OuQiang
 * @date 15-3-9 下午1:35
 */

class Qiniu {
    const CANT_LOAD_QINIU_SDK  = '不能加载七牛SDK!';
    const FILE_NOT_FOUND       = '文件未找到';
    const CURL_TIMEOUT         = 30;
    const CURL_RETRY_TIMES     = 3;   // 重试次数
    const PERSISTENT_QUERY_URL = 'http://api.qiniu.com/status/get/prefop?id=';

    protected $bucket;      // 空间名
    protected $accessUrl;   // 访问地址
    protected $putPolicy    = array();

    private $putExtra;

    public function __construct($accessKey, $secretKey, $bucket = '') {
        if (empty($accessKey) || empty($secretKey)) {
            exit("accesskey or secretkey is emtpy!");
        }
        $this->loadQiniuSdk();
        $this->putExtra    = new Qiniu_PutExtra();

        Qiniu_SetKeys($accessKey, $secretKey);
        $this->setBucket($bucket);
    }

    /**
     * 上传文件
     *
     * @param  string $filePath     文件路径
     * @param  string $resourceName 上传使用的资源名称,传空由七牛生成
     * @return array
     */
    public function uploadFile($filePath, $resourceName = null) {
        if (!file_exists($filePath)) {
            return false;
        }
        $uploadToken = $this->getUploadToken($resourceName);

        return Qiniu_PutFile($uploadToken, $resourceName, $filePath, $this->putExtra);
    }

    /**
     * 上传流
     *
     * @param  string $stream       文件流
     * @param  string $resourceName 上传使用的资源名称,传空由七牛生成
     * @return array|false
     */
    public function uploadStream($stream, $resourceName = null) {
        if (empty($stream)) {
            return false;
        }

        $uploadToken = $this->getUploadToken($resourceName);

        return Qiniu_Put($uploadToken, $resourceName, $stream, $this->putExtra);
    }

    /**
     * 下载资源
     *
     * @param  string $url
     * @return string|false
     */
    public function download($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL           , $url);
        curl_setopt($curl, CURLOPT_TIMEOUT       , self::CURL_TIMEOUT);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        for ($i = 0; $i < self::CURL_RETRY_TIMES; ++$i) {
            $content  = curl_exec($curl);
            $errorMsg = curl_error($curl);
            if (empty($errorMsg)) {
                break;
            }
        }
        curl_close($curl);

        return $content;
    }

    /**
     * 在同一个空间内复制
     *
     * @param  string $srcResourceName 源资源名
     * @param  string $dstResourceName 目标资源名
     * @return array|false
     */
    public function copyInSameBucket($srcResourceName, $dstResourceName) {
        return $this->copy($this->bucket, $srcResourceName, $this->bucket, $dstResourceName);
    }

    /**
     * 在不同的空间之间复制
     *
     * @param  string $srcBucket       源空间
     * @param  string $srcResourceName 源资源名
     * @param  string $dstBucket       目标空间
     * @param  string $dstResourceName 目标资源名
     * @return array|false
     */
    public function copyInDifferentBucket($srcBucket, $srcResourceName,  $dstBucket, $dstResourceName) {
        return $this->copy($srcBucket, $srcResourceName, $dstBucket, $dstResourceName);
    }

    /**
     * 在同一个空间内移动
     *
     * @param  string $srcResourceName 源资源名
     * @param  string $dstResourceName 目标资源名
     * @return array|false
     */
    public function moveInSameBucket($srcResourceName, $dstResourceName){
        return $this->move($this->bucket, $srcResourceName, $this->bucket, $dstResourceName);
    }

    /**
     * 在不同空间之间移动
     *
     * @param  string $srcBucket       源空间
     * @param  string $srcResourceName 源资源名
     * @param  string $dstBucket       目标空间
     * @param  string $dstResourceName 目标资源名
     * @return array|false
     */
    public function moveInDifferentBucket($srcBucket, $srcResourceName,  $dstBucket, $dstResourceName) {
        return $this->move($srcBucket, $srcResourceName, $dstBucket, $dstResourceName);
    }

    /**
     * 删除资源
     *
     * @param  $resourceName
     * @return array
     */
    public function delete($resourceName){
        return Qiniu_RS_Delete($this->getQiniuClient(), $this->bucket, $resourceName);
    }

    /**
     * 批量删除资源
     *
     * @param  $data array
     * @return array
     */
    public function batchDelete($data)
    {
        return Qiniu_RS_BatchDelete($this->getQiniuClient(), $data);
    }

    /**
     * 批量检测资源
     *
     * @param  $data  array
     *
     * @return array
     */
    public function batchStat($data)
    {
        return Qiniu_RS_BatchStat($this->getQiniuClient(), $data);
    }

    /**
     * 获取资源状态
     *
     * @param  string $resourceName 资源名称
     * @return array
     */
    public function getStat($resourceName) {
        return Qiniu_RS_Stat($this->getQiniuClient(), $this->bucket, $resourceName);
    }

    /**
     * 获取上传token
     *
     * @param  string $resourceName 文件名称
     * @return string
     */
    public function getUploadToken($resourceName = null){
        $scope  = $this->bucket;
        if (!empty($resourceName)) {
            $scope .= ':' . $resourceName;
        }

        $putPolicyService = new Qiniu_RS_PutPolicy($scope);

        foreach ($this->putPolicy as $key => $value) {
            $putPolicyService->$key = $value;
        }

        $token = $putPolicyService->Token(null);

        return $token;
    }

    /**
     * 触发持久化数据操作
     *
     * @param  array $data
     * @return array|false
     */
    public function pfop(array $data) {
        $pfop            = new Qiniu_Pfop();
        $pfop->Bucket    = $this->bucket;
        $pfop->Key       = $data['key'];
        $pfop->Fops      = $data['ops'];
        $pfop->Pipeline  = $data['pipeline'];
        $pfop->NotifyURL = $data['notifyUrl'];
        $pfop->Force     = 1;

        return $pfop->MakeRequest(new Qiniu_MacHttpClient(null));
    }

    /**
     * 获取持久化处理状态
     *
     * @param  string $persistentId 持久化id
     * @return string|false
     */
    public function getPersistentOpsStatus($persistentId) {
        if (empty($persistentId)) {
            return false;
        }

        $response = file_get_contents(self::PERSISTENT_QUERY_URL . $persistentId);

        return !empty($response) ? json_decode($response, ture) : false;
    }

    /**
     * 获取视频元信息
     *
     * @param  string $url
     * @return array|false
     */
    public function getVideoMetaInfo($url) {
        if (empty($url)) {
            return false;
        }

        $response = file_get_contents($url . '?avinfo');

        return empty($response) ? false : json_decode($response);
    }

    /**
     * @param mixed $bucket
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @param array $putPolicy
     */
    public function setPutPolicy($putPolicy)
    {
        $this->putPolicy = $putPolicy;
    }

    /**
     * @param mixed $accessUrl
     */
    public function setAccessUrl($accessUrl)
    {
        $this->accessUrl = $accessUrl;
    }

    /**
     * 是否来自于七牛的回调
     *
     * @return bool
     */
    public function isQiniuCallback(){
        $authstr = $_SERVER['HTTP_AUTHORIZATION'];
        if(strpos($authstr,"QBox ") != 0){
            return false;
        }
        $auth = explode(":", substr($authstr,5));
        if(sizeof($auth) !=2 || $auth[0] != $this->accessKey) {
            return false;
        }
        $callbackRequestPath = parse_url($this->putPolicy['callbackUrl'], PHP_URL_PATH);
        $data = $callbackRequestPath . "\n" . file_get_contents('php://input');
        $encodeStr = hash_hmac('sha1',$data, $this->secretKey, true);

        return Qiniu_Encode($encodeStr) == $auth[1];
    }

    /**
     * 复制资源
     *
     * @param  string $srcBucket       源空间
     * @param  string $srcResourceName 源资源名
     * @param  string $dstBucket       目标空间
     * @param  string $dstResourceName 目标资源名
     * @return array|false
     */
    private function copy($srcBucket, $srcResourceName, $dstBucket, $dstResourceName) {
        if (empty($srcBucket) || empty($srcResourceName) || empty($dstBucket) || empty($dstResourceName)) {
            return false;
        }

        $result = Qiniu_RS_Copy($this->getQiniuClient(), $srcBucket, $srcResourceName, $dstBucket, $dstResourceName);

        return $result;
    }

    /**
     * 移动资源
     *
     * @param  string $srcBucket       源空间
     * @param  string $srcResourceName 源资源名
     * @param  string $dstBucket       目标空间
     * @param  string $dstResourceName 目标资源名
     * @return array
     */
    private function move($srcBucket, $srcResourceName,  $dstBucket, $dstResourceName) {
        if (empty($srcBucket) || empty($srcResourceName) || empty($dstBucket) || empty($dstResourceName)) {
            return false;
        }

        return Qiniu_RS_Move($this->getQiniuClient(), $srcBucket, $srcResourceName, $dstBucket, $dstResourceName);
    }

    /**
     * 获取七牛客户端
     *
     * @return object
     */
    protected function getQiniuClient() {
        return new Qiniu_MacHttpClient(null);
    }

    /**
     * 加载七牛sdk
     */
    private function loadQiniuSdk() {
        $fileNameArray = array(
            __DIR__ . '/qiniu/rs.php',
            __DIR__ . '/qiniu/io.php',
            __DIR__ . '/qiniu/auth_digest.php',
            __DIR__ . '/qiniu/pfop.php',
            __DIR__ . '/qiniu/http.php',
        );
        foreach ($fileNameArray as $filename) {
            if (!file_exists($filename)) {
                LOG::e(self::CANT_LOAD_QINIU_SDK . ' : ' . $filename);
                exit();
            }

            require_once($filename);
        }

    }
}