<?php

declare(strict_types=1);

namespace App\Foundation\Handler;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * LogFileHandler
 * 日志处理，存储文件
 *
 * @package Core\Common\Handler
 */

/**
 * 日志处理，存储文件
 * 将info、warning、notic等类型存储一个文件，debug类型存储一个文件，error类型存储一个文件
 * 通过继承RotatingFileHandler实现日期轮转
 * Class LogFileHandler
 * @package App\Foundation\Handler
 * @Author YiYuan-Lin
 * @Date: 2020/9/19
 */
class LogFileHandler extends RotatingFileHandler
{

    /**
     * 改写父类方法，增加判断日志输出，框架日志
     * @param array $record
     * @return bool
     */
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $record = $this->processRecord($record);
        // 判断是否开始日志记录
        if ( !config('app_log') ) {
            return false;
        }
        // 判断是否处理框架日志
        if ( !config('hf_log') && $record['channel'] == 'hyperf' ) {
            return false;
        }
        // 判断系统允许日志类型
        if ( ! isStdoutLog($record['level_name']) ) {
            return false;
        }
        $record['formatted'] = $this->getFormatter()->format($record);
        $this->write($record);
        return false === $this->bubble;
    }

    /**
     * 重写该方法，作用改变日志的存储文件的方式。
     * 将debug,error，单独存储，其它的按着原来规则
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record) : bool
    {
        switch ($record['level']) {
            case Logger::DEBUG:
                return $record['level'] == $this->level;
                break;
            case $record['level'] == Logger::ERROR || $record['level'] == Logger::CRITICAL || $record['level'] == Logger::ALERT || $record['level'] == Logger::EMERGENCY:
                return Logger::ERROR <= $this->level && Logger::EMERGENCY >= $this->level;
                break;
            default:
                return Logger::INFO <= $this->level && Logger::WARNING >= $this->level;
        }
    }
}