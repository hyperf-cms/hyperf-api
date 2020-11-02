<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\IndexService;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;

 /**
 * 测试控制器，一般用来测试一些代码
 * Class IndexController
 * @Controller
 */
class IndexController extends AbstractController
{
    /**
     * 获取用户数据列表
     * @RequestMapping(path="/test", methods="get,post")
     * @Middleware(RequestMiddleware::class)
     */
    public function index()
    {
        IndexService::getInstance()->params =  conSet('params', $this->request->all());
        return IndexService::getInstance()->test();
    }

    /**
     * 获取用户数据列表
     * @RequestMapping(path="/test1", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function test()
    {
        return IndexService::getInstance()->test();
    }
}
