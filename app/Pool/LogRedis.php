<?php
declare(strict_types=1);

namespace App\Pool;

use Hyperf\Redis\Redis as BaseRedis;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * 默认Redis库连接池
 * Class LogRedis
 * @package App\Pool
 * @Author YiYuan-Lin
 * @Date: 2021/3/10
 */
class LogRedis
{
    /**
     * 定义连接池名称
     * @var
     */
    private static $connection = 'log';

    /**
     * 定义单例模式
     * @var
     */
    private static $instance;


    /**
     * 构造方法私有化，防止外部创建实例
     * SingletonTrait constructor.
     */
    private function __construct(){}

    /**
     * 克隆方法私有化，防止复制实例
     */
    private function __clone(){}

    /**
     * 返回一个Redis单例
     * @param mixed ...$args
     * @return BaseRedis|mixed
     */
    static function getInstance(...$args)
    {
        if(!isset(self::$instance)){
            self::$instance =  ApplicationContext::getContainer()->get(RedisFactory::class)->get(self::$connection);
        }
        return self::$instance;
    }

}