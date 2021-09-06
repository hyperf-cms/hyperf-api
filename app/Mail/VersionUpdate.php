<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/mail.
 *
 * @link     https://github.com/hyperf-ext/mail
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/mail/blob/master/LICENSE
 */
namespace App\Mail;

use HyperfExt\Contract\ShouldQueue;
use HyperfExt\Mail\Mailable;

class VersionUpdate extends Mailable implements ShouldQueue
{
    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     */
    public function build()
    {
        //邮箱激活模板
        $html1 = <<<ht
    <p>Hi，<em style="font-weight: 700;">你好 林益远</em>，请点击下面的链接激活你的账号</p>
    <a href="https://blog.zongscan.com?activate=">立即激活</a>
ht;
        return $this->subject('ZONGSCAN-账号注册激活链接')->htmlBody($html1);
    }
}
