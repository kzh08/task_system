<?php
/**
 * 个推推送模板
 */

class GetuiPushTemplate {
    private $appId;
    private $appKey;

    private $message;            // 消息内容
    private $isRing      = true; // 默认响铃
    private $isVibrate   = true; // 默认震动
    private $isClearable = true; //是否可清除

    // 模板
    private $template = array(
        'notify_popup'  => 'getIGtNotyPopLoadTemplate',   // 通知弹窗模板
        'notify'        => 'getIGtNotificationTemplate',  // 通知模板
        'link'          => 'getIGtLinkTemplate',          // 链接模板
        'transmission'  => 'getIGtTransmissionTemplate',  // 透传模板
    );

    public function setAppId($appId) {
        $this->appId = $appId;
    }

    public function setAppKey($appKey) {
        $this->appKey = $appKey;
    }

    /**
     * 获取模板对象
     *
     * @param  string $templateType 模板类型
     * @param  array  $message      消息内容
     * @return object|false
     */
    public function getTemplateObject($templateType, $message) {
        $method = $this->template[$templateType];
        if ( !is_callable(array('GetuiPushTemplate', $method)) || empty($message)) {
            return false;
        }

        $this->message = $message;
        $this->setMessagePropertyDefaultValue();
        return $this->$method($message);
    }

    /**
     * 设置消息属性默认值
     */
    private function setMessagePropertyDefaultValue() {
        if (!isset($this->message['is_ring'])) {
            $this->message['is_ring']           = $this->isRing;
        }
        if (!isset($this->message['is_vibrate'])) {
            $this->message['is_vibrate']        = $this->isVibrate;
        }

        if (!isset($this->message['is_clearable'])) {
            $this->message['is_clearable']      = $this->isClearable;
        }

        if (!isset($this->message['transmission_type'])) {
            $this->message['transmission_type'] = 'wait_broadcast_start';
        }

        if (!isset($this->message['transmission_content'])) {
            $this->message['transmission_content'] = '';
        }

        if (!isset($this->message['logo'])) {
            $this->message['logo']                 = '';
        }


    }

    /**
     * 获取点击通知栏弹框下载模版
     *
     * @return IGtNotyPopLoadTemplate
     */
    private function getIGtNotyPopLoadTemplate(){
        $template =  new IGtNotyPopLoadTemplate();

        $template ->set_appId($this->appId);                                     //应用appid
        $template ->set_appkey($this->appKey);                                   //应用appkey
        //通知栏
        $template ->set_notyTitle($this->message['notify_title']);                     //通知栏标题
        $template ->set_notyContent($this->message['notify_content']);                 //通知栏内容
        $template ->set_notyIcon($this->message['notify_icon']);                       //通知栏logo
        $template ->set_isBelled($this->message['is_ring']);                           //是否响铃
        $template ->set_isVibrationed($this->message['is_vibrate']);                   //是否震动
        $template ->set_isCleared($this->message['is_clearable']);                     //通知栏是否可清除
        //弹框
        $template ->set_popTitle($this->message['popup_title']);                       //弹框标题
        $template ->set_popContent($this->message['popup_content']);                   //弹框内容
        $template ->set_popImage($this->message['popup_icon']);                        //弹窗logo
        $template ->set_popButton1($this->message['popup_download_button_name']);      //左键
        $template ->set_popButton2($this->message['popup_cancel_button_name']);        //右键
        //下载
        $template ->set_loadIcon($this->message['download_icon']);                     //下载图标
        $template ->set_loadTitle($this->message['download_title']);                   //下载标题
        $template ->set_loadUrl($this->message['download_url']);                       //下载地址
        $template ->set_isAutoInstall($this->message['is_auto_install']);              //是否自动安装
        $template ->set_isActived($this->message['is_actived']);                       //安装完成后是否自动启动应用程序

        return $template;
    }

    /**
     * 获取点击通知打开应用模板
     *
     * @return IGtNotificationTemplate
     */
    private function getIGtNotificationTemplate(){
        $template =  new IGtNotificationTemplate();
        $template->set_appId($this->appId);
        $template->set_appkey($this->appKey);
        $transmissionTypeId = $this->getTransmissionTypeId($this->message['transmission_type']);
        $template->set_transmissionType($transmissionTypeId);                //透传消息类型
        $template->set_transmissionContent($this->message['transmission_content']);//透传内容
        $template->set_title($this->message['title']);                      //通知栏标题
        $template->set_text($this->message['content']);                     //通知栏内容
        $template->set_logo($this->message['logo']);                               //通知栏logo
        $template->set_isRing($this->message['is_ring']);                          //是否响铃
        $template->set_isVibrate($this->message['is_vibrate']);                    //是否震动
        $template->set_isClearable($this->message['is_clearable']);                //通知栏是否可清除

        return $template;
    }

    /**
     * 获取点击通知打开网页模板
     *
     * @return  IGtLinkTemplate
     */
    private function getIGtLinkTemplate(){
        $template =  new IGtLinkTemplate();
        $template ->set_appId($this->appId);               //应用appid
        $template ->set_appkey($this->appKey);             //应用appkey
        $template ->set_title($this->message['title']);          //通知栏标题
        $template ->set_text($this->message['content']);         //通知栏内容
        $template ->set_logo($this->message['logo']);            //通知栏logo
        $template ->set_isRing($this->message['is_ring']);       //是否响铃
        $template ->set_isVibrate($this->message['is_vibrate']); //是否震动
        $template ->set_isClearable($this->message['is_clearable']); //通知栏是否可清除
        $template ->set_url($this->message['url']);              //打开连接地址

        return $template;
    }

    /**
     * 获取透传消息模板
     *
     * @return object
     */
    private function getIGtTransmissionTemplate(){
        $template           =  new IGtTransmissionTemplate();
        $template->set_appId($this->appId);
        $template->set_appkey($this->appKey);
        $transmissionTypeId = $this->getTransmissionTypeId($this->message['transmission_type']);
        $template->set_transmissionType($transmissionTypeId) ;
        $template->set_transmissionContent($this->message['transmission_content']);

        return $template;
    }

    /**
     * 获取透传信息类型id 1为立即启动,2则广播等待客户端自启动
     *
     * @param  string $transmissionType 透传信息类型
     * @return mixed
     */
    private function getTransmissionTypeId($transmissionType) {

        return $transmissionType == 'right_now_start' ? 1 : 2;
    }
} 