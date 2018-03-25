<?php

/**
 *
 */
class TestService extends BaseService
{
    /**
     * 推入一个中心demo
     *
     * @return array
     */
    public function test()
    {
        $center_name = 'baby';
        $task_name   = 'test';
        $time        = 1445928217;
        $version     = 1;
        $param       = array(
            'param1' => 'test2',
            'param2' => 'test2',
        );

        $soa    = SoaClient::getSoa('task', 'Task');
        $result = $soa->pushToTask($center_name, $task_name, $time, $version, $param);
        if ($soa->hasError()) {
            return $this->setError($soa->getErrorCode(), $soa->getErrorMsg());
        }
        return $result;
    }

    /**
     * 推入多个中心demo
     *
     * @return array
     */
    public function test2()
    {
        $center_name = array(
            'baby',
            'account',
        );
        $task_name   = array(
            'test',
            'test',
        );
        $time        = array(
            1445928217,
            1445928217,
        );
        $version     = array(
            1,
            1,
        );
        $param       = array(
            array(
              'param1' => 'test1',
            ),
            array(
              'param1' => 'test2',
            ),
        );

        $soa    = SoaClient::getSoa('task', 'Task');
        $result = $soa->batchPushToTask($center_name, $task_name, $time, $version, $param);

        if ($soa->hasError()) {
            return $this->setError($soa->getErrorCode(), $soa->getErrorMsg());
        }
        return $result;
    }
}