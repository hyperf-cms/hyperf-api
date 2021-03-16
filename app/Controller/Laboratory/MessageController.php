<?php

declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Controller\AbstractController;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Pool\Redis;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 信息模块
 * Class MessageController
 * @Controller(prefix="laboratory/chat_module")
 */
class MessageController extends AbstractController
{
    /**
     * 获取服务监控
     * @RequestMapping(path="pull_message", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function pullMessage()
    {

    }
}
