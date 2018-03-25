<?php

/**
 * TASK 中心监控
 */

class MonitorService extends BaseService
{
    /**
     * 监控平台检测方法
     *
     * @param $spaceTime int 间隔时间
     *
     * @return boolean|array
     */
    public function check($spaceTime){
        $result =  $this->isErrorLog($spaceTime);

        if($result !== true){
            return $result;
        }

        return true;
    }

    /**
     * 检测是否存在错误日志
     *
     * @param $spaceTime int 间隔时间
     *
     * @return boolean|array
     */
    private function isErrorLog($spaceTime){
        $errorLogPath = LOG_PATH . '/error.' . date('Ymd') .'.log';

        if(file_exists($errorLogPath)){

            $fp   = file($errorLogPath);
            $last = $fp[count($fp)-1];
            $time =  time() - filemtime($errorLogPath);

            if($time - $spaceTime < 0){
                return $this->setError('error_log_is_exist', '出现错误日志：'.$last);
            }
        }

        return true;
    }
}