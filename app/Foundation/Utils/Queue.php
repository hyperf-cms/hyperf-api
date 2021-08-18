<?php

declare(strict_types=1);

namespace App\Foundation\Utils;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\JobInterface;

/**
 * 队列生产类
 * Class Queue
 * @package App\Foundation\Utils
 * @Author YiYuan-Lin
 * @Date: 2021/8/18
 */
class Queue
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 生产消息
     * @param JobInterface $jobObj
     * @param int $delay
     * @return bool
     */
    public function push(JobInterface $jobObj, int $delay = 0): bool
    {
        return $this->driver->push($jobObj, $delay);
    }
}