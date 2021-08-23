<?php
declare(strict_types=1);

namespace App\Foundation\Facades;

use Hyperf\Config\Annotation\Value;
use Hyperf\Logger\Logger;
use Hyperf\Utils\ApplicationContext;

/**
 * 日志工具类
 * Class Log
 * @Author YiYuan-Lin
 * @Date: 2020/9/19
 */
class Log
{

    /**
     * 日志通道
     * @param string $group
     * @param string $name
     * @return \Psr\Log\LoggerInterface
     */
    public static function channel(string $group = 'default', string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name, $group);
    }

    /**
     * debug调试日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function codeDebug()
    {
        return self::channel('code_debug', config('app_env', 'app'));
    }

    /**
     * 接口请求日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function requestLog()
    {
        return self::channel('request_log', config('app_env', 'app'));
    }

    /**
     * 接口返回日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function responseLog()
    {
        return self::channel('response_log', config('app_env', 'app'));
    }

    /**
     * sql记录日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function sqlLog()
    {
        return self::channel('sql_log', config('app_env', 'app'));
    }

    /**
     * 队列错误日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function jobLog()
    {
        return self::channel('job_log', config('app_env', 'app'));
    }

    /**
     * 定时任务错误日志
     * @return \Psr\Log\LoggerInterface
     */
    public static function crontabLog()
    {
        return self::channel('crontab_log', config('app_env', 'app'));
    }
}