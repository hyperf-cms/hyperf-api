<?php

declare (strict_types=1);
namespace App\Model\Setting;

use App\Foundation\Utils\Cron;
use App\Model\Model;
/**
 * 定时任务日志模型类
 * Class TimedTaskLog
 * @package App\Model\System
 * @Author YiYuan-Lin
 * @Date: 2021/4/12
 */
class TimedTaskLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'timed_task_log';
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected ?string $connection = 'default';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [];
    /**
     * 定义运行结果枚举
     */
    const SUCCESS_RESULT = 1;
    const FAILED_RESULT = 0;
}