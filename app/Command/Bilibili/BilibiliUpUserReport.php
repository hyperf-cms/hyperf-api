<?php
namespace App\Command\Bilibili;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="BilibiliUpUserReport", rule="*\/5 * * * *", callback="execute", memo="BilibiliUp主数据采集定时任务")
 */
class BilibiliUpUserReport
{
    /**
     * @Inject()
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    private $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    /**
     * @Crontab(rule="* * * * * *", memo="foo")
     */
    public function foo()
    {
        var_dump('foo');
    }
}
