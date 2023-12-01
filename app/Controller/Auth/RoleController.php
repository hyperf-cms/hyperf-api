<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\Auth\Role;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 角色控制器
 * Class RoleController
 */
#[Controller(prefix: 'setting/user_module/role')]
class RoleController extends AbstractController
{
    #[Inject]
    private Role $role;

    /**
     * 获取角色数据列表
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('get'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $roleQuery = $this->role->newQuery();

        $total = $roleQuery->count();
        $roleQuery = $this->pagingCondition($roleQuery, $this->request->all());
        //判断是否有查询条件
        if (!empty($this->request->input('description'))) $roleQuery->where('description', 'like', '%' . $this->request->input('description') . '%');
        $list = $roleQuery->get();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    /**
     * 获取角色数据树状列表
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'tree', methods: array('get'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function tree()
    {
        $roleQuery = $this->role->newQuery();

        $list = $roleQuery->select('name', 'description')->get()->toArray();

        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * 添加角色操作
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '添加角色操作')]
    #[RequestMapping(path: 'store', methods: array('post'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'name' => $postData['name'] ?? '',
            'description' => $postData['description'] ?? '',
        ];
        //配置验证
        $rules = [
            'name' => 'required',
        ];
        $message = [
            'name.required' => '[name]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        if (!Role::create($params)) $this->throwExp(400, '添加角色失败');

        return $this->successByMessage('添加角色成功');
    }

    /**
     * 获取单个角色的数据
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'edit/{id}', methods: array('get'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function edit(int $id)
    {
        $roleInfo = Role::getOneByRoleId($id);
        if (empty($roleInfo)) $this->throwExp(StatusCode::ERR_VALIDATION, '获取角色信息失败');

        return $this->success([
            'list' => $roleInfo
        ]);
    }

    /**
     * 修改角色操作
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '修改角色操作')]
    #[RequestMapping(path: 'update/{id}', methods: array('put'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function update(int $id)
    {
        $postData = $this->request->all();
        $params = [
            'id' => $id,
            'name' => $postData['name'],
            'description' => $postData['description']
        ];
        //配置验证
        $rules = [
            'id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'id.required' => '非法参数',
            'name.required' => '[name]缺失',
        ];

        $this->verifyParams($params, $rules, $message);

        if (!Role::query()->where('id', $id)->update($params)) $this->throwExp(400, '修改角色信息失败');

        return $this->successByMessage('修改角色信息成功');
    }

    /**
     * 删除角色操作
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '删除角色操作')]
    #[RequestMapping(path: 'destroy/{id}', methods: array('delete'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
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

        if (!Role::query()->where('id', $id)->delete()) $this->throwExp(400, '删除角色信息失败');

        return $this->successByMessage('删除角色信息成功');
    }
}
