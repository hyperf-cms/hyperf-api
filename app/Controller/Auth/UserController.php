<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\AbstractController;
use App\Model\Auth\User;
use Donjan\Permission\Models\Permission;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 用户控制器
 * Class UserController
 * @Controller(prefix="user")
 */
class UserController extends AbstractController
{
    /**
     * @Inject()
     * @var User
     */
    private $user;

    /**
     * 获取用户数据列表
     * @RequestMapping(path="list", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function index()
    {
        $userQuery = $this->user->newQuery();

        $total = $userQuery->count();
        $userQuery = $this->pagingCondition($userQuery, $this->request->all());
        //判断是否有查询条件
        if (!empty($this->request->input('desc'))) $userQuery->where('desc', 'like', '%' . $this->request->input('desc') . '%');
        $list = $userQuery->get();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    /**
     * 添加用户
     * @RequestMapping(path="store", methods="post")
     * @Middleware(RequestMiddleware::class)
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'username' => $postData['username'] ?? '',
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

        if (!User::create($params)) $this->throwExp(400, '添加权限失败');

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
}
