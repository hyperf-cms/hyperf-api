<?php
namespace App\Service\Auth;

use App\Constants\StatusCode;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use App\Service\System\LoginLogService;
use App\Model\Auth\Permission;
use App\Model\Auth\User;
use App\Model\System\LoginLog;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 登陆服务基础类
 * Class LoginService
 * @package App\Service\Auth
 * @Author YiYuan-Lin
 * @Date: 2020/10/29
 */
class LoginService extends BaseService
{
    use Singleton;

    #[Inject]
    private JWT $jwt;

    /**
     * 处理登陆逻辑
     * @param array $params
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     * @throws \RedisException
     */
    public function login(array $params) : array
    {
        //获取用户信息
        $user = User::query()->where('username', $params['username'])->first();

        //检查用户以及密码是否正确以及检查账户是否被停用
        if (empty($user)) $this->throwExp(StatusCode::ERR_USER_ABSENT,'登录失败，用户不存在');
        if (md5($params['password']) != $user->password) $this->throwExp(StatusCode::ERR_USER_PASSWORD,'登录失败，用户验证失败，密码错误');
        if ($user['status'] != 1)  $this->throwExp(StatusCode::ERR_USER_DISABLE,'该账户已经被停用，请联系管理员');

        //校验验证码 若是测试环境跳过验证码验证
        if (!env('APP_TEST')) {
            $container = ApplicationContext::getContainer();
            $redis = $container->get(\Hyperf\Redis\Redis::class);
            $code = $redis->get($params['code_key']);
            if (strtolower($params['captcha']) != strtolower($code)) $this->throwExp(StatusCode::ERR_CODE, '验证失败，验证码错误');
        }
        $userData = [
            'uid' => $user->id, //如果使用单点登录，必须存在配置文件中的sso_key的值，一般设置为用户的id
            'username' => $user->username,
        ];
        $token = $this->jwt->getToken('default', $userData)->toString();

        //更新用户信息
        $user->last_login = time();
        $user->last_ip = getRealIp();
        $user->save();
        $responseData = $this->respondWithToken($token);

        //记录登陆日志
        $loginLogData = LoginLogService::getInstance()->collectLoginLogInfo();
        $loginLogData['response_code'] = 200;
        $loginLogData['response_result'] = '登陆成功';
        LoginLog::add($loginLogData);

        return $responseData;
    }

    /**
     * 处理注册逻辑
     * @param array $params
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function register(array $params) : bool
    {
        //校验验证码 若是测试环境跳过验证码验证
        if (!env('APP_TEST')) {
            $container = ApplicationContext::getContainer();
            $redis = $container->get(\Hyperf\Redis\Redis::class);
            $code = $redis->get($params['code_key']);
            if (strtolower($params['captcha']) != strtolower($code)) $this->throwExp(StatusCode::ERR_CODE, '验证失败，验证码错误');
        }
        $postData = $this->request->all();

        $user = new User();
        $user->username = $postData['username'];
        $user->password = md5($postData['password']);
        $user->status = User::STATUS_ON;
        $user->avatar = 'https://shmily-album.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1,10) .'.png';
        $user->last_login = time();
        $user->last_ip = getClientIp($this->request);
        $user->creater = '无';
        $user->desc = $postData['desc'] ?? '';
        $user->sex = User::SEX_BY_Female;

        if (!$user->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '注册用户失败');
        $user->assignRole('tourist_admin');
        return true;
    }

    /**
     * 登陆初始化，获取用户信息以及一些权限菜单
     * @return mixed
     */
    public function initialization() : array
    {
        $responseData = [];
        //获取用户信息
        $user = UserService::getInstance()->getUserInfoByToken();
        $userInfo = objToArray($user);
        unset($userInfo['roles']);
        unset($userInfo['permissions']);

        $menu = $this->getMenuList($user);
        $responseData['user_info'] = objToArray($userInfo);
        $responseData['role_info'] = $user->getRoleNames();
        $responseData['menu_header'] = $menu['menuHeader'];
        $responseData['menu_list'] = $menu['menuList'];
        $responseData['permission'] = $menu['permission'];
        $responseData['permission_info'] = $menu['permission_info'];

        return $responseData;
    }

    /**
     * 处理权限得到路由（提供给前端注册路由）
     * @return array
     */
    public function getRouters() :array
    {
        $userInfo = conGet('user_info');
        $permissionList = Permission::getUserPermissions($userInfo);
        $permissionList = objToArray($permissionList);
        $permissionList = array_column($permissionList, null, 'id');

        foreach ($permissionList as $key => $val) {
            if ($val['status'] == Permission::OFF_STATUS) unset($permissionList[$key]);
            if ($val['type'] == Permission::BUTTON_OR_API_TYPE) unset($permissionList[$key]);
        }

        //使用引用传递递归数组
        $routers = [
           'default' => [
                'path' => '',
                'component' => 'Layout',
                'redirect' => '/home',
                'children' => [],
            ]
        ];
        $module_children = [];
        foreach($permissionList as $key => $value){
            if(isset($permissionList[$value['parent_id']])){
                $permissionList[$value['parent_id']]['children'][] = &$permissionList[$key];
            }else{
                $module_children[] = &$permissionList[$key];
            }
        }
        foreach ($module_children as $key => $value) {
            if (!empty($value['children'])) {
                $routers[$value['id']] = [
                    'name' => $value['name'],
                    'path' => $value['url'],
                    'redirect' => 'noRedirect',
                    'hidden' => $value['hidden'],
                    'alwaysShow' => true,
                    'component' => $value['component'],
                    'meta' => [
                        'icon' => $value['icon'],
                        'title' => $value['display_name'],
                    ],
                ];
                $routers[$value['id']]['children'] = $this->dealRouteChildren($value['children']);
            }else {
                $routers['default']['children'][] = [
                    'name' => $value['name'],
                    'path' => $value['url'],
                    'hidden' => $value['hidden'],
                    'alwaysShow' => true,
                    'component' => $value['component'],
                    'meta' => [
                        'icon' => $value['icon'],
                        'title' => $value['display_name'],
                    ],
                ];
            }
        }
        return array_values($routers);
    }

    /**
     * 处理路由下顶级路由下子路由
     * @param array $children
     * @return array
     */
    private function dealRouteChildren(array $children) : array
    {
        $temp = [];
        if (!empty($children)) {
            foreach ($children as $k => $v) {
                if ($v['type'] == Permission::MENU_TYPE) {
                    $temp[] = [
                        'name' => $v['name'],
                        'path' => $v['url'],
                        'hidden' => $v['hidden'],
                        'alwaysShow' => true,
                        'component' => $v['component'],
                        'meta' => [
                            'icon' => $v['icon'],
                            'title' => $v['display_name'],
                        ],
                    ];
                }
                if (!empty($v['children'])) {
                    $temp = array_merge($temp, $this->dealRouteChildren($v['children']));
                }
            }
        }
        return $temp;
    }

    /**
     * 处理TOKEN数据
     * @param string $token
     * @return array
     */
    protected function respondWithToken(string $token) : array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>  $this->jwt->getTTL($token),
        ];
    }

    /**
     * 获取头部菜单数据以及菜单列表
     * @param object $user
     * @return array
     */
    protected function getMenuList(object $user) : array
    {
        //获取菜单树形
        $menuList = Permission::getUserMenuList($user);
        $permission = Permission::getUserPermissions($user);
        $menuHeader = [];
        foreach ($menuList as $key => $val) {
            if ($val['status'] != 0) {
                $menuHeader[] = [
                    'title' => $val['display_name'],
                    'icon' => $val['icon'],
                    'path' => $val['url'],
                    'name' => $val['name'],
                    'id' => $val['id'],
                    'type' => $val['type'],
                    'sort' => $val['sort'],
                ];
            }
        }
        //排序
        array_multisort(array_column($menuHeader, 'sort'), SORT_ASC, $menuHeader);

        return [
            'menuList' => $menuList,
            'menuHeader' => $menuHeader,
            'permission' => array_column($permission, 'name'),
            'permission_info' => $permission,
        ];
    }
}
