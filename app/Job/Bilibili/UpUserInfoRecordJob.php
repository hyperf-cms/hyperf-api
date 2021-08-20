<?php

declare(strict_types=1);

namespace App\Job\Bilibili;

use App\Foundation\Facades\Log;
use App\Foundation\Utils\Mail;
use App\Model\Auth\User;
use App\Service\Laboratory\Bilibili\UpUserService;
use Hyperf\AsyncQueue\Job;

/**
 * 新录入Up主信息录入
 * Class UpUserInfoRecordJob
 * @package App\Job\Bilibili
 * @Author YiYuan-Lin
 * @Date: 2021/8/20
 */
class UpUserInfoRecordJob extends Job
{
    public $params;
    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
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
          UpUserService::getInstance()->recordUpUserInfoFromBilibili($this->params['up_user_mid']);
        } catch (\Exception $e) {
            Log::jobLog()->error($e->getMessage());
        }
    }

}