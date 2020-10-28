<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\IndexService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;

/**
 * Class IndexController
 * @Controller
 */
class IndexController extends AbstractController
{

    /**
     * 获取用户数据列表
     * @RequestMapping(path="/test", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function index()
    {
        return $this->success([
            IndexService::test()
        ]);
    }

}
