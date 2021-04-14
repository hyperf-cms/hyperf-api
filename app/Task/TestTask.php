<?php
declare(strict_types = 1);

namespace App\Task;

use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Task\Annotation\Task;

/**
 * 测试异步任务
 * Class TestTask
 * @package App\Task
 * @Author YiYuan-Lin
 * @Date: 2021/4/12
 */
class TestTask
{
    /**
     * @Task()
     * @return bool
     */
    public function handle()
    {
        Log::codeDebug()->info('测试成功');
        Throw new BusinessException(599, '测试错误');

        return true;
    }

}
