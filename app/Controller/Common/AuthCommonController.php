<?php

declare(strict_types=1);

namespace App\Controller\Common;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Http\Service\Auth\LoginService;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Phper666\JWTAuth\JWT;

/**
 * @Controller(prefix="auth")
 */
class AuthCommonController extends AbstractController
{
    /**
     * 获取验证码操作
     * @RequestMapping(path="verification_code", methods="get")
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
   public function getVerificationCode()
   {
       $config = new \EasySwoole\VerifyCode\Conf();
       $code = new \EasySwoole\VerifyCode\VerifyCode($config);

       $result = $code->DrawCode();
       $imageBase64Code = $result->getImageBase64();
       $code = $result->getImageCode();

       $key = md5_rand();
       $container = ApplicationContext::getContainer();
       $redis = $container->get(\Hyperf\Redis\Redis::class);
       $redis->setex($key, 60, $code);

       return $this->success([
           'code' => $imageBase64Code,
           'key' => $key,
       ]);
   }
}
