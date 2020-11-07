<?php

namespace App\Http\Controllers\System;

use App\Controller\AbstractController;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class MenuController
 * @Controller(prefix="operate_log")
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
     * 获取菜单列表
     * @RequestMapping(path="list", methods="get")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index()
    {
        $beginTime = $this->request->input('time')[0] ?? '';
        $endTime = $this->request->input('time')[1] ?? '';
        $userId = $this->request->input('user_id') ?? '';
        $operate = $this->request->input('operator') ?? '';

        $operateLogQuery = $this->operate->newQuery();
        if (!empty($beginTime) && !empty($endTime)) $operateLogQuery->whereBetween('created_at', [$beginTime, $endTime]);
        if (!empty($userId)) $operateLogQuery->where('user_id', $userId);
        if (!empty($operate)) $operateLogQuery->where('operate', $operate);

        $total = $operateLogQuery->count();
        $data = $operateLogQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }
}