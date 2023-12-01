<?php

declare (strict_types=1);
namespace App\Controller\System;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
/**
 * 首页数据控制器
 * Class IndexController
 */
#[Controller(prefix: 'common')]
class HomePageController extends AbstractController
{
    /**
     * 获取首页数据
     */
    #[RequestMapping(methods: array('GET'), path: 'home_data')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function index()
    {
        return $this->success();
    }
}