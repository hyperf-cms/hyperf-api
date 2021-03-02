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

if(! function_exists('conSet')) {
    /**
     * 设置协程上下文
     * @param string $id
     * @param $value
     * @return mixed
     */
    function conSet(string $id, $value) {
        return \Hyperf\Utils\Context::set($id, $value);
    }
}

if(! function_exists('conGet')) {
    /**
     * 获取协程上下文
     * @param string $id
     * @param $default
     * @return mixed
     */
    function conGet(string $id, $default = null) {
        return \Hyperf\Utils\Context::get($id, $default);
    }
}