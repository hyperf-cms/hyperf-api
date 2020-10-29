<?php
namespace App\Http\Services;

use App\Exception\Handler\BusinessException;

/**
 * Class BaseService
 * @package App\Http\Services
 * @Author YiYuan-LIn
 * @Date: 2019/4/25
 * 服务基础类
 */
abstract class BaseService
{
    protected $params = [];

    protected $md5 = [];

    /**
     * 抛出异常
     * @param int $code
     * @param string $message
     */
    public function throwExp($code = 0, $message = '')
    {
        if (empty($code)) $code = 500;
        Throw new BusinessException($code, $message);
    }

    /**
     * 对象转为数组
     * @param string $data
     * @return mixed
     */
    public function toArray($data = '')
    {
        return json_decode(json_encode($data), true);
    }
}
