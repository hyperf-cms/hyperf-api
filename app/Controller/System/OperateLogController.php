<?php

namespace App\Controller\System;

use App\Controller\AbstractController;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OperateLogController
 * @package App\Controller\System
 * @Author YiYuan-Lin
 * @Date: 2020/11/7
 */
#[Controller(prefix: 'setting/log_module/operate_log')]
class OperateLogController extends AbstractController
{
    #[Inject]
    protected OperateLog $operate;

    /**
     * 操作日志列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $beginTime = $this->request->input('created_at')[0] ?? '';
        $endTime = $this->request->input('created_at')[1] ?? '';
        $action = $this->request->input('action') ?? '';
        $operator = $this->request->input('operator') ?? '';
        $status = $this->request->input('status') ?? '';
        $operateLogQuery = $this->operate->newQuery();
        if (!empty($beginTime) && !empty($endTime)) {
            $operateLogQuery->whereBetween('created_at', [$beginTime, $endTime]);
        }
        if (!empty($action)) {
            $operateLogQuery->where('action', 'like', '%' . $action . '%');
        }
        if (!empty($operate)) {
            $operateLogQuery->where('operate', 'like', '%' . $operator . '%');
        }
        if (strlen($status) > 0) {
            if ($status == 0) {
                $operateLogQuery->where('response_code', '!=', 200);
            }
            if ($status == 1) {
                $operateLogQuery->where('response_code', 200);
            }
        }
        $operateLogQuery->orderBy('created_at', 'desc');
        $total = $operateLogQuery->count();
        $operateLogQuery = $this->pagingCondition($operateLogQuery, $this->request->all());
        $data = $operateLogQuery->get()->toArray();
        return $this->success([
            'list' => $data,
            'total' => $total
        ]);
    }
}