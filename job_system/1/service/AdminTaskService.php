<?php

//任务后台service
class AdminTaskService extends BaseService
{

    /**
     * 删除
     *
     * @param    int $id
     *
     */
    public function del($id)
    {
        $db            = db();
        $db->tableName = "task";
        $where         = array(
            'id' => $id
        );
        $data          = array(
            'statu'       => 0,
            'update_time' => date("Y-m-d H:i:s")
        );
        $db->update($data, $where);
        $service = s("Task");
        $service->stopTask($id);
    }

    /**
     * 修改任务
     *
     * @param   $data   array
     * @param   $id     int
     */
    public function edit($data, $id)
    {
        $db            = db();
        $db->tableName = "task";
        $where         = array(
            'id' => $id
        );
        $db->update($data, $where);
        $service = s("Task");
        $type    = !empty($data['interval_time']) ? 1 : 0;
        $service->modifyTask($id, $type);
        $service->setTaskCache();
    }


    /**
     * 增加任务
     *
     * @param   $data   array
     *
     * @return int
     */
    public function add($data)
    {
        $db            = db();
        $db->tableName = "task";

        $id      = $db->insert($data);
        $service = s("Task");
        $type    = !empty($data['interval_time']) ? 1 : 0;
        $service->addTask($id, $type);
        $service->setTaskCache();

        return $id;
    }

    /**
     * 后台获取列表
     *
     * @param   $where   array
     * @param   $field   string
     * @param   $order   string
     * @param   $limit   string
     *
     * @return   array
     */
    public function getList($where, $field, $order, $limit)
    {
        $db            = db();
        $db->tableName = "task";
        $num           = $db->num($where);
        $result        = $db->findAll($where, $field, $order, $limit);

        return array(
            'num'  => $num,
            'list' => $result,
        );
    }

    /**
     * 获取  详细
     *
     * @param   $id      int
     * @param   $field   string
     *
     * @return array
     */
    public function getDetailById($id, $field = '*')
    {
        $db            = db();
        $db->tableName = "task";
        $where         = array(
            'id' => $id,
        );
        $result        = $db->find($where, $field);
        if (empty($result)) {
            return array();
        }

        return $result;
    }

    /**
     * 后台队列获取列表
     *
     */
    public function getTaskList($page, $start_time, $end_time)
    {
        $page_size = 20;
        $start     = ($page-1) * $page_size;
        $end       = $start + $page_size;

        $redis         = XyRedis::getRedis("task_redis");
        $task_zset_key = c('redis_key.task_zset');
        $task_hash_key = c('redis_key.version_info');
        $result        = $redis->zRange($task_zset_key, $start, $end);
        $num           = $redis->zCard($task_zset_key);
        $arr = $id_arr = $list = array();

        if (!empty($result)) {
            foreach ($result as $item) {
                if (strpos($item, '_') === false) {
                    continue;
                }

                list($id, $version, $time) = explode('_', $item);
                $arr[] = array(
                    'id'      => $id,
                    'version' => $version,
                    'time'    => $this->formatTime($time),
                );
                $id_arr[] = $id;
            }
        }
        $id_arr       = array_unique($id_arr);
        if (!empty($id_arr)) {
            $version_info = $redis->hMGet($task_hash_key, $id_arr);
            $task_info    = $this->batchGetTaskInfo($id_arr);
            foreach ($arr as $item) {
                if ($version_info[$item['id']]) {
                    list($status, $now_version) = explode('_', $version_info[$item['id']]);
                    if ($status == 0) {
                        $now_version = '已停止';
                    }
                } else {
                    $now_version = '已停止';
                }
                $item['now_version']   = $now_version;
                $item['task_system']   = $task_info[$item['id']]['task_system'];
                $item['cli_name']      = $task_info[$item['id']]['cli_name'];
                $item['cli_func']      = $task_info[$item['id']]['cli_func'];
                $item['cli_version']   = $task_info[$item['id']]['cli_version'];
                $item['content']       = $task_info[$item['id']]['content'];
                $item['interval_time'] = $task_info[$item['id']]['interval_time'];
                $item['once_num']      = $task_info[$item['id']]['once_num'];
                $list[] = $item;
            }
        }

        return array(
            'num'  => $num,
            'list' => $list,
        );
    }

    /**
     * 批量获取任务信息
     *
     * @param  $id_arr  array
     *
     * @return array
     */
    private function batchGetTaskInfo($id_arr)
    {
        if (empty($id_arr)) {
            return array();
        }
        $db     = db();
        $id_str = implode(',', $id_arr);
        $sql    = "SELECT id, task_system, cli_name, cli_func, cli_version, interval_time, once_num, content
                   FROM task
                   WHERE id IN ({$id_str})";
        $result = $db->query($sql);

        if (empty($result)) {
            return array();
        }
        $list = array();
        foreach ($result as $item) {
            $list[$item['id']] = $item;
        }

        return $list;
    }

    /**
     *
     * @param   $str    stirng
     *
     * @return string
     */
    private function formatTime($str) {
        return substr($str, 0, 4). '-'.substr($str,4,2).'-'.substr($str,6,2). ' ' .substr($str,8,2).
        ':'.substr($str, 10,2).':'.substr($str, 12);
    }
}