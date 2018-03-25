<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 系统管理相关                         				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-09-01       |
   +----------------------------------------------------------------------+
*/

class SystemController extends CommonController
{

    /**
     * @var object 分页插件
     */
    public $paging;

    /**
     * @var string 数据库表前缀
     */
    public $dbPrefix;

    public function init()
    {
        $this->paging = plugin('Paging');

        $this->$dbPrefix = $GLOBALS['X_G']['db']['prefixOld'];
    }

    /**
     * 显示菜单列表界面
     */
    public function menuAction()
    {
        $rows = s('menu')->getMenuTree();

        $this->assign('tree', $rows);
        $this->display();
    }


    /**
     * 取得菜单详情
     */
    public function getMenuInfoAction()
    {
        $id = (int)$_REQUEST['id'];

        s('menu')->db->tableName = 'xy_menu';

        $result = s('menu')->get(
            array(
                'id' => $id
            )
        );

        echo json_encode($result);
    }

    /**
     * 保存菜单修改
     */
    public function saveMenuAction()
    {
        $id     = (int)$_REQUEST['idPost'];
        $pid    = (int)$_REQUEST['pidPost'];
        $num    = $_REQUEST['numPost'];

        if ($id != 0 && $id == $pid) {
            echo '上级栏目ID不能等于本身';
            return;
        }

        s('menu')->db->tableName = 'xy_menu';

        //取得当前ID的等级
        $level  = s('menu')->getField(
            array(
                'id' => $pid
            ),
            'level'
        );

        $arr = array(
            'level'     => $level + 1, //级别
            'pid'       => $pid,
            'name'      => $_REQUEST['namePost'],
            'url'       => $_REQUEST['urlPost'],
            'icons'     => $_REQUEST['iconsPost'],
            'color'     => $_REQUEST['colorPost'],
            'status'    => $_REQUEST['statusPost'],
            'ispir'     => $_REQUEST['ispirPost'],
            'sort'      => $_REQUEST['sortPost'],
            'num'       => $num,
            'indate'    => date('Y-m-d H:i:s')
        );

        if ($id == 0) {
            s('menu')->I($arr);
        } else {
            $where  = array(
                'id' => $id
            );

            s('menu')->U($arr, $where);
        }

        echo 'success';
    }

    /**
     * 删除菜单
     */
    public function delMenuAction()
    {
        $id     = $_REQUEST['id'];

        s('menu')->db->tableName = 'xy_menu';

        //是否有下一级ID
        $count = s('menu')->getNum(
            array(
                'pid' => $id
            )
        );

        if ($count > 0) {
            echo '存在下级栏目不能删除！';
            return;
        }

        s('menu')->D(
            array(
                'id' => $id
            )
        );

        echo $msg = 'success';
    }

    /**
     * 人员列表
     */
    public function userAction()
    {
        $userArr = $this->paging->getLimit('xy_admin', 'id is not null');

        $this->assign('grid', $userArr['rows']);
        $this->assign('paging', $userArr['paging']);
        $this->display();
    }

    /**
     * 显示新增或者修改用户的界面
     */
    public function editUserAction()
    {
        $id = (int)$_REQUEST['id'];

        if($id != 0){
            $arr = s('xy_admin')->get(
                array(
                    'id' => $id
                )
            );

            $this->assign('da', $arr);
        }

        $this->assign('id', $id);
        $this->display();
    }

    /**
     * 保存新增或者修改
     */
    public function saveUserAction()
    {
        $id         = (int)$_REQUEST['idPost'];
        $username   = $_REQUEST['usernamePost'];
        $password   = $_REQUEST['passwordPost'];

        if ($id == 0 && $password == '') {
            $password = '123456'; //设置默认密码
        }

        if ($username == '') {
            echo '用户名不能为空';
            return;
        }

        //是否已有相关用户名存在
        $count = s('xy_admin')->getNum("username='" . $username . "' and id<>'" . $id . "'");

        if ($count > 0) {
            echo '用户名:' . $username . ' 已存在';
            return;
        }

        $userArr = array(
            'username'  => $username,
            'name'      => $_REQUEST['namePost'],
            'gender'    => $_REQUEST['genderPost'],
            'tel'       => $_REQUEST['telPost'],
            'email'     => $_REQUEST['emailPost'],
            'status'    => $_REQUEST['statusPost']
        );

        $where = '';

        if ($id != 0) {
            $where = array(
                'id' => $id
            );
        }

        if ($password) {
            if ($GLOBALS['X_G']['passCheckModel']) {
                $Jm                     = plugin('Jm');
                $userArr['password']    = $Jm->randNum($password);
            }else{
                $userArr['password']    = md5($password);
            }
        }

        if ($where == "") {
            $userArr['indate']      = date("Y-m-d H:i:s");
            $userArr['add_user']    = $this->adminName;

            s('xy_admin')->I($userArr);
        } else {
            s('xy_admin')->U($userArr, $where);
        }

        echo $msg = 'success';
    }

    /**
     * 删除用户
     */
    public function delUserAction()
    {
        $id = (int)$_REQUEST['id'];

        s('xy_admin')->D(
            array(
                'id' => $id
            )
        );

        echo 'success';
    }


    /**
     * 修改密码页面
     */
    public function passAction()
    {
        $this->assign('id', $this->adminId);

        $this->display();
    }

    /**
     * 保存密码
     */
    public function savePassAction()
    {
        //引入加密插件
        $Jm         = plugin('jm');

        $id         = (int)$_REQUEST['idPost'];
        $oldPass    = $_REQUEST['oldPassPost'];
        $newPass    = $_REQUEST['newPassPost'];

        if ($newPass == '') {
            echo '新密码不能为空';

            return;
        }

        s('admin')->db->tableName = 'xy_admin';

        $oldPassDb = s("admin")->getField("id='$id'", "password");

        //加密模式
        if ($GLOBALS['X_G']['passCheckModel'] && $oldPassDb != $Jm->randNumEncode($oldPass)) {
            echo '旧密码不正确';

            return;
        }elseif (!$GLOBALS['X_G']['passCheckModel'] && $oldPassDb != md5($oldPass)) {
            echo '旧密码不正确';

            return;
        }

        //加密模式
        if($GLOBALS['X_G']['passCheckModel']){
            $updateData = array(
                'password' => $Jm->randNumEncode($newPass)
            );
        }else{
            $updateData = array(
                'password' => md5($newPass)
            );
        }

        $where = array(
            'id' => $id
        );

        s("admin")->U($updateData, $where);

        echo 'success';
    }

    /**
     * 显示相关权限
     * todo:权限相关整理
     */
    public function extentAction()
    {
        //权限类型
        $type       = $_REQUEST['type'];

        //我的菜单权限
        $privilege  = $_SESSION[$this->prefix . 'adminMenuAll'];

        //权限呈递分配
        $where = '';

        if ($type == 'uu') {
            if ($privilege != '-1') {
                $where = ' and id in(' . $privilege . ')';
            }
        }

        $menuRows = s('menu')->getmenuTree(0, ' and status=1 and ispir = 1 ' . $where . '');
        $userRows = s('xy_admin')->getAll("status=1 and privilege != -1");

        $this->assign('tree', $menuRows);
        $this->assign('userArr', $userRows);
        $this->assign('type', $type);
        $this->display();
    }

    /**
     * 取得某用户的权限
     */
    public function getExtentAction()
    {
        $type   = $_REQUEST['type'];
        $id     = (int)$_REQUEST['id'];

        $s = '[0]';

        $userInfo = s('xy_admin')->get(
            array(
                'id' => $id
            )
        );

        //权限查看的
        if ($type == 'view') {
            $s .= s('menu')->getuserext($id, 'all');
        } else {
            $s = '[' . str_replace(',','],[',$userInfo['privilege']) . ']';
        }

        echo $s;
    }

    /**
     * 保存权限
     */
    public function saveExtentAction()
    {
        $id         = (int)$_REQUEST['id'];
//        $type       = $_REQUEST['type'];
        $checkAId   = $_REQUEST['checkAId'];

        if ($checkAId == '') {
            $checkAId = '0';
        }

        s('menu')->saveExtent($id, $checkAId);

        echo 'success';
    }

    /*
     * 查看日志
    */
    public function logAction()
    {
        $arr = $this->paging->getLimit('xy_log', 'id is not null', 'id desc');

        $this->assign('grid', $arr);
        $this->assign('paging', $arr['paging']);
        $this->display();
    }

    //-------------样式主题管理--------------
    public function styleAction()
    {
        $this->assign('adminstyle', $this->adminstyle); //读取当前样式
        $this->display();
    }

    //切换样式
    public function changecolorAction()
    {
        $mdir = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
        $color = $_REQUEST['color'];
        $filename = '' . $mdir . '' . __PUBLIC__ . '/css/style' . $color . '.css';
        $cont = file_get_contents(
            'http://' . $_SERVER['HTTP_HOST'] . '' . __PUBLIC__ . '/css/style.php?color=' . $color . '&rnd=' . time(
            ) . ''
        );
        @unlink($filename);
        $fh = fopen($filename, "a");
        fwrite($fh, $cont);
        fclose($fh);
    }

    /**
     * 保存相关样式
     */
    public function saveStyleAction()
    {
        $color      = $_REQUEST['color'];

        $updateData = array(
            'style' => $color
        );

        $where = array(
            'id' => $this->adminId
        );

        s('xy_admin')->U($updateData, $where);

        $_SESSION[$this->prefix . 'adminStyle'] = $color;

        $coTime = time() + 3600 * 24 * 365; //保存一年

        setcookie($this->prefix . 'cookieAdminStyle', $color, $coTime, '/', NULL, NULL, TRUE);
    }

    //--------------编辑器使用的上传图片
    public function xyEditerAction()
    {
        $arr = array();
        if ($_REQUEST['upload'] == 'true') {
            $upload = plugin('Upload');
            $dir = $upload->mkDir();
            $upload->uploadback = true;
            foreach ($_FILES AS $k => $file) {
                $arr = $upload->up($file, $dir);
            }

        }
        $this->assign('files', json_encode($arr));
        $this->display();
    }


    /**
     * 单元格直接编辑保存功能
     * todo:找出项目中相关引用位置，然后重构
     */
    public function saveEditAbleAction()
    {
        $table      = $_REQUEST['table'];
        $fields     = $_REQUEST['fields'];
        $value      = $_REQUEST['newvalue'];
        $canid      = $_REQUEST['id'];
        $keyfields  = $_REQUEST['keyfields'];

        if (!$keyfields) {
            $keyfields = 'id';
        }

        s($table)->U(
            array($fields => $value),
            array($keyfields => $canid)
        );

        echo 'success';
    }
}