<?php

class BaseInterface
{
    public function __construct()
    {
        $saveArray = array(
            b('token'),
            b('appcode'),
            b('platform_name'),
            b('os'),
            b('os_version'),
            b('app_version'),
            b('access'),
            'interface request',
        );

        LOG::i(join($saveArray, ' # '));

        //防sql注入，配置开关为auth_on
        if ($GLOBALS['X_G']["filter_on"]){
            $attackResult = filterParams();

            if($attackResult !== true){
                LOG::e("用户参数可能包含sql注入，key:" . $attackResult['key'] . " value:" . $attackResult['value']);

                $service = new BaseService();
                $service->setError("may_be_attack");

                $this->respondFailure($service->getError());
            }
        }

        $this->beforeInterface();
    }

    public function __destruct()
    {
        $this->afterInterface();
    }

    /**
     * API数据返回统一方法
     * json格式
     *
     * @author Sam Lu
     * @param        $response_status
     * @param        $data
     * @param null   $extra
     * @param string $msg
     */
    public function respond($response_status, $data, $extra = null, $msg = '')
    {
        //TQCC缓存记录
        if(is_object($GLOBALS['X_G']['tqcc_cache'])){
            $dataArray = array(
                "data"  => $data,
                "extra" => $extra,
                "msg"   => $msg
            );

            $GLOBALS['X_G']['tqcc_result'] = serialize($dataArray);
        }

        $response = array(
            'info'            => array(
                'extra' => $extra,
                'data'  => !empty($data) ? $data : null,
            ),
            'response_status' => $response_status,
            'msg'             => $msg
        );

        echo json_encode($response);

        if($response_status != 'success'){
            $failureArray = array(
                var_export($_REQUEST, true),
                var_export($response, true),
                $response_status,
            );

            LOG::n(join($failureArray, ' # '));

            $this->beforeInterface();
        }

        exit();
    }

    public function respondSuccess($data, $extra = null, $msg = '')
    {
        $this->respond('success', $data, $extra, $msg);
    }

    public function respondFailure($error = array('code'=>'un_define_code','msg'=>'系统异常，请稍后再试(错误码：9998)'))
    {
        $this->respond($error['code'], null, null, $error['msg']);
    }

    /**
     * 接口调用前相关事件
     * 子类覆盖时请调用parent::beforeInterface();
     */
    public function beforeInterface()
    {
        $isCache = false;

        //判断是否是审核版本，是的话不缓存
        if($GLOBALS['X_G']['is_app_examined']){
            $baseService = new BaseService();
            $isCache     = $baseService->isAppExamined();
        }

        if(!$isCache){
            //取得缓存配置
            $cacheConfig = c($GLOBALS['X_G']['uri']);

            //假如存在缓存配置才进行缓存处理
            if(!empty($cacheConfig)){

                if(!empty($cacheConfig['mainParam'])){
                    foreach($cacheConfig['mainParam'] as $value){
                        if($_REQUEST[$value] == ""){
                            require(X_PATH . '/exception/TQCCMainParamNotExistException.php');
                            throw new TQCCMainParamNotExistException('interface主要参数未传或者TQCC配置异常！');
                        }
                    }
                }else{
                    $cacheConfig['mainParam'] = array('no_mainParam');
                    $_REQUEST['no_mainParam'] = 'no_mainParam';
                }

                $cacheConfig['uri'] = c("uri");

                //取得缓存服务
                $cache = new Cache($cacheConfig);

                //取得缓存内容
                $cacheContent = $cache->getCacheContent();

                //判断是否存在缓存内容
                if($cacheContent){
                    //反序列化相关的缓存内容
                    $cacheArray = unserialize($cacheContent);

                    //记录缓存命中日志
                    $saveArray = array(
                        'cache hit',
                    );

                    LOG::i(join($saveArray, ' # '));

                    //返回内容
                    $this->respondSuccess($cacheArray['data'], $cacheArray['extra'], $cacheArray['msg']);
                }else{
                    //如果内容不存在则保存对象用于设置
                    $GLOBALS['X_G']['tqcc_cache'] = $cache;
                }
            }

        }
    }

    /**
     * 接口调用后相关事件
     * 子类覆盖时请调用parent::afterInterface();
     */
    public function afterInterface()
    {
        //假如是有缓存并且有结果的话，则取得返回值并且进行缓存
        if(is_object($GLOBALS['X_G']['tqcc_cache']) && $GLOBALS['X_G']['tqcc_result']){
            $isCache = false;

            //判断是否是审核版本，是的话不缓存
            if($GLOBALS['X_G']['is_app_examined']){
                $baseService = new BaseService();
                $isCache     = $baseService->isAppExamined();
            }

            if(!$isCache){
                $cache = $GLOBALS['X_G']['tqcc_cache'];
                $cache->setCacheContent($GLOBALS['X_G']['tqcc_result']);
            }
        }
    }
}