<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Http\Service\Auth\LoginService;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Phper666\JWTAuth\JWT;

/**
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
        ];
        //配置验证
        $rules = [
            'username' => 'required',
            'password' => 'required|min:6|max:18',
        ];
        $message = [
            'username.required' => ' username 缺失',
            'password.required' => ' password 缺失',
            'password.min' => ' password 最少6位数',
            'password.max' => ' password 最多18位数',
        ];

        $this->verifyParams($params, $rules, $message);

        $responseData = LoginService::getInstance()->login($params);
        unset($responseData['user_info']['roles']);
        return $this->success($responseData);
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
