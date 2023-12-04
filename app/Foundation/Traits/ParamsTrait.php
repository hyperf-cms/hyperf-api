<?php
namespace App\Foundation\Traits;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Phper666\JWTAuth\Util\JWTUtil;

/**
 * 参数基类
 * Trait ParamsTrait
 * @package App\Foundation\Traits
 */
trait ParamsTrait
{
    #[Inject]
    protected RequestInterface $request;

    /**
     * 获取请求参数
     * @param string $queryString
     * @return array|string
     */
    public function params(string $queryString = '')
    {
        if (empty($queryString)) return $this->request->all();

        return $this->request->all()[$queryString] ?? null;
    }

    /**
     * 获取当前用户
     * Date 2022/10/10
     * Author YiYuan
     * @return array
     */
    public function user() : array
    {
        return conGet('user_info') ?? [];
    }

    /**
     * 获取请求IP
     * @Author YiYuan
     * @Date 2023/12/4
     * @return string
     */
    protected function ip() : string
    {
        return conGet('request_ip') ?? '';
    }
}
