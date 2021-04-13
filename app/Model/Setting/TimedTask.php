<?php

declare(strict_types=1);

namespace App\Model\Setting;

use App\Foundation\Utils\Cron;
use App\Model\Model;

/**
 * 定时任务模型类
 * Class TimedTask
 * @package App\Model\System
 * @Author YiYuan-Lin
 * @Date: 2021/4/12
 */
class TimedTask extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timed_task';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * 启动状态
     */
    const ON_STATUS = 1;
    const OFF_STATUS = 0;

    /**
     * 更新下次执行时间
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public static function updateNextExecuteTime(int $id) : bool
    {
        $self = self::findById($id);
        $executeTime = $self['execute_time'] ?? '';
        $nextExecuteTime = Cron::init($executeTime)->getNextRunDate()->format('Y-m-d H:i');

        if (self::query()->where('id', $id)->update(['next_execute_time' => $nextExecuteTime, 'times' => $self['times'] + 1])) return true;

        return false;
    }

}