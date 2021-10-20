<?php

declare(strict_types=1);

namespace App\Job;

use App\Foundation\Facades\Log;
use App\Foundation\Facades\MessageParser;
use App\Foundation\Utils\Mail;
use App\Model\Auth\User;
use Hyperf\AsyncQueue\Job;

/**
 * 邮箱通知队列
 * Class EmailNotificationJob
 * @package App\Job
 * @Author YiYuan-Lin
 * @Date: 2021/8/12
 */
class EmailNotificationJob extends Job
{
    public $params;
    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     *
     * @var int
     */
    protected $maxAttempts = 2;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {
            $userList = User::query()->where('email', '!=', '')->get();

            foreach ($userList as $user) {
                $mailTitle = $this->params['title'] ?? '';
                $mailContent = $this->params['content'] ?? '';
                $title = '[Hyperf-cms] ' . $mailTitle;

                Mail::init()->setFromAddress('linyiyuann@163.com', 'Hyperf-cms')
                    ->setAddress($user['email'], $user['desc'])
                    ->setSubject($title)
                    ->setBody($this->getEmailHtml($user['desc'], $mailContent, date('Y-m-d H:i:s')))
                    ->send();
            }
        } catch (\Exception $e) {
            Log::jobLog()->error(MessageParser::expMessageParser($e));
        }
    }

    /**
     * 获取Email模板
     * @param $name
     * @param $content
     * @param $time
     * @return string
     */
    private function getEmailHtml(string $name, string $content, string $time) : string
    {
        return '<body style="color: #666; font-size: 14px; font-family: \'Open Sans\',Helvetica,Arial,sans-serif;">
                    <div class="box-content" style="width: 80%; margin: 20px auto; max-width: 800px; min-width: 600px;">
                        <div class="header-tip" style="font-size: 12px;
                                                   color: #aaa;
                                                   text-align: right;
                                                   padding-right: 25px;
                                                   padding-bottom: 10px;">
                            Confidential - Scale Alarm Use Only
                        </div>
                        <div class="info-top" style="padding: 15px 25px;
                                                 border-top-left-radius: 10px;
                                                 border-top-right-radius: 10px;
                                                 background: {0};
                                                 color: #fff;
                                                 overflow: hidden;
                                                 line-height: 32px;">
                            <img src="https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/logo/logo_color.png" style="float: left; margin: 0 10px 0 0; width: 250px;" />
                        </div>
                        <div class="info-wrap" style="border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;
                                                  border:1px solid #ddd;
                                                  overflow: hidden;
                                                  padding: 15px 15px 20px;">
                            <div class="tips" style="padding:15px;">
                                <p style=" list-style: 160%; margin: 10px 0;">Hi, ' . $name . '你好，Hyperf-cms现已更新了新的一个版本，更新内容如下：</p>
                                <p style=" list-style: 160%; margin: 10px 0;">' . $content . '</p>
                            </div>
                            <div class="time" style="text-align: right; color: #999; padding: 0 15px 15px;">' . $time . '</div>
                            <br>
                            <table class="list" style="width: 100%; border-collapse: collapse; border-top:1px solid #eee; font-size:12px;">
                                <thead>
                                    <tr style=" background: #fafafa; color: #333; border-bottom: 1px solid #eee;">
                                        <a href="http://cms.linyiyuan.top">点击此处跳转</a>
                                    </tr>
                                </thead><br>
                              
                            </table>
                        </div>
                    </div>
                </body>';
    }
}