<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\System\Advice;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 系统建议控制器
 * Class IndexController
 * @Controller(prefix="setting/system_module/advice")
 */
class AdviceController extends AbstractController
{
    /**
     * @Inject()
     * @var Advice
     */
    private $advice;

    /**
     * 获取系统建议列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $adviceQuery = $this->advice->newQuery();
        $status = $this->request->input('status') ?? '';
        $type = $this->request->input('type') ?? '';

        if (strlen($status) > 0) $adviceQuery->where('status', $status);
        if (strlen($type) > 0) $adviceQuery->where('type', $type);

        $total = $adviceQuery->count();
        $adviceQuery->with('getUserName:id,desc');
        $adviceQuery->orderBy('created_at', 'desc');
        $adviceQuery = $this->pagingCondition($adviceQuery, $this->request->all());
        $data = $adviceQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * @Explanation(content="添加系统建议")
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
            'title' => $postData['title'] ?? '',
            'type' => $postData['type'] ?? '',
            'content' => $postData['content'] ?? '',
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'type' => 'required|integer',
            'content' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'type.required' => '[type]缺失',
            'type.integer' => '[type]类型不正确',
            'content.required' => '[content]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $adviceQuery = new Advice();
        $adviceQuery->title = $params['title'];
        $adviceQuery->type = $params['type'];
        $adviceQuery->content = $params['content'];
        $adviceQuery->reply = '';
        $adviceQuery->status = 0;
        $adviceQuery->user_id = conGet('user_info')['id'];

        if (!$adviceQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加系统建议错误');

        var_dump($adviceQuery->created_at);
        return $this->successByMessage('添加系统建议成功');
    }

    /**
     * 获取单个系统建议信息
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $adviceInfo = Advice::findById($id);
        if (empty($adviceInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取字典信息失败');

        return $this->success([
            'list' => $adviceInfo
        ]);
    }

    /**
     * @Explanation(content="修改系统建议信息")
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
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();
        $params = [
            'title' => $postData['title'] ?? '',
            'type' => $postData['type'] ?? '',
            'content' => $postData['content'] ?? '',
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'type' => 'required|integer',
            'content' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'type.required' => '[type]缺失',
            'type.integer' => '[type]类型不正确',
            'content.required' => '[content]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $adviceQuery = Advice::findById($id);
        $adviceQuery->title = $params['title'];
        $adviceQuery->type = $params['type'];
        $adviceQuery->content = $params['content'];

        if (!$adviceQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改系统建议错误');

        return $this->successByMessage('修改系统建议成功');
    }

    /**
     * @Explanation(content="回复建议")
     * @param int $id
     * @RequestMapping(path="reply/{id}", methods="put")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function reply(int $id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();
        $params = [
            'reply' => $postData['reply'] ?? '',
            'status' => $postData['status'] ?? '',
        ];
        //配置验证
        $rules = [
            'reply' => 'required',
            'status' => 'required|integer',
        ];
        //错误信息
        $message = [
            'reply.required' => '[reply]缺失',
            'status.required' => '[status]缺失',
            'status.integer' => '[status]类型不正确',
        ];
        $this->verifyParams($params, $rules, $message);

        $adviceQuery = Advice::findById($id);
        $adviceQuery->reply = $params['reply'];
        $adviceQuery->status = $params['status'];

        if (!$adviceQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '回复系统建议错误');

        return $this->successByMessage('回复系统建议成功');
    }

    /**
     * @Explanation(content="删除系统建议")
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
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!Advice::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除系统建议成功');
    }

}