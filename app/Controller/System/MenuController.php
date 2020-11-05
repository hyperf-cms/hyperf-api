<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\System\Menu;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class MenuController
 * @Controller(prefix="menu")
 * @package App\Controller\System
 * @Author YiYuan-Lin
 * @Date: 2020/11/4
 */
class MenuController extends AbstractController
{
    /**
     * @Inject()
     * @var Menu
     */
    protected $menu;
    /**
     * 获取菜单列表
     * @RequestMapping(path="list", methods="get")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index()
    {
        $menuQuery = $this->menu->newQuery();

        $status = $this->params['status'] ?? '';
        if (!empty($this->request->input('id'))) $menuQuery->where('id', $this->request->input('id'));
        if (strlen($status)) $menuQuery->where('status', $status);
        $total = $menuQuery->count();
        $data = $menuQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * 添加菜单
     * @RequestMapping(path="store", methods="post")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'title' => $postData['title'] ?? '',
            'app' => $postData['app'] ?? '',
            'parent_id' => $postData['parent_id'] ?? 0,
            'path' => $postData['path'] ?? '',
            'icon' => $postData['icon'] ?? '',
            'status' => $postData['status'] ?? 1,
            'sort' => $postData['sort'] ?? 99,
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'app' => 'required|unique:menu',
            'path' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'app.unique' => '该应用标识已经存在',
            'app.required' => '[app]缺失',
            'path.required' => '[path]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $menuModel = new Menu();
        $menuModel->title = $postData['username'];
        $menuModel->app = $postData['app'];
        $menuModel->parent_id = $postData['parent_id'] ?? 0;
        $menuModel->path = $postData['path'] ?? '';
        $menuModel->icon = $postData['icon'] ?? '';
        $menuModel->status = $postData['status'] ?? 1;
        if (!$menuModel->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加菜单失败');

        return $this->successByMessage('添加菜单成功');
    }

    /**
     * 获取菜单单挑记录
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $menuInfo = Menu::findById($id);
        if (empty($menuInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取菜单信息失败');

        return $this->success([
            'list' => $menuInfo
        ]);
    }

    /**
     * 修改菜单
     * @RequestMapping(path="update/{id}", methods="put")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update()
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();

        $params = [
            'title' => $postData['title'] ?? '',
            'app' => $postData['app'] ?? '',
            'parent_id' => $postData['parent_id'] ?? 0,
            'path' => $postData['path'] ?? '',
            'icon' => $postData['icon'] ?? '',
            'status' => $postData['status'] ?? 1,
            'sort' => $postData['sort'] ?? 99,
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'app' => 'required|unique:menu',
            'path' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'app.unique' => '该应用标识已经存在',
            'app.required' => '[app]缺失',
            'path.required' => '[path]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $menuModel = Menu::findById($id);
        $menuModel->title = $postData['username'];
        $menuModel->app = $postData['app'];
        $menuModel->parent_id = $postData['parent_id'] ?? 0;
        $menuModel->path = $postData['path'] ?? '';
        $menuModel->icon = $postData['icon'] ?? '';
        $menuModel->status = $postData['status'] ?? 1;
        if (!$menuModel->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改菜单失败');

        return $this->successByMessage('修改菜单成功');
    }

    /**
     * 删除菜单
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!Menu::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除菜单失败');

        return $this->successByMessage('删除菜单成功');
    }

}