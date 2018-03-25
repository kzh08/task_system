<?php

/**
 * 任务Controller
 */
class TaskController extends CommonController
{
    /**
     * @var object 分页插件
     */
    public $paging;
    private $soa;

    public function init()
    {
        $this->paging = plugin('Paging');
        $this->soa    = SoaClient::getSoa('task', 'AdminTask');
    }

    /**
     * 列表
     */
    public function getListAction()
    {
        $page_size = c('page_size');
        $this->paging->limit = c('page_size');
        if (!empty($_REQUEST['limit'])) {
            $page_size = intval($_REQUEST['limit']);
        }
        $page      = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
        $start     = ($page - 1) * $page_size;
        $condition = "statu = 1";
        if (!empty($_REQUEST['startDate'])) {
            $condition .= " AND create_time >= '" . $_REQUEST['startDate'] . "'";
        }

        if (!empty($_REQUEST['endDate'])) {
            $condition .= " AND create_time <= '" . $_REQUEST['endDate'] . "'";
        }

        $where  = $this->paging->getWhere($condition);
        $fields = "*";
        $order  = "create_time DESC";
        $limit  = "{$start}, {$page_size}";

        $result = $this->soa->getList($where, $fields, $order, $limit);

        $paging = $this->paging->getPage($result['num']);

        $task      = array();
        $task[]    = array(
            '',
            '所有中心'
        );
        $task_list = c('task_list');
        if (!empty($task_list)) {

            foreach ($task_list as $key => $value) {
                $task[] = array(
                    $key,
                    $value,
                );
            }
        }
        $tool = array(
            'xtype' => 'select',
            'name'  => 'param1_task_system',
            'data'  => $task
        );

        $this->assign('result', $result['list']);
        $this->assign('tool', $tool);
        $this->assign('paging', $paging);
        $this->display();
    }

    /**
     * 添加任务
     */
    public function addAction()
    {
        if (!empty($_REQUEST['submitbtn'])) {
            if (empty($_REQUEST['run_start_time']) || empty($_REQUEST['cli_name']) || empty($_REQUEST['cli_func'])) {
                return;
            }

            if (!empty($_REQUEST['run_end_time'])) {
                if ($_REQUEST['run_end_time'] < $_REQUEST['run_start_time']) {
                    return;
                }
            }
            $data = array(
                'task_system'    => trim($_REQUEST['task_system']),
                'cli_name'       => trim($_REQUEST['cli_name']),
                'cli_version'    => trim($_REQUEST['cli_version']),
                'cli_func'       => trim($_REQUEST['cli_func']),
                'extra_param'    => trim($_REQUEST['extra_param']),
                'run_start_time' => strtotime(trim($_REQUEST['run_start_time'])),
                'run_end_time'   => strtotime(trim($_REQUEST['run_end_time'])),
                'interval_time'  => intval($_REQUEST['interval_time']),
                'once_num'       => intval($_REQUEST['once_num']),
                'allow_ip'       => trim($_REQUEST['allow_ip']),
                'content'        => trim($_REQUEST['content']),

            );
            $id   = $this->soa->add($data);
            LOG::i("[添加任务] 客服：{$this->adminId}:{$this->adminName} 增加了id:{$id} 的任务");
            echo 'success';
            exit;
        }
        $task_list = c('task_list');
        $version   = c('version');
        $this->assign('task_list', $task_list);
        $this->assign('version', $version);
        $this->assign('type', 'add');
        $this->display();
    }

    /**
     * 修改任务
     */
    public function editAction()
    {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            return;
        }
        if (!empty($_REQUEST['submitbtn'])) {
            if (empty($_REQUEST['run_start_time']) || empty($_REQUEST['cli_name']) || empty($_REQUEST['cli_func'])) {
                return;
            }

            $data = array(
                'task_system'    => trim($_REQUEST['task_system']),
                'cli_name'       => trim($_REQUEST['cli_name']),
                'cli_version'    => trim($_REQUEST['cli_version']),
                'cli_func'       => trim($_REQUEST['cli_func']),
                'extra_param'    => trim($_REQUEST['extra_param']),
                'run_start_time' => strtotime(trim($_REQUEST['run_start_time'])),
                'run_end_time'   => strtotime(trim($_REQUEST['run_end_time'])),
                'interval_time'  => intval($_REQUEST['interval_time']),
                'once_num'       => intval($_REQUEST['once_num']),
                'allow_ip'       => trim($_REQUEST['allow_ip']),
                'content'        => trim($_REQUEST['content']),
                'update_time'    => date("Y-m-d H:i:s"),
            );
            $id   = $this->soa->edit($data, $id);
            LOG::i("[修改任务] 客服：{$this->adminId}:{$this->adminName} 修改了id:{$id} 的任务");
            echo 'success';
            exit;
        }

        $result = $this->soa->getDetailById($id);

        $result['run_start_time'] = empty($result['run_start_time']) ? : date("Y-m-d H:i:s", $result['run_start_time']);
        $result['run_end_time']   = empty($result['run_end_time']) ? '' : date("Y-m-d H:i:s", $result['run_end_time']);

        $task_list = c('task_list');
        $version   = c('version');
        $this->assign('task_list', $task_list);
        $this->assign('version', $version);
        $this->assign('result', $result);
        $this->assign('type', 'edit');
        $this->display('Task/add');
    }

    /**
     * 删除动态
     */
    public function delAction()
    {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            echo 0;
            exit;
        }

        $this->soa->del($id);
        echo 1;
        LOG::i("[删除任务] 客服：{$this->adminId}:{$this->adminName} 删除了id:{$id} 的任务");
        exit;
    }

    /**
     * 任务列表
     */
    public function getTaskListAction()
    {
        $page   = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
        $start  = empty($_REQUEST['startDate']) ? 0 : strtotime($_REQUEST['startDate']);
        $end    = empty($_REQUEST['endDate']) ? 0 : strtotime($_REQUEST['endDate']);
        $result = $this->soa->getTaskList($page, $start, $end);
        $this->paging->limit = 20;
        $paging = $this->paging->getPage($result['num']);

        $this->assign('result', $result['list']);
        $this->assign('paging', $paging);
        $this->display();
    }
}