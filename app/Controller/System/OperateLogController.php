<?php

namespace App\Http\Controllers\System;

use App\Controller\AbstractController;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;


/**
 * Class MenuController
 * @Controller(prefix="setting/log_module/operate_log")
 * @package App\Controller\System
 * @Author YiYuan-Lin
 * @Date: 2020/11/7
 */
class OperateLogController extends AbstractController
{
    /**
     * @Inject()
     * @var OperateLog
     */
    protected $operate;

    /**
     * 获取操作日志列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function list()
    {
        $beginTime = $this->request->input('time')[0] ?? '';
        $endTime = $this->request->input('time')[1] ?? '';
        $userId = $this->request->input('user_id') ?? '';
        $operate = $this->request->input('operator') ?? '';

        $operateLogQuery = $this->operate->newQuery();
        if (!empty($beginTime) && !empty($endTime)) $operateLogQuery->whereBetween('created_at', [$beginTime, $endTime]);
        if (!empty($userId)) $operateLogQuery->where('user_id', $userId);
        if (!empty($operate)) $operateLogQuery->where('operate', $operate);
        $operateLogQuery->orderBy('created_at', 'desc');

        $total = $operateLogQuery->count();
        $operateLogQuery = $this->pagingCondition($operateLogQuery, $this->request->all());
        $data = $operateLogQuery->get()->toArray();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }
}