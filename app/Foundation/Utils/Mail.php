<?php

declare(strict_types=1);

namespace App\Foundation\Utils;

use App\Exception\Handler\BusinessException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件工具类
 * Class Mail
 * @package App\Foundation\Utils
 * @Author YiYuan-Lin
 * @Date: 2021/8/17
 */
class Mail
{
    /**
     * 邮件类实例化
     * @var
     */
    static private $mail;

    /**
     * Mail类对象
     * @var
     */
    static public $obj  = null;

    /**
     * 初始化邮件工具类
     * @param array $config
     * config['char_set'] string 邮件编码
     * config['smtp_debug'] bool 是否调试模式输出
     * config['is_html'] bool 是否HTML输出
     * config['host'] string SMTP服务器
     * config['port'] int 服务器端口
     * config['smtp_auth'] bool 允许SMTP认证
     * config['username'] string SMTP用户名
     * config['password'] string SMTP密码
     * config['smtp_secure'] tls/ssl 允许 TLS 或者ssl协议
     * @return Mail|null
     */
    public static function init(array $config = [])
    {
        self::$mail = new PHPMailer(true);
        self::__setConfig($config);
        if (!is_object(self::$obj)) self::$obj = new self;
        return self::$obj;
    }

    /**
     * 设置参数
     * @param array $config
     */
    private static function __setConfig(array $config)
    {
        //使用SMTP
        self::$mail->isSMTP();
        //设定邮件编码
        self::$mail->CharSet = $config['char_set'] ?? "UTF-8";
        //调试模式输出
        self::$mail->SMTPDebug = $config['smtp_debug'] ?? 0;
        //是否HTML输出
        self::$mail->isHTML($config['is_html'] ?? true);
        //SMTP服务器
        self::$mail->Host = $config['host'] ?? env('MAIL_SMTP_HOST', 'smtp.163.com');
        //服务器端口 25 或者465 具体要看邮箱服务器支持
        self::$mail->Port = $config['port'] ?? env('MAIL_SMTP_PORT', 465);
        //允许SMTP认证
        self::$mail->SMTPAuth = $config['smtp_auth'] ?? true;
        //SMTP用户名
        self::$mail->Username = $config['username'] ?? env('MAIL_SMTP_USERNAME', 'SMTP用户名');
        //SMTP密码  部分邮箱是授权码(例如163邮箱)
        self::$mail->Password = $config['password'] ?? env('MAIL_SMTP_PASSWORD', '密码或者授权码');
        //允许 TLS 或者ssl协议
        self::$mail->SMTPSecure = $config['smtp_secure'] ?? env('MAIL_SMTP_ENCRYPTION', 'ssl');
    }

    /**
     * 设置发件人
     * @param string $email
     * @param string $name
     * @return mixed
     */
    public function setFromAddress(string $email, string $name) : self
    {
        self::$mail->setFrom($email, $name);
        return $this;
    }

    /**
     * 设置收件人信息
     * @param string $email
     * @param string $name
     * @return self
     */
    public function setAddress(string $email, string $name) : self
    {
        self::$mail->addAddress($email, $name);
        return $this;
    }

    /**
     * 设置收件人信息（多收件人）
     * @param array $emailInfo
     * @return self
     */
    public function setMoreAddress(array $emailInfo) : self
    {
        foreach ($emailInfo as $item) {
            self::$mail->addAddress($item['email'], $item['name']);
        }
        return $this;
    }

    /**
     * 抄送
     * @param string $email
     * @return self
     */
    public function addCC(string $email) : self
    {
        self::$mail->addCC($email);
        return $this;
    }

    /**
     * 密送
     * @param string $email
     * @return self
     */
    public function addBCC(string $email) : self
    {
        self::$mail->addBCC($email);
        return $this;
    }

    /**
     * 添加附件
     * @param string $url
     * @param string $fileName
     * @return self
     */
    public function addAttachment(string $url, string $fileName = '') : self
    {
        self::$mail->addAttachment($url, $fileName);
        return $this;
    }

    /**
     * 设置邮件标题
     * @param string $title
     * @return self
     */
    public function setSubject(string $title) : self
    {
        self::$mail->Subject = $title;
        return $this;
    }

    /**
     * 设置邮件内容
     * @param string $body
     * @return self
     */
    public function setBody(string $body) : self
    {
        self::$mail->Body = $body;
        return $this;
    }

    /**
     * 发送邮件
     * @return bool
     */
    public function send()
    {
        try {
            if (self::$mail->send()) return true;
            return false;
        }catch (\Exception $e) {
            Throw new BusinessException(400, $e->getMessage());
        }
    }
}
