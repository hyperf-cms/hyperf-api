<?php

declare(strict_types=1);

namespace App\Controller\Setting;

use App\Controller\AbstractController;
use App\Http\Service\Setting\ServerMonitorService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 监控控制器
 * Class IndexController
 * @Controller(prefix="setting/monitoring_module")
 */
class MonitoringController extends AbstractController
{
    /**
     * 获取服务监控
     * @RequestMapping(path="server", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     */
    public function server()
    {
        $cpuInfo = ServerMonitorService::getInstance()->getCpuInfo();

        $memoryInfo = ServerMonitorService::getInstance()->getMemoryInfo();

        $uptime = ServerMonitorService::getInstance()->getUptime();



        return getrusage();
    }
}
