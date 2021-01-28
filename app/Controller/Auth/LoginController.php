<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Http\Service\Auth\LoginService;
use App\Middleware\RequestMiddleware;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Phper666\JWTAuth\JWT;

/**
 * 登陆控制器
 * @Controller(prefix="auth")
 */
class LoginController extends AbstractController
{
    /**
     * @Inject()
     * @var JWT
     */
    private $jwt;

    /**
     * 登陆操作
     * @RequestMapping(path="login", methods="post")
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login()
    {
        $params = [
            'username' =>  $this->request->input('username') ?? '',
            'password' => $this->request->input('password') ?? '',
            'code_key' => $this->request->input('code_key') ?? '',
            'captcha' => $this->request->input('captcha') ?? '',
        ];
        $rules = [
            'username' => 'required',
            'password' => 'required|min:6|max:18',
            'code_key' => 'required',
            'captcha' => 'required',
        ];
        $message = [
            'username.required' => ' username 缺失',
            'password.required' => ' password 缺失',
            'password.min' => ' password 最少6位数',
            'password.max' => ' password 最多18位数',
            'code_key.required' => '验证码KEY缺失',
            'captcha.required' => '验证码缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $responseData = LoginService::getInstance()->login($params);
        return $this->success($responseData);
    }

    /**
     * 初始化操作
     * @RequestMapping(path="initialization", methods="get")
     * @Middlewares({
            @Middleware(RequestMiddleware::class)
 *     })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function initialization()
    {
        $list = LoginService::getInstance()->initialization();
        return $this->success($list);
    }

    /**
     * 获取前端路由
     * @RequestMapping(path="routers", methods="get")
     * @Middlewares({
            @Middleware(RequestMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getRouters()
    {
        $list = LoginService::getInstance()->getRouters();
        return $this->success($list);
    }

    /**
     * 退出登录操作
     * @RequestMapping(path="logout", methods="post")
     * @Middlewares({
            @Middleware(RequestMiddleware::class)
*     })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logOut()
    {
        $this->jwt->logout();
        return $this->success([], '退出登录成功');
    }
}
