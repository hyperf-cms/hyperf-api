<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use App\Http\Service\Auth\UserService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestMiddleware implements MiddlewareInterface
{
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
     * 请求校验Token中间件
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requireParams = $this->request->all();
        //记录请求参数日志记录
        if (config('request_log')) Log::requestLog()->info('请求参数：' . json_encode($requireParams));

        try {
            $isValidToken = false;
            // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
            $token = $request->getHeaderLine('Authorization') ?? '';
            if (strlen($token) > 0) {
                $token = JWTUtil::handleToken($token);
                if ($token !== false && $this->jwt->checkToken($token)) $isValidToken = true;

                //如果校验成功
                if ($isValidToken) {
                    //将用户信息放置协程上下文当中
                    $userInfo = UserService::getInstance()->getUserInfoByToken();
                    conSet('user_info', $userInfo);
                    return $handler->handle($request);
                }
            }
        }catch (TokenValidException $e) {
            // 此处捕获到了 token 过期所抛出的 TokenValidException
            //我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                // 刷新用户的 token
                $token = $request->getHeaderLine('Authorization') ?? '';
                $token = JWTUtil::handleToken($token);
                $tokenData = $this->jwt->getParserData($token);

                //判断token是否在缓存时间内，如果是刷新token
                if (time()-$tokenData['exp'] < intval(config('jwt.ttl_cache'))) {
                    $token = $this->jwt->refreshToken();
                    //从协程获取全局的Response 对象， 并将刷新TOKEN写进去头部返回给前端供前端刷新token
                    $response = conGet(ResponseInterface::class);
                    //在返回对象里增添刷新后的token以及Access-Control-Expose-Headers（让前端可以获取到我们自定义的authorization）
                    $response = $response->withHeader('authorization', $token)->withHeader('Access-Control-Expose-Headers', 'authorization');
                    conSet(ResponseInterface::class, $response);

                    //将用户信息放置协程上下文当中
                    $userInfo = UserService::getInstance()->getUserInfoByToken();
                    conSet('user_info', $userInfo);

                    return $handler->handle($request);
                }

                throw new TokenValidException('Token已经过期', 401);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表refresh也过期了，用户无法刷新令牌，需要重新登录。
                throw new TokenValidException($exception->getMessage(), $exception->getCode());
            }
        }

        Throw new BusinessException(StatusCode::ERR_INVALID_TOKEN, 'Token无效或者过期');
    }
}
