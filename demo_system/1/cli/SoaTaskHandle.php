<?php

/**
 * soa  调用任务系统
 *
 */
class SoaTaskHandle extends BaseService
{
    /**
     * 执行要执行的cli
     */
    public function start()
    {
        $params = json_decode(base64_decode($GLOBALS['X_G']['request'][5]), true);
        if (empty($params) || empty($params['task_name'])) {
            Log::e("soa 调用任务系统参数传递出错 param:".var_export($params, true));
            return ;
        }

        $task_name = $params['task_name'];
        $param     = empty($params['param']) ? '' : base64_encode(json_encode($params['param']));
        $cliKey    = c("clikey");
        $version   = $class = $action = '';
        switch (strval($task_name)) {
            case 'test':
                    $version = 1;
                    $class   = 'Test';//cli名称
                    $action  = 'start';//cli方法名称
                break;

            default :
                Log::e("soa 调用任务系统  任务名称未定义 task_name:" . $task_name);
                exit;
        }

        $shell  = "nohup php ../cli.php {$cliKey} v{$version} {$class} {$action} {$param} &";
        shell_exec($shell);
    }

}