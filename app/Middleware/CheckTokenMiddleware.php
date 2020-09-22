<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 校验TOKEN是否合法
 * Class CheckTokenMiddleware
 * @package App\Middleware
 * @Author YiYuan-Lin
 * @Date: 2020/9/22
 */
class CheckTokenMiddleware implements MiddlewareInterface
{
    /**
     * @var HttpResponse
     */
    protected $response;

    protected $jwt;

    /**
     * CheckTokenMiddleware constructor.
     * @param HttpResponse $response
     * @param JWT $jwt
     */
    public function __construct(HttpResponse $response, JWT $jwt)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        var_dump(1);
        $isValidToken = false;
        // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
        $token = $request->getHeaderLine('Authorization') ?? '';
        if (strlen($token) > 0) {
            $token = JWTUtil::handleToken($token);
            if ($token !== false && $this->jwt->checkToken($token)) {
                $isValidToken = true;
            }
        }
        if ($isValidToken) {
            return $handler->handle($request);
        }

        Throw new BusinessException(StatusCode::ERR_INVALID_TOKEN, 'Token无效或者过期');
    }
}