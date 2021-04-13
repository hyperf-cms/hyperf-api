<?php
declare(strict_types=1);

namespace App\Process;

use App\Model\Setting\TimedTask;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\Utils\ApplicationContext;

/**
 * 监听Ws的链接情况
 * Class TimedTaskProcess
 * @Process(name="timed_task")
 * @package App\Process
 * @Author YiYuan-Lin
 * @Date: 2021/4/12
 */
class TimedTaskProcess extends AbstractProcess
{
    /**
     * 监听定时任务时间表
     * @throws \Exception
     */
    public function handle(): void
    {
        while (true) {
            $nowTime = date('Y-m-d H:i:00');
            $timedTaskList = TimedTask::query()->get()->where('status', TimedTask::ON_STATUS)->toArray();
            foreach ($timedTaskList as $timeTask) {
                        if ($timeTask['next_execute_time'] == $nowTime) {
                    $params = json_decode($timeTask['params']);
                    foreach ($params as $key => $value) {
                        ${'$value[0]'} = $value[1];
                    }
                    $class = '\App\Task\\' . $timeTask['task'];
                    $this->container->get($class)->handle();
                    TimedTask::updateNextExecuteTime($timeTask['id']);
                }
                continue;
            }
            sleep(1);
        }
    }
}