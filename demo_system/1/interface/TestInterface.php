<?php

/**
 *  test
 */
class TestInterface extends BaseInterface
{
    /**
     * 检测人脸
     */
    public function test()
    {
        $service    = s('Test');
        $result     = $service->test2();

        if ($service->hasError()) {
            $this->respondFailure($service->getError());
        } else {
            $this->respondSuccess($result);
        }
    }

}
