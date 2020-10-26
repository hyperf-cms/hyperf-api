<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use Donjan\Permission\Models\Role;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Phper666\JWTAuth\JWT;

/**
 * 角色控制器
 * Class RoleController
 * @Controller(prefix="role")
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
     * @Middleware(RequestMiddleware::class)
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
     * 添加角色
     * @RequestMapping(path="store", methods="post")
     * @Middleware(RequestMiddleware::class)
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
     * 修改角色
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

        if (!Role::query()->where('id', $id)->delete()) $this->throwExp(400, '删除角色信息失败');

        return $this->successByMessage('删除角色信息成功');
    }

}
