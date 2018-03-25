<?php

class TaskService extends BaseService
{
    /**
     * 直接写入mq
     *
     * @param      $name   string    队列名称
     * @param      $data   array     写入队列的数据
     *
     * @return array
     */
    public function pushTaskMq($name, $data)
    {
        if (empty($name) || empty($data)) {
            return $this->setError('params_is_no_complete');
        }
        $soa = SoaClient::getSoa('tqmq', 'operation');
        $soa->push($name, json_encode($data));
        if ($soa->hasError()) {
            Log::w('加入任务队列' . $name . '失败,任务系统:' . $data['task_system'] . '当前时间:' . time() . '错误代码:' . $soa->getErrorCode());

            return $this->setError($soa->getErrorCode());
        }

        return true;
    }

    /**
     * 将延时任务推到 任务系统中 (供其他系统调用)
     *
     * @param  $center_name  string         队列中心名称
     * @param  $task_name    string         任务名称
     * @param  $time         int            要执行的时间戳
     * @param  $version      int            版本
     * @param  $param        string|array   参数信息  (如果是数组 建议先json_encode 以后台统一)
     *
     * soa_config配置需要增加相关地址
     *       'soa_client'  => array(
     *          'task'    =>  'http://10.10.10.50:831/v1/Soa/',
     *         ),
     *
     *  调用方法为
     *  $center_name   = "account"; //哪个中心
     *  $task_name     = "register"; //执行的事件
     *  $time          = time();//执行时间
     *  $version       = 1;
     *  $param         = '{"uid":112}';
     *
     *  $soa    = SoaClient::getSoa('task', 'Task');
     *  $result = $soa->pushToTask($center_name, $task_name, $time, $version, $param);
     *
     *
     * @return bool|array
     */
    public function pushToTask($center_name, $task_name, $time = 0, $version, $param = '')
    {
        $version = intval($version);
        if (empty($center_name) || empty($task_name) || empty($version)) {
            return $this->setError('param_not_true');
        }
        $redis    = XyRedis::getRedis("task_redis");
        $zset_key = c('redis_key.task_zset');
        $hash_key = c('redis_key.hash_info');
        $data     = array();

        $data['cli_name']      = "SoaTaskHandle";
        $data['cli_func']      = "start";
        $data['cli_version']   = $version;
        $param                 = array(
                    'task_name' => $task_name,
                    'param'     => $param,
        );
        $data['extra_param']   = base64_encode(json_encode($param));

        //限定 脚本只执行一次
        $data['task_system']   = $center_name;
        //以下参数写死  暂时不开放给soa调用 防止调用出错
        $data['interval_time'] = 0;
        $data['once_num']      = 0;
        $data['allow_ip']      = '';
        $member                = $this->getSoaMember($center_name);
        $info                  = json_encode($data);
        $redis->zAdd($zset_key, $time, $member);
        $redis->hSet($hash_key, $member, $info);

        return true;
    }

    /**
     * 批量 将延时任务推到 任务系统中 (供其他系统调用)
     *
     * @param  $center_name  array      队列中心名称
     * @param  $task_name    array      任务名称
     * @param  $time         array      要执行的时间戳
     * @param  $version      array      版本
     * @param  $param        array      参数信息
     *
     * soa_config配置需要增加相关地址
     *       'soa_client'  => array(
     *          'task'    =>  'http://10.10.10.50:831/v1/Soa/',
     *         ),
     *
     *  调用方法为
     *  $center_name   = array(
     *                        'account',
     *                        'baby',
     *                  ); //哪个中心
     *  $task_name     = array(
     *                       'register',
     *                       'register',
     *                  ); //执行的事件
     *  $time          = array(
     *                        1445909753,
     *                        1445909754,
     *                  );//执行时间
     *  $version       = array(
     *                        1,
     *                        1,
     *                  );
     *  $param         = array(
     *                        '{"uid":112}',
     *                        '{"uid":112}',
     *                  );
     *
     *  $soa    = SoaClient::getSoa('task', 'Task');
     *  $result = $soa->batchPushToTask($center_name, $task_name, $time, $version, $param);
     *
     *
     * @return bool|array
     */
    public function batchPushToTask($center_name, $task_name, $time = 0, $version, $param)
    {
        if (empty($center_name) || empty($task_name) || empty($version)) {
            return $this->setError('param_not_true');
        }

        $redis    = XyRedis::getRedis("task_redis");
        $zset_key = c('redis_key.task_zset');
        $hash_key = c('redis_key.hash_info');
        $hashKeys = array();

        for ($i = 0; $i < count($center_name); $i++) {
            $data                  = array();
            $data['cli_name']      = "SoaTaskHandle";
            $data['cli_func']      = "start";
            $data['cli_version']   = $version[$i];
            $extra_param           = array(
                'task_name' => $task_name[$i],
                'param'     => $param[$i],
            );
            $data['extra_param']   = base64_encode(json_encode($extra_param));
            //限定 脚本只执行一次
            $data['task_system']   = $center_name[$i];
            //以下参数写死  暂时不开放给soa调用 防止调用出错
            $data['interval_time'] = 0;
            $data['once_num']      = 0;
            $data['allow_ip']      = '';
            $member                = $this->getSoaMember($center_name[i]);
            $info                  = json_encode($data);
            $hashKeys[$member]     = $info;
            $redis->zAdd($zset_key, $time, $member);
        }
        $redis->hMset($hash_key, $hashKeys);

        return true;
    }

    /**
     * 获取通过soa 调用的 member
     *
     * @param  $name  string
     *
     * @return string
     */
    private function getSoaMember($name)
    {
        $str = $name . uniqid() . mt_rand(0, 9999);

        return substr(md5($str), 0, 16);
    }

    /* ====      后台相关start          === */

    /**
     * 添加任务
     *
     * @param    $id     int     任务id
     * @param    $type   int     任务类型  1：循环任务  0：一次性任务
     *
     * @return bool
     */
    public function addTask($id, $type)
    {
        $redis            = XyRedis::getRedis("task_redis");
        $version_info_key = c('redis_key.version_info');
        $status           = 1;
        $version          = 1;
        $value            = $status . "_" . $version;
        $redis->hSet($version_info_key, $id, $value);
        if ($type == 0) {
            $this->setOnceTask($id, $version);
        } else {
            $this->setLoopTask($id, $version);
        }
    }

    /**
     * 停止任务 后台删除时调用
     *
     * @param    $id     int     任务id
     *
     *
     * @return bool
     */
    public function stopTask($id)
    {
        $redis            = XyRedis::getRedis("task_redis");
        $version_info_key = c('redis_key.version_info');
        $redis->hDel($version_info_key, $id);
    }

    /**
     * 修改任务 后台修改时调用
     *
     * @param    $id     int     任务id
     * @param    $type   int     任务类型  1：循环任务  0：一次性任务
     *
     * @return bool
     */
    public function modifyTask($id, $type)
    {
        $redis = XyRedis::getRedis("task_redis");

        $version_info_key = c('redis_key.version_info');
        $info             = $redis->hGet($version_info_key, $id);

        if (!empty($info)) {
            list($status, $version) = explode('_', $info);
            $version = intval($version);
            $version++;
        } else {
            $status  = 1;
            $version = 1;
        }
        $value = $status . "_" . $version;
        $redis->hSet($version_info_key, $id, $value);

        if ($type == 0) {
            $this->setOnceTask($id, $version);
        } else {
            $this->setLoopTask($id, $version);
        }
    }


    /*=============  定时任务 start  ==============*/

    /**
     * 任务列表写入缓存
     */
    public function setTaskCache()
    {
        $this->db      = new Db();
        $redis         = XyRedis::getRedis("task_redis");
        $task_zset_key = c('redis_key.task_zset');
        $get_interval  = c('get_interval'); //每次获取多少秒内要执行的脚本内容
        $pre_time      = c('pre_time'); //脚本执行时间
        $start_time    = time() + $pre_time;
        $end_time      = $start_time + $get_interval;

        //获取在时间start_time ~ end_time内只执行一次的任务
        $sql = "SELECT * FROM task
                WHERE statu = 1
                AND interval_time = 0
                AND run_start_time BETWEEN {$start_time} AND {$end_time}";

        $once_result = $this->db->query($sql);
        if (empty($once_result)) {
            $once_result = array();
        }

        //获取在时间start_time ~ end_time内要执行不只一次的任务  1 永久执行的  2 一段时间内执行的
        $sql = "SELECT * FROM task
                WHERE statu = 1
                AND interval_time > 0
                AND run_start_time <= {$end_time}
                AND ( run_end_time = 0
                      OR
                      run_end_time >= {$start_time}
                    )";

        $loop_result = $this->db->query($sql);
        if (empty($loop_result)) {
            $loop_result = array();
        }
        //提取任务区间内要执行的 循环任务
        $interval_list = $this->filterLoopTask($loop_result, $start_time, $end_time);
        //合并一次任务和 循环任务
        $all_list      = array_merge($interval_list, $once_result);
        //获取已经在redis里的信息
        $redis_list    = $redis->zRange($task_zset_key, 0, -1);
        //获取版本信息
        $version_info  = $this->getVersionInfo($redis, $all_list);
        //将任务推到队列
        $this->pushBgDataToTask($redis, $all_list, $redis_list, $version_info);
    }

    /**
     * 设置一次性任务到任务里
     *
     * @param   $id        int    任务id
     * @param   $version   int    版本
     */
    private function setOnceTask($id, $version)
    {
        $this->db = new Db();
        $redis    = XyRedis::getRedis("task_redis");

        $get_interval  = c('get_interval'); //每次获取多少秒内要执行的脚本内容
        $start_time    = time();
        $end_time      = $start_time + $get_interval;

        $sql = "SELECT * FROM task
                WHERE statu = 1
                AND  id = {$id}
                AND interval_time = 0
                AND run_start_time BETWEEN {$start_time} AND {$end_time}";

        $all_list = $this->db->query($sql);
        if (empty($all_list)) {
            return;
        }
        $redis_list = $version_info = array();
        $version_info[$id] = $version;
        $this->pushBgDataToTask($redis, $all_list, $redis_list, $version_info);
    }

    /**
     * 设置循环任务到任务里
     *
     * @param   $id        int   任务id
     * @param   $version   int    版本
     */
    private function setLoopTask($id, $version)
    {
        $this->db = new Db();
        $redis    = XyRedis::getRedis("task_redis");

        $get_interval  = c('get_interval'); //每次获取多少秒内要执行的脚本内容
        $start_time    = time();
        $end_time      = $start_time + $get_interval;

        $sql = "SELECT * FROM task
                WHERE statu = 1
                AND id={$id}
                AND interval_time > 0
                AND run_start_time <= {$end_time}
                AND ( run_end_time = 0
                      OR
                      run_end_time >= {$start_time}
                    )";

        $loop_result = $this->db->query($sql);
        if (empty($loop_result)) {
            return ;
        }

        //提取任务区间内要执行的 循环任务
        $interval_list = $this->filterLoopTask($loop_result, $start_time, $end_time);
        if (empty($interval_list)) {
            return ;
        }

        $redis_list = $version_info = array();
        $version_info[$id] = $version;
        $this->pushBgDataToTask($redis, $interval_list, $redis_list, $version_info);
    }

    /**
     * 提取任务区间内要执行的 循环任务
     *
     * @param   $loop_result   array   循环任务数组
     * @param   $start_time    int     开始时间
     * @param   $end_time      int     结束时间
     *
     * @return  array
     */
    private function filterLoopTask($loop_result, $start_time, $end_time)
    {
        $interval_list = array();
        if (empty($loop_result)) {
            return array();
        }
        foreach ($loop_result as $value) {
            //取得时间段内任务执行的时间点
            //任务在时间段内执行的次数,有可能会出现多次任务
            $interval_time = $value['interval_time'] * $value['once_num'];
            if ($interval_time <= 0) { //如果循环脚本执行间隔时间为0 说明错误数据不进入缓存
                continue;
            }
            $times = ceil(($end_time - $start_time) / $interval_time);

            if ($value['run_start_time'] < $start_time) {
                //当任务开始时间 小于当前执行时间 时
                $remainder       = ($start_time - $value['run_start_time']) % $interval_time;
                $task_start_time = $start_time - $remainder;
            } else {
                //当任务开始时间 大于等于当前执行时间
                $task_start_time = $value['run_start_time'];
            }

            for ($i = 0; $i <= $times; $i++) {
                $list                   = $value;
                $list['run_start_time'] = $task_start_time + $interval_time * $i;
                //处于时间段内
                if ($list['run_start_time'] >= $start_time && $list['run_start_time'] <= $end_time
                    && $list['run_start_time'] <= $value['run_end_time']) {
                    $interval_list[] = $list;
                }
            }
        }

        return $interval_list;
    }

    /**
     * 将从数据库里取出来的任务推到  任务里
     *
     * @param   $all_list    array   后台取出来的任务
     */
    private function pushBgDataToTask($redis, $all_list, $redis_list, $version_info)
    {
        if (empty($all_list)) {
            return;
        }
        $task_zset_key = c('redis_key.task_zset');
        $hash_info_key = c('redis_key.hash_info');
        $hashKeys      = array();
        foreach ($all_list as $item) {
            $version = $version_info[$item['id']];
            //如果没有该id的版本信息或者版本为0说明已经被删除 不进入队列
            if (empty($version)) {
                continue;
            }
            $member = $this->createMember($item['run_start_time'], $item['id'], $version);
            //不在redis的集合里则加入
            if (!in_array($member, $redis_list, true)) {
                $value             = json_encode($item);
                $hashKeys[$member] = $value;
                $redis->zAdd($task_zset_key, $item['run_start_time'], $member);
            }
        }

        if (!empty($hashKeys)) {
            $redis->hMset($hash_info_key, $hashKeys);
        }
    }

    /**
     * 对要进入 task 任务缓存的数据 获取 它的版本信息
     *
     * @param  $redis   object
     * @param  $task    array
     *
     * @return array
     */
    private function getVersionInfo($redis, $task)
    {
        $version_info_key = c('redis_key.version_info');
        $id_arr           = array();
        if (empty($task)) {
            return array();
        }
        foreach ($task as $item) {
            $id_arr[] = $item['id'];
        }
        $id_arr       = array_unique($id_arr);
        $version_info = $redis->hMget($version_info_key, $id_arr);

        if (empty($version_info)) {
            return array();
        }

        foreach ($version_info as $key => $value) {
            if (empty($value)) { //没有版本信息说明已经被删除
                $version_info[$key] = 0;
            } else {
                list($status, $version) = explode('_', $value);
                if ($status == 0) { //状态为0说明已删除 版本信息设为0
                    $version_info[$key] = 0;
                } else {
                    $version_info[$key] = $version;
                }
            }
        }

        return $version_info;
    }

    /**
     * 生成唯一的值
     *
     * @param    $time       int
     * @param    $id         int
     * @param    $version    int
     *
     * @return string
     */
    private function createMember($time, $id, $version)
    {
        return $id . "_" . $version . '_' . date("YmdHis", $time);
    }

    /**
     * 将任务写入消息队列
     */
    public function setTaskMq()
    {
        $redis         = XyRedis::getRedis("task_redis");
        $soa           = SoaClient::getSoa('tqmq', 'operation');
        $zset_key      = c('redis_key.task_zset');
        $hash_info_key = c('redis_key.hash_info');

        //找出缓存中要执行的任务
        $now  = time();
        $task = $redis->zRangeByScore($zset_key, 0, $now);
        if (!empty($task)) {
            $task = $this->removeUselessTask($redis, $task);
            $info = $redis->hMGet($hash_info_key, $task);
            if (!empty($info)) {
                foreach ($info as $key => $value) {
                    //推入mq
                    $task_info   = json_decode($value, true);
                    $extra_param = $task_info['extra_param'] ? base64_encode(json_encode($task_info['extra_param'])) : '';
                    $tqmq_data = array(
                        'task_system'   => $task_info['task_system'],
                        'cli_name'      => $task_info['cli_name'],
                        'cli_func'      => $task_info['cli_func'],
                        'cli_version'   => $task_info['cli_version'],
                        'interval_time' => $task_info['interval_time'],
                        'once_num'      => $task_info['once_num'],
                        'extra_param'   => $extra_param,
                        'allow_ip'      => $task_info['allow_ip'],
                    );
                    $name      = $task_info['task_system'] . 'TaskList';
                    $soa->push($name, json_encode($tqmq_data));

                    if ($soa->hasError) {
                        Log::w('加入任务队列失败,任务ID:' . $task_info['id'] . ' 任务系统:' . $task_info['task_system'] . '当前时间:' . time() . '错误代码:' . $soa->getErrorCode());
                    } else {
                        $this->removeOneCache($redis, $key);
                    }
                }
            }
        }
    }

    /**
     * 移除不要执行的数据 (删除 或者低版本的数据)
     *
     * @param   $redis    object
     * @param   $task     array
     *
     * @return arrray
     */
    private function removeUselessTask($redis, $task)
    {
        $version_info_key = c('redis_key.version_info');
        $useless          = $soa_task = $id_arr = $id_info = $task_version_info = array();
        if (empty($task)) {
            return $task;
        }

        //区分出后台添加的任务  还是 soa  调用来的任务
        foreach ($task as $item) {
            //后台添加进入的任务 key 含有 "_"符号
            if (strpos($item, '_') !== false) {
                list($id, $version) = explode('_', $item);
                $id_arr[]       = $id;
                $id_info[$item] = $version;
            } else {
                $soa_task[] = $item;
            }
        }
        $id_arr = array_unique($id_arr);
        if (empty($id_arr)) { //如果没有后台任务
            return $soa_task;
        }

        //获取任务的版本信息和状态
        $info = $redis->hMget($version_info_key, $id_arr);
        if (empty($info)) { //如果没有后台版本信息
            return $soa_task;
        }
        foreach ($info as $id => $value) {
            if (empty($value)) { //如果没有信息 说明已被删除
                $task_version_info[$id] = array(
                    'status'  => 0,
                    'version' => 0,
                );
            } else {
                list($status, $version) = explode('_', $value);
                $task_version_info[$id] = array(
                    'status'  => $status,
                    'version' => $version,
                );
            }
        }
        if (empty($task_version_info)) { //任务的版本信息和状态
            return $soa_task;
        }

        foreach ($task as $item) {
            //后台添加进入的任务 key 含有 "_"符号
            if (strpos($item, '_') === false) {
                continue;
            }
            list($id, $version) = explode('_', $item);
            //如果任务已被删除 或者版本号不是最新的  则去删除该任务
            if (empty($task_version_info[$id]) || $task_version_info[$id]['status'] == 0 || $version < $task_version_info[$id]['version']) {
                //删除redis里的任务信息
                $this->removeOneCache($redis, $item);
                $useless[] = $item;
                Log::n("remove task " . $item . " success");
            }
        }

        return array_diff($task, $useless);
    }

    /**
     * 删除一个固定时间的 的redis任务
     *
     * @param   $redis   object
     * @param   $key     string
     *
     */
    private function removeOneCache($redis, $key)
    {
        $zset_key      = c('redis_key.task_zset');
        $hash_info_key = c('redis_key.hash_info');
        $redis->zRem($zset_key, $key);
        $redis->hDel($hash_info_key, $key);
    }
}