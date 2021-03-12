<?php
declare(strict_types = 1);

namespace App\Foundation\Facades;

use Hyperf\Utils\Codec\Json;

/**
 * json格式化
 * Class MessageParser
 * @Author YiYuan-Lin
 * @Date: 2021/3/12
 */
class MessageParser
{
    /**
     * @param string $data
     *
     * @return array
     */
    public static function decode(string $data) : array
    {
        return Json::decode($data, true);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public static function encode(array $data) : string
    {
        return Json::encode($data);
    }
}
