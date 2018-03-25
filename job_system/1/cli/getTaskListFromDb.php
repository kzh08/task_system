<?php

/*
 * 从DB中获取需要执行的任务
 */

Class getTaskListFromDb
{
    /**
     * 任务列表写入缓存
     */
    public function setTaskCache()
    {
        $service = s("Task");
        $service->setTaskCache();
        if ($service->hasError()) {
            LOG::w("定时执行 任务写入缓存 调用 task service 出错 code:"
                . $service->getErrorCode(). "msg:".$service->getErrorMsg());
        }
    }

    /**
     * 将任务写入消息队列
     */
    public function setTaskMq()
    {
        $service = s("Task");
        $service->setTaskMq();
        if ($service->hasError()) {
            LOG::w("定时执行 任务写入消息队列 调用 task service 出错 code:"
                . $service->getErrorCode(). "msg:".$service->getErrorMsg());
        }
    }

}
