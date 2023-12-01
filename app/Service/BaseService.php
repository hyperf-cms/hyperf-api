<?php
namespace App\Service;

use App\Exception\Handler\BusinessException;
use App\Foundation\Traits\ApiTrait;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use PHPUnit\Framework\MockObject\Api;

/**
 * Class BaseService
 * @package App\Services
 * @Author YiYuan-LIn
 * @Date: 2019/4/25
 * 服务基础类
 */
abstract class BaseService
{
    use ApiTrait;

    /**
     * 抛出异常
     * @param int $code
     * @param string $message
     */
    public function throwExp(int $code = 0, string $message = '')
    {
        if (empty($code)) $code = 500;
        Throw new BusinessException($code, $message);
    }

    /**
     * 对象转为数组
     * @param string $data
     * @return mixed
     */
    public function toArray(string $data = '')
    {
        return json_decode(json_encode($data), true);
    }
}
