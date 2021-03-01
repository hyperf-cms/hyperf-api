<?php

namespace App\Http\Controllers\System;

use App\Controller\AbstractController;
use App\Model\System\LoginLog;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;


/**
 * 登陆日志
 * Class LoginLogController
 * @Controller(prefix="setting/log_module/login_log")
 * @package App\Http\Controllers\System
 * @Author YiYuan-Lin
 * @Date: 2021/3/1
 */
class LoginLogController extends AbstractController
{
    /**
     * @Inject()
     * @var LoginLog
     */
    protected $loginLog;

    /**
     * 获取登陆日志列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index()
    {
        $beginTime = $this->request->input('login_date')[0] ?? '';
        $endTime = $this->request->input('login_date')[1] ?? '';
        $loginIp = $this->request->input('login_ip') ?? '';;
        $status = $this->request->input('status') ?? '';
        $username = $this->request->input('username') ?? '';

        $loginLogQuery = $this->loginLog->newQuery();
        if (!empty($beginTime) && !empty($endTime)) $loginLogQuery->whereBetween('created_at', [$beginTime, $endTime]);
        if (!empty($loginIp)) $loginLogQuery->where('login_ip', 'like', '%' . $loginIp . '%');
        if (!empty($username)) $loginLogQuery->where('username', 'like', '%' . $username . '%');
        if (strlen($status) > 0) {
            if ($status == 0) $loginLogQuery->where('response_code', '!=', 200);
            if ($status == 1) $loginLogQuery->where('response_code', 200);
        }
        $loginLogQuery->orderBy('login_date', 'desc');

        $total = $loginLogQuery->count();
        $loginLogQuery = $this->pagingCondition($loginLogQuery, $this->request->all());
        $data = $loginLogQuery->get()->toArray();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }
}