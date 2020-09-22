<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Phper666\JWTAuth\JWT;

/**
 * Class LoginController
 * @Controller()
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
        //获取用户信息
        $user = User::query()->where('username', $params['username'])->first();

        //检查用户以及密码是否正确
        if (empty($user)) $this->throwExp(StatusCode::ERR_USER_ABSENT,'登录失败，用户不存在');
        if (md5($params['password']) != $user->password) $this->throwExp(StatusCode::ERR_USER_PASSWORD,'登录失败，用户验证失败，密码错误');
        //检查账户是否被停用
        if ($user['status'] != 1)  $this->throwExp(StatusCode::ERR_USER_DISABLE,'该账户已经被停用，请联系管理员');

        $userData = [
            'uid' => $user->id, //如果使用单点登录，必须存在配置文件中的sso_key的值，一般设置为用户的id
            'username' => $user->username,
        ];
        $token = $this->jwt->getToken($userData);

        //更新用户信息
        $user->last_login = time();
        $user->last_ip = getClientIp();
        $user->save();

        return $this->respondWithToken($token);
    }

    /**
     * 响应TOKEN数据
     * @param $token
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function respondWithToken($token)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>  $this->jwt->getTTL(),
        ];
        return $this->success($data);
    }

    /**
     * 退出登录操作
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logOut()
    {
        $this->jwt->logout();
        return $this->success();
    }
}
