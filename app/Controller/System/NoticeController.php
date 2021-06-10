<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\System\Notice;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 系统通知控制器
 * Class NoticeController
 * @package App\Controller\System
 * @Controller(prefix="setting/system_module/notice")
 * @Author YiYuan-Lin
 * @Date: 2021/3/3
 */
class NoticeController extends AbstractController
{
    /**
     * @Inject()
     * @var Notice
*/
    private $notice;

    /**
     * 获取系统通知列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $noticeQuery = $this->notice->newQuery();
        $status = $this->request->input('status') ?? '';
        $title = $this->request->input('title') ?? '';

        if (strlen($status) > 0) $noticeQuery->where('status', $status);
        if (!empty($title)) $noticeQuery->where('title', 'like', '%'. $title . '%');

        $total = $noticeQuery->count();
        $noticeQuery->with('getUserName:id,desc');
        $noticeQuery->orderBy('created_at', 'desc');
        $noticeQuery = $this->pagingCondition($noticeQuery, $this->request->all());
        $data = $noticeQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * @Explanation(content="添加系统通知")
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
            'content' => $postData['content'] ?? '',
            'status' => $postData['status'] ?? '',
            'public_time' => $postData['public_time'] ?? '',
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'status' => 'required|integer',
            'content' => 'required',
            'public_time' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'status.required' => '[status]缺失',
            'status.integer' => '[status]类型不正确',
            'content.required' => '[content]缺失',
            'public_time.required' => '[public_time]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $noticeQuery = new Notice();
        $noticeQuery->title = $params['title'];
        $noticeQuery->status = $params['status'];
        $noticeQuery->content = $params['content'];
        $noticeQuery->public_time = strtotime($params['public_time']);
        $noticeQuery->user_id = conGet('user_info')['id'];
        $noticeQuery->username = conGet('user_info')['desc'];

        if (!$noticeQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加系统通知错误');

        return $this->successByMessage('添加系统通知成功');
    }

    /**
     * 获取单个系统通知信息
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $noticeInfo = Notice::findById($id);
        $noticeInfo->public_time = date('Y-m-d H:i:s', $noticeInfo->public_time);
        if (empty($noticeInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取字典信息失败');

        return $this->success([
            'list' => $noticeInfo
        ]);
    }

    /**
     * @Explanation(content="修改系统通知信息")
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
            'content' => $postData['content'] ?? '',
            'status' => $postData['status'] ?? '',
            'public_time' => $postData['public_time'] ?? '',
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'status' => 'required|integer',
            'content' => 'required',
            'public_time' => 'required',
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'status.required' => '[status]缺失',
            'status.integer' => '[status]类型不正确',
            'content.required' => '[content]缺失',
            'public_time.required' => '[public_time]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $noticeQuery = Notice::findById($id);
        $noticeQuery->title = $params['title'];
        $noticeQuery->status = $params['status'];
        $noticeQuery->content = $params['content'];
        $noticeQuery->public_time = strtotime($params['public_time']);

        if (!$noticeQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改系统通知错误');

        return $this->successByMessage('修改系统通知成功');
    }

    /**
     * @Explanation(content="删除系统通知")
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
        if (!Notice::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除系统通知成功');
    }

}