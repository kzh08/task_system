<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 控制器继承，判断用户是否有权限，是否登录				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/

class CommonController extends XyController
{

    public $adminId;

    public $adminName;

    public $adminUser;

    public $adminStyle;

    //默认隐藏的列名
    public $hideHeader = '';

    //session cookie的前缀
    public $prefix;


    public function _initialize()
    {
        //用于session、cookie等的前缀
        $this->prefix = $GLOBALS['X_G']['website']['projectEnName'];

        //没有登录就跳转
        $this->adminId = $this->getSession('adminId');

        if (!$this->adminId) {
            header("Location: " . __APP__ . "/Login/index");
        }

        $this->adminName    = $this->getSession('adminName');
        $this->adminUser    = $this->getSession('adminUser');
        $this->adminStyle   = $this->getSession('adminStyle');

        $this->assign('adminId', $this->adminId);
    }

    /**
     * 输出之前设置
     */
    public function beforeDisplay()
    {
        $this->assign('gridRow', $this->getHiddenUid());
    }

    /**
     * 获取session的值
     *
     * @param $name string 名称
     * @return string
     */
    protected function getSession($name)
    {
        $val = $_SESSION[$this->prefix . $name];

        return $val;
    }

    /**
     * 设置session的值
     *
     * @param $name     string 名称
     * @param $value    string 内容
     * @return string
     */
    protected function setSession($name, $value)
    {
        $_SESSION[$this->prefix . $name] = $value;
    }

    /**
     * 获取显示隐藏列字段的键值
     *
     * @param $menuId string 菜单ID
     * @return array
     */
    private function getHiddenUid($menuId = '')
    {
        if ($menuId == '') {
            $menuId = $_REQUEST['menuId'];
        }

        //获取隐藏列
        $cookie = $_COOKIE[$this->prefix . '_menu_hide_' . $menuId . '_' . $this->adminId];

        $tmpArray = array();

        if ($cookie == '' || !$cookie || empty($cookie)) {
            $cookie = $this->hideHeader;
        }

        if ($cookie != '') {
            $tmpArray = explode(",", $cookie);
        }

        return $tmpArray;
    }
}