<?php

namespace App\Foundation\Utils;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use Cron\CronExpression;
use Hyperf\Di\Annotation\Inject;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

/**
 * CronTab表达式解析工具类
 * Class Cron
 * @Author YiYuan-Lin
 * @Date: 2021/4/13
 */
class Cron
{
    /**
     * cron对象
     * @var
     */
    private static $cron;

    /**
     * 表达式
     * @var
     */
    private static $expression;

    /**
     * 自身类的实例化
     * @var
     */
    private static $self;

    /**
     * 初始化表达式
     * @param string $expression
     * @return Cron
     */
    public static function init(string $expression = '')
    {
        if (empty($expression))Throw new BusinessException(StatusCode::ERR_EXCEPTION, '表达式不正确');
        self::$cron = new CronExpression($expression);
        self::$self = new static;
        self::$expression = $expression;
        return self::$self;
    }

    /**
     * 获取cron下次时间
     * @return \DateTime
     * @throws \Exception
     */
    public static function getNextRunDate()
    {
        return self::$cron->getNextRunDate();
    }

    /**
     * 获取cron上次时间
     * @return \DateTime
     * @throws \Exception
     */
    public static function getPreviousRunDate()
    {
        return self::$cron->getPreviousRunDate();
    }

    /**
     * 或者上/下 X次执行时间
     * @param int $total
     * @param string $currentTime
     * @param bool $invert
     * @param bool $allowCurrentDate
     * @param null $timeZone
     * @return array|\DateTime[]
     */
    public static function getMultipleRunDates(int $total, $currentTime = 'now', bool $invert = false, bool $allowCurrentDate = false, $timeZone = null)
    {
        return self::$cron->getMultipleRunDates($total, $currentTime, $invert, $allowCurrentDate, $timeZone);
    }
}