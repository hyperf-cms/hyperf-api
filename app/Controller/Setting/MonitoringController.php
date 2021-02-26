<?php

declare(strict_types=1);

namespace App\Controller\Setting;

use App\Controller\AbstractController;
use App\Http\Service\Setting\ServeMonitorService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
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
     * @RequestMapping(path="serve", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function serve()
    {
        $cpuInfo = ServeMonitorService::getInstance()->getCpuInfo();

        $memoryInfo = ServeMonitorService::getInstance()->getMemoryInfo();

        $uptime = ServeMonitorService::getInstance()->getUptime();

        $serveInfo = ServeMonitorService::getInstance()->getServeInfo();

        return $this->success([
            'cpu_info' => $cpuInfo,
            'memory_info' => $memoryInfo,
            'uptime' => $uptime,
            'serve_info' => $serveInfo,
        ]);
    }
}
