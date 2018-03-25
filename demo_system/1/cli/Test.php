<?php

/**
 * Test
 *
 */
class Test extends BaseService
{
    //传递过来的参数
    private $params;

    public function __construct()
    {
        parent::__construct();
        $this->params = json_decode(base64_decode($GLOBALS['X_G']['request'][5]), true);
    }

    /**
     * 执行要执行的cli
     */
    public function start()
    {
        LOG::w('success:'. var_export($this->params, true));
    }

}