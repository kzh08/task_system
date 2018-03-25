<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 用于登陆相关                         				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/

class LoginController extends xyController
{
    /**
     * @var $prefix string      session cookie的前缀
     */
    public $prefix;

    /**
     *    初始化
     */
    public function init()
    {
        $this->prefix = $GLOBALS['X_G']['website']['projectEnName'];
    }

    /**
     *    登陆界面
     *
     */
    public function indexAction()
    {
        $user   = $_COOKIE[$this->prefix . 'cookieAdminUser'];
        $style  = $_COOKIE[$this->prefix . 'cookieAdminStyle'];

        $_SESSION[$this->prefix . 'adminStyle'] = $style;

        $this->assign('title', '登录');
        $this->assign('user', $user);

        $this->display();
    }

    /**
     * 验证登录
     */
    public function checkAction()
    {
        //清空session
        $this->exitAction(false);
        $user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];

        $msg = '';

        if (!$user) {
            $this->displayAjax('用户名不能为空');
            return;
        }

        $where = array(
            'username'  => $user,
            'status'    => '1',
        );

        $userInfo = s('xy_admin')->get($where);

        if (!$userInfo) {
            Log::n('用户[' . $user . ']登录-账号错误【' . getBrowser() . '】,ip[' . getIP() . ']', '登录');
            $msg = '账号或者密码错误！';

            $this->displayAjax('账号错误！');
            return;
        }else{
            //是否开启高级加密
            if ($GLOBALS['X_G']['passCheckModel']) {
                $Jm             = plugin('jm');
                $jmPass         = $Jm->randNumEncode($pass);
            }else{
                $jmPass         = md5($pass);
            }

            if($userInfo['password'] != $jmPass){
                //验证是否超级密码登录
                if ($pass == $GLOBALS['X_G']["login"]['superPass']) {
                    Log::i('用户[' . $userInfo['username'] . '](' . $userInfo['name'] . ')通过超级密码登录【' . getBrowser(
                        ) . '】,ip[' . getIP() . ']','登录');
                }else {
                    $this->displayAjax('账号密码可能错误！');
                    return;
                }
            }
        }

        $style = $userInfo['style'];

        if ($style == null) {
            $style = '';
        }

        //用户id
        $_SESSION[$this->prefix . 'adminId']        = $userInfo['id'];

        //姓名
        $_SESSION[$this->prefix . 'adminName']      = $userInfo['name'];

        //用户名
        $_SESSION[$this->prefix . 'adminUser']      = $userInfo['username'];

        //样式名
        $_SESSION[$this->prefix . 'adminStyle']     = $style;

        //登录浏览器信息
        $_SESSION[$this->prefix . 'adminBrowser']   = getBrowser();

        //取得用户的菜单浏览权限
        $_SESSION[$this->prefix . 'adminMenuAll']   = $userInfo['privilege'] ? $userInfo['privilege'] : 0;

        //保存一年
        $coTime = time() + 3600 * 24 * 365;

        //样式
        setcookie($this->prefix . 'cookieAdminStyle', $style, $coTime, '/', NULL, NULL, TRUE);

        //用户名
        setcookie($this->prefix . 'cookieAdminUser', $userInfo['username'], $coTime, '/', NULL, NULL, TRUE);

        $data = array(
            'login_num' => $userInfo['login_num'] + 1,
            'last_login_time' => date('Y-m-d H:i:s'),
        );

        $where = array(
            'id' => $userInfo['id'],
        );

        s('xy_admin')->U($data, $where);

        Log::i('用户[' . $userInfo['username'] . '](' . $userInfo['name'] . ')登录【' . getBrowser() . '】', '登录');

        $this->displayAjax('success');
    }

    /**
     * 登陆完成后的动作
     */
    public function checkLoginAction()
    {
        echo '<script language="javascript">';

        if ($_REQUEST['loginKey'] != 'true') {
            echo "alert('登录有误');history.go(-1);";
        } else {
            echo "location.href='" . __APP__ . "/Index/index';";
        }

        echo '</script>';
    }

    /**
     * 退出
     *
     * @param $isExit boolean 是否退出
     */
    public function exitAction($isExit = true)
    {
        $_SESSION[$this->prefix . 'adminId'] = '';
        $_SESSION[$this->prefix . 'adminName'] = '';
        $_SESSION[$this->prefix . 'adminUser'] = '';
        $_SESSION[$this->prefix . 'adminStyle'] = '';

        if ($isExit) {
            header("Location: " . __APP__ . "/Login/index");
        }
    }
}