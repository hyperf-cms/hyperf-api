<?php
namespace App\Service\Auth;

use App\Constants\StatusCode;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\JWT;

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

    /**
     * @Inject()
     * @var JWT
     */
    private $jwt;

    /**
     * 根据Token获取用户的信息
     * @return object
     */
    public function getUserInfoByToken() : object
    {
        //获取Token解析的数据
        $parserData = $this->jwt->getParserData();
        $userId = $parserData['uid'];

        $userInfo = User::getOneByUid($userId);
        return $userInfo;
    }
}
