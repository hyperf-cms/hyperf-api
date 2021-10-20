<?php
declare(strict_types = 1);

namespace App\Foundation\Facades;

use Hyperf\Utils\Codec\Json;

/**
 * 消息格式化
 * Class MessageParser
 * @Author YiYuan-Lin
 * @Date: 2021/3/12
 */
class MessageParser
{
    /**
     * @param string $data
     * @return array
     */
    public static function decode(string $data) : array
    {
        return Json::decode($data, true);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function encode(array $data) : string
    {
        return Json::encode($data);
    }

    /**
     * 异常消息格式化
     * @param \Exception $e
     * @return string
     */
    public static function expMessageParser(\Exception $e) : string
    {
        return '错误码为：' . $e->getCode() . '错误信息： ' . $e->getMessage() . ':: 错误信息文件位置:' . $e->getFile() . ':: 错误信息行数: ' . $e->getLine();
    }
}
