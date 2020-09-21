<?php

use Hyperf\Contract\StdoutLoggerInterface;

/**
 * 与应用逻辑相关的帮助函数
 * create by linyiyuan
 */
if (! function_exists('isStdoutLog')) {
    /**
     * isStdoutLog
     * 判断日志类型是否允许输出
     * @param string $level
     * @return bool
     */
    function isStdoutLog(string $level)
    {
        $config = config(StdoutLoggerInterface::class, ['log_level' => []]);
        return in_array(strtolower($level), $config['log_level'], true);
    }
}