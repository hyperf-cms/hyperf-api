<?php

declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Controller\AbstractController;
use App\Model\Auth\User;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 聊天模块
 * Class ChatController
 * @Controller(prefix="laboratory/chat_module")
 */
class ChatController extends AbstractController
{
    /**
     * 初始化当前用户信息
     * @RequestMapping(path="init_user_info", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function initUserInfo()
    {
        $returnUserInfo = [];
        $userInfo = conGet('user_info');

        $returnUserInfo['id'] = $userInfo['id'];
        $returnUserInfo['displayName'] = $userInfo['desc'];
        $returnUserInfo['avatar'] = $userInfo['avatar'];
        //未读消息数
        $returnUserInfo['unread'] = 10;
        //最近一条消息的时间戳，13位毫秒
        $returnUserInfo['lastSendTime'] = 1614928060000;
        //最近一条消息的内容
        $returnUserInfo['lastContent'] = 'Hello World';

        return $this->success([
            'user' => $returnUserInfo
        ]);
    }

    /**
     * 初始化当前用户联系人
     * @RequestMapping(path="init_contact_person", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function initContactPerson()
    {
        $userInfo = conGet('user_info');
        $userList = User::query()->where('id', '!=', $userInfo['id'])->get()->toArray();
        $list = [];

        foreach ($userList as $key => $val) {
            $list[] = [
                'id' => $val['id'],
                'displayName' => $val['desc'],
                'avatar' => $val['avatar'],
                'index' => $val['desc'],
                'unread' => 0,
                'lastContent' => '',
                'lastSendTime' => '1566047865417',
            ];
        }

        return $this->success([
            'list' => $list
        ]);
    }
}
