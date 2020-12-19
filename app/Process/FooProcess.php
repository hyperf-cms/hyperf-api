<?php

declare(strict_types=1);

namespace App\Process;

use App\Foundation\Facades\Log;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

/**
 * @Process(name="foo_process")
 * Class FooProcess
 * @package App\Process
 * @Author YiYuan-Lin
 * @Date: 2020/12/18
 */
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // TODO: Implement handle() method.
//        While (true) {
//            $redis = $this->container->get(\Redis::class);
//            $count = $redis->llen('queue:failed');
////            if ($count > 0) Log::channel('code_debug')->info('The num of failed queue is ' . $count);
////            sleep(1);
////        }
    }
}