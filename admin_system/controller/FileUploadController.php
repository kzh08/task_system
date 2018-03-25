<?php

/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 文件上传实例代码                   				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-26       |
   +----------------------------------------------------------------------+
*/

class FileUploadController extends CommonController
{

    public function indexAction()
    {
        //error_reporting(E_ALL | E_STRICT);
        $upload = plugin('Upload');
        $upload->upload();
    }

    public function deleteAction()
    {
        $file = rawurldecode($_REQUEST['file']);
        $file = str_replace(__APP__ . "/", "", $file);
        if (unlink($file)) {
            exit("success");
        } else {
            exit("failed");
        }
    }
}