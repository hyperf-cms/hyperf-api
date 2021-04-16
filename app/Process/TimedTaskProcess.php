<?php
declare(strict_types=1);

namespace App\Process;

use App\Foundation\Facades\Log;
use App\Model\Setting\TimedTask;
use App\Model\Setting\TimedTaskLog;
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
                    try {
                       $this->container->get($class)->handle();
                        $this->recordTaskLog(true, $timeTask);
                    } catch (\Exception $e) {
                        $errorInfo = [];
                        $errorInfo['code'] = $e->getCode();
                        $errorInfo['message'] = $e->getMessage();
                        $errorInfo['line'] = $e->getLine();
                        $errorInfo['file'] = $e->getFile();
                        $this->recordTaskLog(false, $timeTask, $errorInfo);
                    }
                }
                continue;
            }
            sleep(1);
        }
    }

    /**
     * 记录监控任务日志
     * @param bool $result
     * @param array $task
     * @param string $errorInfo
     * @return bool
     * @throws \Exception
     */
    private function recordTaskLog(bool $result, array $task, $errorInfo = '')
    {
        $timedTaskLog = new TimedTaskLog();
        if (empty($task)) return false;
        $timedTaskLog->task_id = $task['id'];
        $timedTaskLog->task_name = $task['name'];
        $timedTaskLog->task = $task['task'];
        $timedTaskLog->execute_time = strtotime($task['next_execute_time']);
        $timedTaskLog->result = $result ? 1 : 0;

        if (!$result) {
            $timedTaskLog->error_log = json_encode($errorInfo);
            TimedTask::where('id', $task['id'])->update(['status' => TimedTask::OFF_STATUS]);
        }else {
            TimedTask::updateNextExecuteTime($task['id']);
        }
        var_dump(1);
        return $timedTaskLog->save();

    }
}