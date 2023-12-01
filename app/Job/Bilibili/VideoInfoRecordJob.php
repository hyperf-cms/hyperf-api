<?php

declare(strict_types=1);

namespace App\Job\Bilibili;

use App\Foundation\Facades\Log;
use App\Foundation\Utils\Mail;
use App\Model\Auth\User;
use App\Service\Laboratory\Bilibili\UpUserService;
use App\Service\Laboratory\Bilibili\VideoService;
use Hyperf\AsyncQueue\Job;

/**
 * 视频数据录入异步队列
 * Class VideoInfoRecordJob
 * @package App\Job\Bilibili
 * @Author YiYuan-Lin
 * @Date: 2021/8/24
 */
class VideoInfoRecordJob extends Job
{
    public $params;
    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     * @var int
     */
    protected int $maxAttempts = 2;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {
            VideoService::getInstance()->recordVideoInfoFromBilibili($this->params['video_bvid']);
        } catch (\Exception $e) {
            Log::jobLog()->error($e->getMessage());
        }
    }

}