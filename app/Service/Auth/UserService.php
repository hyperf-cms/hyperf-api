<?php
namespace App\Service\Auth;

use App\Constants\StatusCode;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Context\Context;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;

/**
 * 用户服务基础类
 * Class UserService
 * @package App\Service\Auth
 * @Author YiYuan-Lin
 * @Date: 2020/10/29
 */
class UserService extends BaseService
{
    use Singleton;

    #[Inject]
    private JWT $jwt;

    /**
     * 根据Token获取用户的信息
     * @return object
     */
    public function getUserInfoByToken() : object
    {
        $parserData = JWTUtil::getParserData($this->request);

        return User::getOneByUid($parserData['uid']);
    }
}
