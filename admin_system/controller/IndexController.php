<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 入口控制器                          				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/

class IndexController extends CommonController
{

    public function indexAction()
    {
        $where = '';

        //我的菜单权限
        $menuPrivilege = $this->getSession('adminMenuAll');

        if ($menuPrivilege != '-1' && !empty($menuPrivilege)) {
            $where =  'and id in (' . $menuPrivilege . ')';
        }

        //顶级菜单读取
        $topMenu = s('xy_menu')->getAll("pid=0 and status=1 " . $where, 'name,id,icons', 'sort');

        $where = array(
            'id' => $this->adminId,
        );

        //获取用户信息
        $urs = s('xy_admin')->get($where, 'login_num,face,last_login_time');

        $this->assign('name', $this->adminName);
        $this->assign('topMenu', $topMenu);
        $this->assign('urs', $urs);
        $this->assign('ip', getIP());

        $this->display();
    }

    /**
     * 获取菜单
     */
    public function getMenuAction()
    {
        $pid    = (int)$_REQUEST['pid'];

        $menu   = s('menu')->getMenu($pid);

        echo json_encode($menu);
    }

    /**
     * 记录使用菜单
     */
    public function clickMenuAction()
    {
        $menuId     = (int)$_REQUEST['menuId'];

        $menuName   = s('xy_menu')->getField(
            array(
                'id' => $menuId
            ),
            'name'
        );

        Log::i(
            $this->adminName . '[' . $this->adminUser . ']' . '用户点击菜单[' . $menuId . ']-' . $menuName . ',ip[' . getIP() . ']',
            '菜单使用'
        );

        echo $menuId;
    }

    /**
     * 返回首页
     */
    public function homeAction()
    {
        $this->display();
    }
}