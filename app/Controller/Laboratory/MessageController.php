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
       $id = $this->request->query('id');
       $userInfo = conGet('user_info');
       $messageList = FriendChatHistory::query()
           ->where(function ($query) use ($userInfo, $id) {
               $query->where('from_uid', $userInfo['id'])->where('to_uid', $id);
           })->orWhere(function ($query) use ($userInfo, $id) {
               $query->where('from_uid', $id)->where('to_uid', $userInfo['id']);
           })->get()->toArray();

        $list = [];
        foreach ($messageList as $key => $value) {
            $list[] = [
                'id' => $value['message_id'],
                'status' => $value['status'],
                'type' => $value['type'],
                'sendTime' => intval($value['send_time']),
                'content' => $value['content'],
                'toContactId' => $value['to_uid'],
                'fromUser' => [
                    'id' => $value['from_uid'],
                    'avatar' => User::query()->where('id', $value['from_uid'])->value('avatar'),
                    'displayName' => User::query()->where('id', $value['from_uid'])->value('desc'),
                ],
            ];
        }

       return $this->success([
           'list' => $list
       ]);
    }
}
