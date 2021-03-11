<?php
declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Controller\AbstractController;
use App\Pool\Redis;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 好友模块
 * Class FriendController
 * @Controller(prefix="laboratory/chat_module/friend")
 */
class FriendController extends AbstractController
{
    /**
     * 好友列表
     * @RequestMapping(path="list", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class),
     * })
     */
    public function index()
    {

    }

    /**
     * 添加好友
     * @RequestMapping(path="store", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class),
     * })
     */
    public function store()
    {

    }
}
