<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use Donjan\Permission\Models\Permission;
use Donjan\Permission\Models\Role;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
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
     * @Middleware(RequestMiddleware::class)
     */
    public function index()
    {
        $permissionQuery = $this->permission->newQuery();

        $total = $permissionQuery->count();
        $permissionQuery = $this->pagingCondition($permissionQuery, $this->request->all());
        //判断是否有查询条件
        if (!empty($this->request->input('display_name'))) $permissionQuery->where('display_name', 'like', '%' . $this->request->input('display_name') . '%');
        $list = $permissionQuery->get();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    /**
     * 添加权限
     * @RequestMapping(path="store", methods="post")
     * @Middleware(RequestMiddleware::class)
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'parent_id' => $postData['parent_id'] ?? 0,
            'name' => $postData['name'] ?? '',
            'display_name' => $postData['display_name'] ?? '',
        ];
        //配置验证
        $rules = [
            'name' => 'required',
            'display_name' => 'required',
        ];
        $message = [
            'name.required' => '[name]缺失',
            'display_name.required' => '[display_name]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        if (!Permission::create($params)) $this->throwExp(400, '添加权限失败');

        return $this->successByMessage('添加权限成功');
    }

    /**
     * 修改权限
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(int $id)
    {
        $postData = $this->request->all();
        $params = [
            'id' => $id,
            'name' => $postData['name'] ?? '',
            'parent_id' => $postData['parent_id'] ?? '',
            'display_name' => $postData['display_name'] ?? ''
        ];
        //配置验证
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'parent_id' => 'required',
            'display_name' => 'display_name',
        ];
        $message = [
            'id.required' => '非法参数',
            'name.required' => '[name]缺失',
            'parent_id.required' => '[parent_id]缺失',
            'display_name.required' => '[display_name]缺失',
        ];

        $this->verifyParams($params, $rules, $message);
        if (!Permission::query()->where('id', $id)->update($params)) $this->throwExp(400, '修改权限信息失败');

        return $this->successByMessage('修改权限信息成功');
    }

    /**
     * 修改角色
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middleware(RequestMiddleware::class)
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
     * 分配用户角色
     * @RequestMapping(path="accord_user_role", methods="post")
     * @Middleware(RequestMiddleware::class)
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
     * 分配角色权限
     * @RequestMapping(path="accord_role_permission", methods="post")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function accordRolePermission()
    {
        $postData = $this->request->all() ?? [];
        $params = [
            'role_id' => $postData['role_id'],
            'permission_list' => $postData['permission_list'],
        ];
        //配置验证
        $rules = [
            'role_id' => 'required',
            'permission_list' => 'required|array',
        ];
        $message = [
            'role_id.required' => '[role_id]缺失',
            'permission_list.required' => '请至少选择一个权限',
            'permission_list.array' => '权限数据格式不正确',
        ];
        $this->verifyParams($params, $rules, $message);

        $roleModel = Role::findById(intval($params['role_id']));

        //先清空当前角色所有权限
        Db::table('role_has_permissions')
            ->where('role_id', $params['role_id'])
            ->delete();

        if (!$roleModel->syncPermissions($params['permission_list'])) $this->throwExp(StatusCode::ERR_EXCEPTION, '分配角色权限失败');
        return $this->successByMessage( '分配角色权限成功');
    }

    /**
     * 分配角色权限
     * @RequestMapping(path="accord_user_permission", methods="post")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function accordUserPermission()
    {
            $postData = $this->request->all() ?? '';
            $params = [
                'user_id' => $postData['user_id'] ?? '',
                'permission_list' => $postData['permission_list'] ?? ''
            ];
            $rules = [
                'user_id' => 'required',
                'permission_list' => 'required|array',
            ];
            $message = [
                'user_id.required' => '[user_id]缺失',
                'permission_list.required' => '请至少选择一个权限',
                'permission_list.array' => '权限数据格式不正确',
            ];
            $this->verifyParams($params, $rules, $message);

            //根据用户获取相应所有权限列表
            $userModel = User::query()->where('id', $params['user_id'])->first();
            //先清空当前用户所有权限
            DB::table('model_has_permissions')
                ->where('model_id', $params['user_id'])
                ->delete();

            if (!$userModel->syncPermissions($params['permission_list'])) $this->throwExp(StatusCode::ERR_EXCEPTION, '分配用户权限失败');

            return $this->successByMessage('分配用户权限成功');
    }
}
