<?php

/**
 *    rockupload 上传，使用html5上传技术(IE8,IE7,IE6下是不支持的哦)
 *    createname:chenxihu
 *    createdate:2014-03-28 16:51:00
 */
class XyuploadController extends CommonController
{


    public function indexAction()
    {
        $this->assign('callback', $this->request('callback'));
        $this->assign('maxup', $this->request('maxup', '0'));
        $this->assign('uptype', $this->request('uptype', '*'));
        $this->assign('maxsize', $this->request('maxsize', '50')); //单文件最大上传
        $this->assign('bodytitle', $this->request('title', '文件上传'));
        $this->display('System/xyupload'); //输出模版
    }

    /**
     * 处理传过来的数据
     *
     * @author kzh
     *
     */
    private function request($key, $val = '')
    {
        if (isset($key)) {
            $key = trim($_REQUEST[$key]);
        } else {
            $key = $val;
        }

        return $key;
    }

    public function uploadtestAction()
    {
        $this->display('System/uploadtest');
    }

    /**
     * 上传图片到七牛
     *
     * @author kzh
     * @date   2015年4月24日15:01:12
     *
     */
    private function soaUploadImage($file)
    {
        $img      = base64_decode($file);
        $soa      = SoaClient::getSoa('pic_v1', 'ResourceStorage');
        $filename = $soa->uploadStream($img);
        $url      = c("image_url") . $filename;

        //推入tqmq 下载图片
        if ($filename) {
            $data = array(
                'operation' => 'download',
                'params'    => array(
                    'url'    => $url,
                ),
            );
            $soa->pushToQueue($data);
        }
        usleep(100000);
        return $filename;
    }

    /**
     * 上传修改
     * new开始上传  要换回上传到本地 删掉改方法 并且把uploadOldAction方法改为uploadAction
     *
     * @author kzh
     *
     * @date   2015年4月24日14:55:35
     *
     */
    public function uploadAction()
    {
        $sendci   = (int) $_REQUEST['sendci'] + 1;
        $maxsend  = (int) $_REQUEST['maxsend'];
        $sendcont = $_REQUEST['sendcont'];

        $id = 0;
        if ($sendci == $maxsend) {
            $qiniu = $this->soaUploadImage($sendcont);
            $id = rand(1000, 9999);
        }
        echo '{success:true,msg:"' . $id . '",filepath:"' . $qiniu . '",sendci:' . $sendci . '}';
    }

    /**
     * 开始上传了
     */
    public function uploadOldAction()
    {
        $sendci     = (int) $_REQUEST['sendci'] + 1;
        $maxsend    = (int) $_REQUEST['maxsend'];
        $sendcont   = $_REQUEST['sendcont'];
        $filename   = $_REQUEST['filename'];
        $filetype   = $_REQUEST['filetype'];
        $fileext    = $_REQUEST['fileext'];
        $filesize   = $_REQUEST['filesize'];
        $filesizecn = $_REQUEST['filesizecn'];
        $newfile    = $_REQUEST['newfile'];
        $mkdir      = $_REQUEST['mkdir'];

        $stara = explode('-', $mkdir);

        $smkdir = '' . USER_PATH . '/attachment/' . $stara[0] . '';
        if (!file_exists($smkdir)) {
            mkdir($smkdir);
        }

        $smkdir .= '/' . $stara[1] . '';
        if (!file_exists($smkdir)) {
            mkdir($smkdir);
        }

        $smkdir .= '/' . $stara[2] . '';
        if (!file_exists($smkdir)) {
            mkdir($smkdir);
        }

        $allfile = '' . $smkdir . '/' . $newfile . '';

        $tempfile = $allfile . '.temp';
        $filepath = substr($tempfile, 3);
        $fc       = fopen($tempfile, 'a');
        fwrite($fc, $sendcont);
        fclose($fc);
        $id        = 0;
        $gfilepath = '' . __APP__ . '/attachment/' . $stara[0] . '/' . $stara[1] . '/' . $stara[2] . '/' . $newfile . '.' . $fileext . '';
        if ($sendci == $maxsend) {
            $content   = file_get_contents($tempfile);
            $temp1file = '' . $allfile . '.' . $fileext . '';
            file_put_contents($temp1file, base64_decode($content));
            unlink($tempfile);
            $id       = rand(1000, 9999);
            $filepath = $temp1file;
        }
        echo '{success:true,msg:"' . $id . '",filepath:"' . $gfilepath . '",sendci:' . $sendci . '}';
    }
}