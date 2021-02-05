<?php
namespace App\Http\Service\Auth;

use App\Constants\StatusCode;
use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;
use App\Model\Auth\Permission;
use App\Model\Auth\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use Phper666\JWTAuth\JWT;

/**
 * 登陆服务基础类
 * Class LoginService
 * @package App\Http\Service\Auth
 * @Author YiYuan-Lin
 * @Date: 2020/10/29
 */
class LoginService extends BaseService
{
    use Singleton;

    /**
     * @Inject()
     * @var JWT
     */
    private $jwt;

    /**
     * 处理登陆逻辑
     * @param array $params
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
        $token = $this->jwt->getToken($userData);

        //更新用户信息
        $user->last_login = time();
        $user->last_ip = getClientIp($this->request);
        $user->save();
        $responseData = $this->respondWithToken($token);

        return $responseData;
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
        $permissionList = permission::getUserPermissions($userInfo);
        $permissionList = objToArray($permissionList);
        $permissionList = array_column($permissionList, null, 'id');

        foreach ($permissionList as $key => $val) {
            if ($val['status'] == permission::OFF_STATUS) unset($permissionList[$key]);
            if ($val['type'] != permission::MENU_TYPE) unset($permissionList[$key]);
        }

        //使用引用传递递归数组
        $routers = [];
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
                    'hidden' => $value['hidden'] ? true : false,
                    'alwaysShow' => true,
                    'component' => $value['component'],
                    'meta' => [
                        'icon' => $value['icon'],
                        'title' => $value['display_name'],
                    ],
                    'children' => []
                ];
                foreach ($value['children'] as $k => $v) {
                    $temp = [];
                    if (!empty($v['children'])) {
                        foreach ($v['children'] as $k1 => $v1) {
                            $temp[] = [
                                'name' => $v1['name'],
                                'path' => $v1['url'],
                                'hidden' => $v1['hidden'] ? true : false,
                                'alwaysShow' => true,
                                'component' => $v1['component'],
                                'meta' => [
                                    'icon' => $v1['icon'],
                                    'title' => $v1['display_name'],
                                ],
                            ];
                        }
                    }
                    $routers[$value['id']]['children'] =  array_merge($routers[$value['id']]['children'], $temp);
                }
            }
        }

        return array_values($routers);
    }

    /**
     * 处理TOKEN数据
     * @param $token
     * @return array
     */
    protected function respondWithToken(string $token) : array
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>  $this->jwt->getTTL(),
        ];
        return $data;
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
            if ($val['status'] != 0 && !empty($val['child'])) {
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
