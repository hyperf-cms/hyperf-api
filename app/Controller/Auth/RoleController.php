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
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 角色控制器
 * Class RoleController
 * @Controller(prefix="setting/user_module/role")
 */
class RoleController extends AbstractController
{
    /**
     * @Inject()
     * @var Role
     */
    private $role;

    /**
     * 获取角色数据列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
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
     * 获取角色数据列表
     * @RequestMapping(path="tree", methods="get")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function tree()
    {
        $roleQuery = $this->role->newQuery();

        $list = $roleQuery->select('name', 'description')->get()->toArray();

        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * @Explanation(content="添加角色操作")
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
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $roleInfo = Role::getOneByRoleId($id);
        if (empty($roleInfo)) $this->throwExp(StatusCode::ERR_VALIDATION, '获取角色信息失败');

        return $this->success([
            'list' => $roleInfo
        ]);
    }

    /**
     * @Explanation(content="修改角色操作")
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
     * @Explanation(content="删除角色操作")
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

        if (!Role::query()->where('id', $id)->delete()) $this->throwExp(400, '删除角色信息失败');

        return $this->successByMessage('删除角色信息成功');
    }

}
