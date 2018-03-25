<?php

/**
 * 执行任务
 *
 */
class StartHandle extends BaseService
{
    const MQ_QUEUE = 'babyTaskList';

    /**
     * 取出要处理的mq
     */
    public function start()
    {
        $mqEmpty = false;
        while (!$mqEmpty) {
            $soa    = SoaClient::getSoa('tqmq_v2', 'operation');
            $result = $soa->pop(self::MQ_QUEUE);
            //如果发生异常
            if ($soa->hasError()) {
                LOG::e("MQ调用发生异常! code:" . $soa->getErrorCode() . "，msg:" . $soa->getErrorMsg());
                exit;
            }

            //有内容的时候则进行处理
            if ($result['msg_id']) {
                $content = json_decode($result['msg_body'], true);

                if (!empty($content)) {
                    if (!empty($content['allow_ip'])) {
                        $ip = $this->getOwnIp();
                        if ($ip != trim($content['allow_ip'])) {
                            //ip有并且不是这台ip处理的话  推回到task_system
                            $this->pushBackToTask($content);
                        } else {
                            $this->execCli($content);
                        }
                    } else {
                        $this->execCli($content);
                    }
                } else {
                    LOG::w("MQ调用发生异常 从MQ里取出的 内容为空");
                }

                $soa->remove(self::MQ_QUEUE, $result['msg_id']);
            } else {
                $mqEmpty = true;
            }
        }
    }

    /**
     * 执行 cli
     *
     * @param   $content     array
     *
     * @return bool
     */
    private function execCli($content)
    {
        if (empty($content)) {
            LOG::w("执行cli 内容为空");
            return false;
        }
        $class         = trim($content['cli_name']);
        $action        = trim($content['cli_func']);
        $param         = trim($content['extra_param']);
        $version       = intval($content['cli_version']);
        $interval_time = intval($content['interval_time']);
        $once_num      = intval($content['once_num']);
        if (empty($class) || empty($action) || empty($version)) {
            LOG::w("执行cli 中参数不完整 class:{$class} action: {$action} version: v{$version}");

            return false;
        }
        $cliKey = c("clikey");
        $shell  = "php ../cli.php {$cliKey} v{$version} {$class} {$action} {$param}";

        if ($interval_time == 0 || $once_num == 0) { //任务只执行一次
            shell_exec($shell);
        } else { //任务循环执行
            for ($i = 1; $i <= $once_num; $i++) {
                shell_exec($shell);
                sleep($interval_time);
            }
        }
        return true;
    }

    /**
     * 将内容推回task_system
     *
     * @param  $content  array
     *
     * @return  array
     */
    private function pushBackToTask($content)
    {
        $soa = SoaClient::getSoa('task', 'Task');
        $soa->PushTaskMq(self::MQ_QUEUE, $content);
        LOG::n("取到处理ip不是本机ip, 推回任务中心");
        if ($soa->hasError()) {
            LOG::w("start推回 任务中心出错  code : " . $soa->getErrorCode());
        }
    }

    /**
     * 获取ip
     *
     * @return  ip
     */
    private function getOwnIp()
    {
        $ip = shell_exec("ip addr | egrep ' inet ' | grep -v '127.0.0.1' | awk -F ' ' '{print $2}' | cut -d '/' -f 1");

        return trim($ip);
    }
}