<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Model\Auth\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\Context;
use Hyperf\WebSocketServer\Security;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP请求控制（跨域中间件）
 * Class WsMiddleware
 * @package App\Middleware
 * @Author YiYuan-Lin
 * @Date: 2020/9/21
 */
class WsMiddleware implements MiddlewareInterface
{
    private const HANDLE_SUCCESS_CODE = 101;

    private const HANDLE_FAIL_CODE = 401;

    private const HANDLE_BAD_REQUEST_CODE = 400;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JWT
     */
    protected $jwt;

    public function __construct(ContainerInterface $container, RequestInterface $request, JWT $jwt)
    {
        $this->container = $container;
        $this->request = $request;
        $this->jwt = $jwt;
    }

    /**
     * WebSocket鉴权中间件
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
            $isValidToken = false;
            $response = Context::get(ResponseInterface::class);
            $request = Context::get(ServerRequestInterface::class);
            $token = $request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL) ?? '';

            try {
                if (strlen($token) > 0 && $this->jwt->checkToken($token)) $isValidToken = true;
            } catch (Throwable $e) {
                return $response
                    ->withStatus(self::HANDLE_BAD_REQUEST_CODE);
            }

            if ($isValidToken) {
                $jwtData = $this->jwt->getParserData($token);
                $userInfo = User::query()->where(['id' => $jwtData['uid']])->first();
                $userInfo = objToArray($userInfo);
                conSet('user_info', $userInfo);
                if (!empty($request->getQueryParams()['is_reconnection'])) {
                    conSet('is_reconnection', true);
                }
                return $handler->handle($request);
            }

            return $response->withStatus(self::HANDLE_FAIL_CODE);
    }
}