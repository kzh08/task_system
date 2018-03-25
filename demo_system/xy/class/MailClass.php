<?php
/*
   +----------------------------------------------------------------------+
   |                  			  push platform                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2014 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | ���ڷ�������ʼ�									      	 	  	  |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-07-11       |
   +----------------------------------------------------------------------+
*/
ini_set('display_errors', 1);
error_reporting(E_All);

require 'mail/class.phpmailer.php';

class Mail
{
    public $from_name = 'ϵͳ�ʼ�';
    public $from_email = 'xynotice@ushengsheng.com';
    public $to_name;
    public $to_email;
    public $to_email_list;
    public $cc_email;
    public $bcc_email;
    public $subject;
    public $message;
    public $attachment; //����url
    public $attachmentName; //�������
    public $email;
    public $replyto_list = ''; //��ʽ������ a@b.com|����  a@b.com;c@d.com|����;����
    public $authenticate_username; //�û���
    public $authenticate_password;

    private $test_type;
    private $smtp_debug; //0 == off, 1 for client output, 2 for client and server
    private $smtp_server;
    private $smtp_port;
    private $smtp_secure;
    private $smtp_authenticate;


    public function __construct()
    {
        $this->test_type = 'smtp';
        $this->smtp_debug = 0;
        $this->smtp_server = 'smtp.exmail.qq.com';
        $this->smtp_port = '25';
        $this->smtp_secure = 'None';
        $this->smtp_authenticate = true;
        $this->authenticate_username = 'xynotice@ushengsheng.com';
        $this->authenticate_password = 'd8a9b7s6q8o1';

        $this->email = new PHPMailer(true);
        $this->email->CharSet = 'gb2312';
        $this->email->SetLanguage('ch');
        $this->email->Debugoutput = 'html';
    }

    public function send()
    {


        $username = $this->authenticate_username;
        $password = $this->authenticate_password;
        $from = $this->from_email;
        switch ($from) {
            case 'xywar@ushengsheng.com':
                $username = 'xywar@ushengsheng.com';
                $password = 'd8a9b7s6q8o1';
                break;
            case 'xyerror@ushengsheng.com':
                $username = 'xyerror@ushengsheng.com';
                $password = 'd8a9b7s6q8o1';
                break;
            default:
                $from = $username;
        }

        try {
            if (!PHPMailer::ValidateAddress($this->to_email) && $this->to_email != '' && $this->to_name != '') {
                throw new phpmailerAppException("Email address " . $this->to_email . " is invalid -- aborting!");
            }

            switch ($this->test_type) {
                case 'smtp':
                    $this->email->IsSMTP(); // telling the class to use SMTP
                    $this->email->SMTPDebug = (integer)$this->smtp_debug;
                    $this->email->Host = $this->smtp_server; // SMTP server
                    $this->email->Port = (integer)$this->smtp_port; // set the SMTP port
                    if ($this->smtp_secure != 'None') {
                        $this->email->SMTPSecure = strtolower($this->smtp_secure);
                    }
                    $this->email->SMTPAuth = $this->smtp_authenticate;
                    $this->email->Username = $username; // SMTP account username
                    $this->email->Password = $password; // SMTP account password
                    break;
                case 'mail':
                    $this->email->IsMail(); // telling the class to use PHP's email()
                    break;
                case 'sendmail':
                    $this->email->IsSendmail(); // telling the class to use Sendmail
                    break;
                case 'qmail':
                    $this->email->IsQmail(); // telling the class to use Qmail
                    break;
                default:
                    throw new phpmailerAppException('Invalid test_type provided');
            }


            if ($this->from_name != '') {
                $this->from_name = iconv('utf-8', 'gb2312', $this->from_name);
                $this->email->AddReplyTo($from, $this->from_name);
                $this->email->From = $from;
                $this->email->FromName = $this->from_name;
            } else {
                $this->email->AddReplyTo($from);
                $this->email->From = $from;
                $this->email->FromName = $this->from_email;
            }

            if ($this->to_name != '') {
                $this->to_name = iconv('utf-8', 'gb2312', $this->to_name);
                $this->email->AddAddress($this->to_email, $this->to_name);
            } else {
                if ($this->to_email != '') {
                    $this->email->AddAddress($this->to_email);
                }
            }
            //���˽���
            if ($this->to_email_list != '') {
                $toMailArr = explode('|', $this->to_email_list);
                $emailArr = explode(';', $toMailArr[0]);
                $nameArr = explode(';', $toMailArr[1]);

                foreach ($emailArr as $key => $value) {
                    $this->email->AddAddress($value, iconv('utf-8', 'gb2312', $nameArr[$key]));
                }
            }

            //�ظ��˽���
            if ($this->replyto_list != '') {
                $toMailArr = explode('|', $this->replyto_list);
                $emailArr = explode(';', $toMailArr[0]);
                $nameArr = explode(';', $toMailArr[1]);

                foreach ($emailArr as $key => $value) {
                    $this->email->AddReplyTo($value, iconv('utf-8', 'gb2312', $nameArr[$key]));
                }
            }

            if ($this->bcc_email != '') {
                $indiBCC = explode(";", $this->bcc_email);
                foreach ($indiBCC as $key => $value) {
                    $this->email->AddBCC($value);
                }
            }

            if ($this->cc_email != '') {
                $indiCC = explode(";", $this->cc_email);
                foreach ($indiCC as $key => $value) {
                    $this->email->AddCC($value);
                }
            }

        } catch (phpmailerException $e) { //Catch all kinds of bad addressing
            throw new phpmailerAppException($e->getMessage());
        }

        $this->subject = iconv('utf-8', 'gb2312', $this->subject);
        $this->email->Subject = $this->subject;

        $this->message = iconv('utf-8', 'gb2312//IGNORE', $this->message);
        if (!$this->message) {
            return 'ת�����';
        }
        $body = $this->message;

        //if ( $_POST['Message'] == '' ) {
        //$body = file_get_contents('a.php');
        // } else {
        //$body = $this->message;
        // }

        $this->email->WordWrap = 80;

        $this->email->MsgHTML($body);
        //��Ӹ��������������|�ֿ���attachment ������ַ����Ե�ַŶ
        if ($this->attachment != '') {
            $atta1 = explode('|', $this->attachment);
            $atta2 = explode('|', $this->attachmentName);
            for ($i = 0; $i < count($atta1); $i++) {
                $this->email->AddAttachment($atta1[$i], $atta2[$i]);
            }
        }

        $this->email->Send();

        return 1010;
    }
}

class phpmailerAppException extends phpmailerException
{
}

?>