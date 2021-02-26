<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\Auth\Permission;
use App\Model\Auth\Role;
use App\Model\Auth\User;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 权限控制器
 * Class PermissionController
 * @Controller(prefix="setting/user_module/permission")
 */
class PermissionController extends AbstractController
{
    /**
     * @Inject()
     * @var Permission
     */
    private $permission;

    /**
     * 获取权限数据列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $permissionQuery = $this->permission->newQuery();
        if (!empty($this->request->input('display_name'))) $permissionQuery->where('display_name', 'like', '%' . $this->request->input('display_name') .'%');
        if (!empty($this->request->input('name'))) $permissionQuery->where('name', 'like', '%' . $this->request->input('name') .'%');
        if (strlen($this->request->input('status') ?? '') > 0) $permissionQuery->where('status', $this->request->input('status'));

        $permissionList = $permissionQuery->get()->toArray();
        $permissionList = array_column($permissionList, null, 'id');

        //使用引用传递递归数组
        $list = [];
        foreach($permissionList as $key => $value){
            if(isset($permissionList[$value['parent_id']])){
                $permissionList[$value['parent_id']]['children'][] = &$permissionList[$key];
            }else{
                $list[] = &$permissionList[$key];
            }
        }

        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * 根据用户获取权限树状列表（用于分配用户权限）
     * @RequestMapping(path="tree_by_user", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function treeByUser()
    {
        $userId = $this->request->all()['user_id'] ?? '';
        if (empty($userId)) $this->throwExp(StatusCode::ERR_VALIDATION, '用户ID缺失');

        //获取用户信息
        $userInfo = User::getOneByUid($userId);

        //获取系统所有启用的功能权限
        $permissionList = Permission::getAllPermissionByTree();

        //获取用户拥有的权限
        $userHasPermission = Permission::getUserPermissions($userInfo);
        $userHasPermission = array_column($userHasPermission, 'name');

        return $this->success([
            'permission_list' => $permissionList,
            'user_has_permission' => $userHasPermission
        ]);
    }

    /**
     * 根据角色获取权限树状列表（用于分配角色权限）
     * @RequestMapping(path="tree_by_role", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function treeByRole()
    {
        $roleId = $this->request->all()['role_id'] ?? '';
        if (empty($roleId)) $this->throwExp(StatusCode::ERR_VALIDATION, '角色ID缺失');

        //获取用户信息
        $roleInfo = Role::getOneByRoleId($roleId);

        //获取系统所有启用的功能权限
        $permissionList = Permission::getAllPermissionByTree();

        //获取角色拥有的权限
        $roleHasPermission = $roleInfo->permissions->toArray();
        $roleHasPermission = array_column($roleHasPermission, 'name');

        return $this->success([
            'permission_list' => $permissionList,
            'role_has_permission' => $roleHasPermission
        ]);
    }

    /**
     * @Explanation(content="添加权限操作")
     * @RequestMapping(path="store", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'parent_id' => $postData['parent_id'] ?? 0,
            'name' => $postData['name'] ?? '',
            'display_name' => $postData['display_name'] ?? '',
            'type' => $postData['type'] ?? ''
        ];
        //配置验证
        $rules = [
            'parent_id' => 'required',
            'name' => 'required',
            'display_name' => 'required',
            'type' => 'required',
        ];
        $message = [
            'parent_id.required' => '[parent_id]缺失',
            'name.required' => '[name]缺失',
            'type.required' => '[type]缺失',
            'display_name.required' => '[display_name]缺失',
        ];
        $this->verifyParams($params, $rules, $message);
        $permission = new Permission();
        $permission->parent_id = $params['parent_id'];
        $permission->type = $params['type'];
        $permission->name = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->display_desc = $postData['display_desc'] ?? '';
        $permission->url = $postData['url'] ?? '';
        $permission->component = $postData['component'] ?? '';
        $permission->guard_name = $postData['guard_name'] ?? '';
        $permission->icon = $postData['icon'] ?? '';
        $permission->hidden = $postData['hidden'] ?? false;
        $permission->status = $postData['status'] ?? 1;
        $permission->sort = $postData['sort'] ?? 99;
        $permission->created_at = date('Y-m-d H:i:s');
        $permission->updated_at = date('Y-m-d H:i:s');

        if (!$permission->save()) $this->throwExp(400, '添加权限失败');

        return $this->successByMessage('添加权限成功');
    }

    /**
     * 获取单个权限的数据
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $permissionInfo = Permission::findById($id);
        if (empty($permissionInfo)) $this->throwExp(StatusCode::ERR_VALIDATION, '获取权限信息失败');

        return $this->success([
            'list' => $permissionInfo
        ]);
    }

    /**
     * @Explanation(content="修改权限操作")
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(int $id)
    {
        $postData = $this->request->all();
        $params = [
            'id' => $id,
            'parent_id' => $postData['parent_id'] ?? 0,
            'name' => $postData['name'] ?? '',
            'display_name' => $postData['display_name'] ?? '',
            'type' => $postData['type'] ?? ''
        ];
        //配置验证
        $rules = [
            'id' => 'required',
            'parent_id' => 'required',
            'name' => 'required',
            'display_name' => 'required',
            'type' => 'required',
        ];
        $message = [
            'id.required' => '[id]缺失',
            'parent_id.required' => '[parent_id]缺失',
            'name.required' => '[name]缺失',
            'type.required' => '[type]缺失',
            'display_name.required' => '[display_name]缺失',
        ];

        $this->verifyParams($params, $rules, $message);
        $permission = Permission::findById($id);
        $permission->parent_id = $params['parent_id'];
        $permission->type = $params['type'];
        $permission->name = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->display_desc = $postData['display_desc'] ?? '';
        $permission->url = $postData['url'] ?? '';
        $permission->component = $postData['component'] ?? '';
        $permission->guard_name = $postData['guard_name'] ?? '';
        $permission->icon = $postData['icon'] ?? '';
        $permission->hidden = $postData['hidden'] ?? false;
        $permission->status = $postData['status'] ?? 1;
        $permission->sort = $postData['sort'] ?? 99;
        $permission->updated_at = date('Y-m-d H:i:s');
        if (!$permission->save()) $this->throwExp(400, '修改权限信息失败');

        return $this->successByMessage('修改权限信息成功');
    }

    /**
     * @Explanation(content="删除权限操作")
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        $params = [
            'id' => $id,
        ];
        //配置验证
        $rules = [
            'id' => 'required',
        ];
        $message = [
            'id.required' => '非法参数',
        ];

        $this->verifyParams($params, $rules, $message);

        if (!Permission::query()->where('id', $id)->delete()) $this->throwExp(400, '删除权限信息失败');

        return $this->successByMessage('删除权限信息成功');
    }

    /**
     * @Explanation(content="分配用户角色")
     * @RequestMapping(path="accord_user_role", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function accordUserRole()
    {
        $postData = $this->request->all() ?? [];
        $params = [
            'user_id' => $postData['user_id'],
            'role_list' => $postData['role_list'],
        ];
        //配置验证
        $rules = [
            'user_id' => 'required',
            'role_list' => 'required|array',
        ];
        $message = [
            'user_id.required' => '[user_id]缺失',
            'role_list.required' => '请至少选择一个角色',
            'role_list.array' => '角色数据格式不正确',
        ];
        $this->verifyParams($params, $rules, $message);

        $userModel = User::getOneByUid($params['user_id']);

        //先清空当前用户所有角色
        Db::table('model_has_roles')
            ->where('user_id', $params['user_id'])
            ->delete();

        if (!$userModel->syncRoles($params['role_list'])) $this->throwExp(StatusCode::ERR_EXCEPTION, '分配用户角色失败');
        return $this->successByMessage( '分配用户角色成功');
    }


    /**
     * @Explanation(content="分配角色权限")
     * @RequestMapping(path="accord_role_permission", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function accordRolePermission()
    {
        $postData = $this->request->all() ?? [];
        $params = [
            'role_id' => $postData['role_id'],
            'role_has_permission' => $postData['role_has_permission'],
        ];
        //配置验证
        $rules = [
            'role_id' => 'required|int',
            'role_has_permission' => 'required|array',
        ];
        $message = [
            'role_id.required' => '[role_id]缺失',
            'role_id.int' => '[role_id]参数格式不正确',
            'role_has_permission.required' => '请至少选择一个权限',
            'role_has_permission.array' => '权限数据格式不正确',
        ];
        $this->verifyParams($params, $rules, $message);

        $roleModel = Role::findById(intval($params['role_id']));

        //先清空当前角色所有权限
        Db::table('role_has_permissions')
            ->where('role_id', $params['role_id'])
            ->delete();

        if (!$roleModel->syncPermissions($params['role_has_permission'])) $this->throwExp(StatusCode::ERR_EXCEPTION, '分配角色权限失败');
        return $this->successByMessage( '分配角色权限成功');
    }

    /**
     * @Explanation(content="分配用户权限")
     * @RequestMapping(path="accord_user_permission", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function accordUserPermission()
    {
            $postData = $this->request->all() ?? '';
            $params = [
                'user_id' => $postData['user_id'] ?? '',
                'user_has_permission' => $postData['user_has_permission'] ?? ''
            ];
            $rules = [
                'user_id' => 'required|int',
                'user_has_permission' => 'required|array',
            ];
            $message = [
                'user_id.required' => '[user_id]缺失',
                'user_id.int' => '[user_id]参数类型错误',
                'user_has_permission.required' => '请至少选择一个权限',
                'user_has_permission.array' => '权限数据格式不正确',
            ];
            $this->verifyParams($params, $rules, $message);

            //根据用户信息
            $userModel = User::query()->where('id', $params['user_id'])->first();

            //获取该用户拥有的角色以及角色对应权限
            $roleName = $userModel->getRoleNames();
            $roleList = Role::query()->whereIn('name', $roleName)->get();
            $roleHasPermission = [];
            foreach ($roleList as $role)  {
                $roleHasPermission = array_merge($roleHasPermission, array_column($role->permissions->toArray(), 'name'));
            }
            $roleHasPermission = array_values(array_unique($roleHasPermission));
            $userHasPermission = array_diff($params['user_has_permission'], $roleHasPermission);

            //先清空当前用户所有权限
            DB::table('model_has_permissions')
                ->where('model_id', $params['user_id'])
                ->delete();
            //分配用户权限
            if (!$userModel->syncPermissions($userHasPermission)) $this->throwExp(StatusCode::ERR_EXCEPTION, '分配用户权限失败');
            return $this->successByMessage('分配用户权限成功');
    }
}
