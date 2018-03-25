<?php
/**
 * 安卓推送
 * 
 * @author OuQiang
 * @date 15-2-26 上午11:13
 */

require(__DIR__ . '/getui/GetuiPush.php');

class AndroidPush {

    private $getui;

    /**
     * Constructor
     */
    public function __construct(array $config) {
        $this->getui        = new GetuiPush($config['app_id'], $config['app_key'], $config['master_secret']);
    }


    /**
     * 给用户发送通知
     *
     * @param  int|array $user
     * @param  array     $message   消息
     * @param  string    $platform  平台
     * @return mixed
     */
    public function sendNotifyToUser($user, $message, $platform) {
        return $this->getui->pushMessageToUserByClientId($user, 'notify', $message, $platform);
    }

    /**
     * 给用户发送链接地址
     *
     * @param  int|array $user
     * @param  array     $message   消息
     * @param  string    $platform  平台
     * @return mixed
     */
    public function sendLinkToUser($user, $message, $platform) {
        return $this->getui->pushMessageToUserByClientId($user, 'link', $message, $platform);
    }

    /**
     * 给用户发送透传信息
     *
     * @param  int|array $user
     * @param  array     $message   消息
     * @param  string    $platform  平台
     * @return mixed
     */
    public function sendTransmissionToUser($user, $message, $platform) {
        return $this->getui->pushMessageToUserByClientId($user, 'transmission', $message, $platform);
    }

    /**
     * 针对APP发送通知，可通过标签和省份过滤用户
     *
     * @param  array     $message 消息
     * @param array $tagList     标签
     * @param array $proviceList 省份
     * @return mixed
     */
    public function sendNotifyToApp($message, $tagList = array(), $proviceList = array()) {
        return $this->getui->pushMessageToApp('notify', $message , $tagList, $proviceList);
    }

    /**
     * 针对APP发送链接，可通过标签和省份过滤用户
     *
     * @param  array     $message 消息
     * @param array $tagList     标签
     * @param array $proviceList 省份
     * @return mixed
     */
    public function sendLinkToApp($message, $tagList = array(), $proviceList = array()) {
        return $this->getui->pushMessageToApp( 'link', $message, $tagList, $proviceList);
    }

    /**
     * 针对APP发送透传信息，可通过标签和省份过滤用户
     *
     * @param  array     $message 消息
     * @param array $tagList     标签
     * @param array $proviceList 省份
     * @return mixed
     */
    public function sendTransmissionToApp($message, $tagList = array(), $proviceList = array()) {
        return $this->getui->pushMessageToApp('transmission', $message, $tagList, $proviceList);
    }

} 