<?php

declare (strict_types=1);
namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Queue;
use App\Job\EmailNotificationJob;
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
 * @Author YiYuan-Lin
 * @Date: 2021/3/3
 */
#[Controller(prefix: 'setting/system_module/notice')]
class NoticeController extends AbstractController
{
    #[Inject]
    private Notice $notice;
    
    #[Inject]
    private Queue $queue;

    /**
     * 列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $noticeQuery = $this->notice->newQuery();
        $status = $this->request->input('status') ?? '';
        $title = $this->request->input('title') ?? '';
        if (strlen($status) > 0) {
            $noticeQuery->where('status', $status);
        }
        if (!empty($title)) {
            $noticeQuery->where('title', 'like', '%' . $title . '%');
        }
        $total = $noticeQuery->count();
        $noticeQuery->with('getUserName:id,desc');
        $noticeQuery->orderBy('created_at', 'desc');
        $noticeQuery = $this->pagingCondition($noticeQuery, $this->request->all());
        $data = $noticeQuery->get()->toArray();
        return $this->success([
            'list' => $data,
            'total' => $total
        ]);
    }

    /**
     * 添加
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '添加公告操作')]
    #[RequestMapping(path: 'store', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function store()
    {
        $params = [
            'title' => $this->params('title') ?? '',
            'content' => $this->params('content') ?? '',
            'status' => $this->params('status') ?? '',
            'public_time' => $this->params('public_time') ?? ''
        ];
        $rules = [
            'title' => 'required',
            'status' => 'required|integer',
            'content' => 'required',
            'public_time' => 'required'
        ];
        $message = [
            'title.required' => '[title]缺失',
            'status.required' => '[status]缺失',
            'status.integer' => '[status]类型不正确',
            'content.required' => '[content]缺失',
            'public_time.required' => '[public_time]缺失'
        ];
        $this->verifyParams($params, $rules, $message);
        $noticeQuery = new Notice();
        $noticeQuery->title = $params['title'];
        $noticeQuery->status = $params['status'];
        $noticeQuery->content = $params['content'];
        $noticeQuery->public_time = strtotime($params['public_time']);
        $noticeQuery->user_id = conGet('user_info')['id'];
        $noticeQuery->username = conGet('user_info')['desc'];
        if (!$noticeQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '添加系统通知错误');
        }
        //分发队列
        $this->queue->push(new EmailNotificationJob(['title' => $params['title'], 'content' => $params['content']]));
        return $this->successByMessage('添加系统通知成功');
    }

    /**
     * 获取编辑数据
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'edit/{id}', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function edit(int $id)
    {
        $noticeInfo = Notice::findById($id);
        $noticeInfo->public_time = date('Y-m-d H:i:s', $noticeInfo->public_time);
        if (empty($noticeInfo)) {
            $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取字典信息失败');
        }
        return $this->success(['list' => $noticeInfo]);
    }

    /**
     * 修改公告
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '修改公告操作')]
    #[RequestMapping(path: 'update/{id}', methods: array('PUT'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function update(int $id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $params = [
            'title' => $this->params('title') ?? '',
            'content' => $this->params('content') ?? '',
            'status' => $this->params('status') ?? '',
            'public_time' => $this->params('public_time') ?? ''
        ];
        //配置验证
        $rules = [
            'title' => 'required',
            'status' => 'required|integer',
            'content' => 'required',
            'public_time' => 'required'
        ];
        //错误信息
        $message = [
            'title.required' => '[title]缺失',
            'status.required' => '[status]缺失',
            'status.integer' => '[status]类型不正确',
            'content.required' => '[content]缺失',
            'public_time.required' => '[public_time]缺失'
        ];
        $this->verifyParams($params, $rules, $message);
        $noticeQuery = Notice::findById($id);
        $noticeQuery->title = $params['title'];
        $noticeQuery->status = $params['status'];
        $noticeQuery->content = $params['content'];
        $noticeQuery->public_time = strtotime($params['public_time']);
        if (!$noticeQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '修改系统通知错误');
        }
        return $this->successByMessage('修改系统通知成功');
    }

    /**
     * 删除
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '删除公告操作')]
    #[RequestMapping(path: 'destroy/{id}', methods: array('DELETE'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function destroy(int $id)
    {
        if ($id == 0) {
            $idArr = $this->request->input('id') ?? [];
            if (empty($idArr) || !is_array($idArr)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数类型不正确');
            }
            if (!Notice::whereIn('id', $idArr)->delete()) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        } else {
            if (!intval($id)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
            }
            if (!Notice::destroy($id)) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        }
        return $this->successByMessage('删除系统通知成功');
    }
}