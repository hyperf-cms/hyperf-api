<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Ws;

use App\Pool\Redis;
use Swoole\Http\Request;
use App\Model\Auth\User;
use Swoole\Websocket\Frame;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\WebSocket\Server as WebSocketServer;

class ChatController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * 用户连接服务器
     * @param \Swoole\Http\Response|WebSocketServer $server
     * @param Request $request
     */
    public function onOpen($server, Request $request): void
    {
        $returnUserInfo = [];
        $userInfo = conGet('user_info');

        //修改用户ID为WS分配的唯一ID
        $returnUserInfo['id'] = $userInfo['id'];
        $returnUserInfo['user_id'] = $userInfo['id'];
        $returnUserInfo['displayName'] = $userInfo['desc'];
        $returnUserInfo['avatar'] = $userInfo['avatar'];
        $returnUserInfo['unread'] = 10;
        $returnUserInfo['lastSendTime'] = 1614928060000;
        $returnUserInfo['lastContent'] = 'Hello World';

        //获取用户联系人
        $userList = User::query()->where('id', '!=', $userInfo['id'])->get()->toArray();
        $userContactList = [];
        foreach ($userList as $key => $val) {
            $userContactList[] = [
                'id' => $val['id'],
                'user_id' => $val['id'],
                'displayName' => $val['desc'],
                'avatar' => $val['avatar'],
                'index' => $val['desc'],
                'unread' => 0,
                'lastContent' => '',
                'lastSendTime' => '1566047865417',
            ];
        }
        //将在线用户放置Redis中
        Redis::getInstance()->hSet('online_user', (string) $returnUserInfo['user_id'], (string) $request->fd);

        //连接信息发送
        $server->push($request->fd, json_encode([
            'type' => 'init',
            'user_info' => $returnUserInfo,
            'user_contact' => $userContactList
        ]));
    }

    /**
     * 用户发送信息
     * @param \Swoole\Http\Response|WebSocketServer $server
     * @param Frame $frame
     */
    public function onMessage($server, Frame $frame): void
    {
        $contactData = json_decode($frame->data, true);
        $contactId = Redis::getInstance()->hget('online_user', (string) $contactData['toContactId']);
        $contactData['status'] = 'succeed';
        $contactData['fromUser']['id'] = $contactData['fromUser']['user_id'];
        $contactData['toContactId'] = $contactData['fromUser']['user_id'];

        unset($contactData['fromUser']['unread']);
        unset($contactData['fromUser']['lastSendTime']);
        unset($contactData['fromUser']['lastContent']);
        unset($contactData['fromUser']['user_id']);

        Redis::getInstance()->hset('CHAT_MESSAGE_LIST_BY_' . $contactData['toContactId'], $contactData['id'], $frame->data);
        $server->push((int) $contactId, json_encode($contactData));
    }

    /**
     * 用户关闭连接
     * @param \Swoole\Http\Response|\Swoole\Server $server
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, int $fd, int $reactorId): void
    {
        $userInfo = conGet('user_info');

        //删除在线列表中的用户
        Redis::getInstance()->hDel('online_user', (string) $userInfo['id']);
    }
}