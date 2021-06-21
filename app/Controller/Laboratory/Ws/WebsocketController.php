<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use App\Pool\Redis;

use App\Task\Laboratory\FriendWsTask;
use App\Task\Laboratory\GroupWsTask;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Sender;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;
use App\Constants\Laboratory\ChatRedisKey;
use App\Foundation\Facades\MessageParser;
use App\Service\Laboratory\InitService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * 聊天
 * Class WebsocketController
 * @package App\Controller\Laboratory\Ws
 * @Author YiYuan-Lin
 * @Date: 2021/4/25
 */
class WebsocketController extends AbstractController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject()
     * @var Sender
     */
    private $sender;

    /**
     * 用户发送信息
     * @param \Swoole\Http\Response|WebSocketServer $server
     * @param Frame $frame
     */
    public function onMessage($server, Frame $frame): void
    {
            $message = json_decode($frame->data, true);

            conSet('chat_message', MessageParser::encode([
                'message' => $message['message'],
                'file' => $message['file'] ?? '',
            ]));
            $targetUri = $message['uri'] ?? '';
            $requestMethod = $message['method'] ?? 'GET';
            $dispatcher = $this->container
                ->get(DispatcherFactory::class)
                ->getDispatcher('ws');
            $dispatched = make(Dispatched::class, [
                $dispatcher->dispatch($requestMethod, $targetUri)
            ]);
            if ($dispatched->isFound()) {
                //路由处理
                $result = call_user_func([
                    make($dispatched->handler->callback[0]),
                    $dispatched->handler->callback[1],
                ]);
                if ($result !== NULL) {
                    if (!empty($result['fd'])){
                        if (is_array($result['fd'])) {
                            foreach ($result['fd'] as $fd) {
                                $server->push((int) $fd, json_encode($result['message_data']));
                            }
                        }else {
                            $server->push((int) $result['fd'], json_encode($result['message_data']));
                        }
                    }
                }
            }
    }

    /**
     * 用户连接服务器
     * @param \Swoole\Http\Response|WebSocketServer $server
     * @param Request $request
     */
    public function onOpen($server, Request $request): void
    {
        //是否重连，如果是断线重连择不通知好友新用户上线提示
        $isReconnection = conGet('is_reconnection') ?? false;
        //获取聊天初始化信息
        $initInfo = InitService::getInstance()->initialization();
        //获取用户信息
        $userInfo = conGet('user_info');
        //将在线用户放置Redis中
        Redis::getInstance()->hSet(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $initInfo['user_info']['id'], (string) $request->fd);
        //将FD对应在线用户ID放置Redis中
        Redis::getInstance()->hSet(ChatRedisKey::ONLINE_FD_USER_KEY, (string) $request->fd, (string) $initInfo['user_info']['id']);

        //连接信息发送
        $server->push($request->fd, MessageParser::encode($initInfo));
        //通知好友该用户登陆状态
        $this->container->get(FriendWsTask::class)->friendOnlineAndOfflineNotify($userInfo, WsMessage::FRIEND_ONLINE_MESSAGE, $isReconnection);
    }

    /**
     * 用户关闭连接
     * @param \Swoole\Http\Response|WebSocketServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, int $fd, int $reactorId): void
    {
        $uid = Redis::getInstance()->hGet(ChatRedisKey::ONLINE_FD_USER_KEY, (string) $fd);
        $userInfo = User::findById($uid)->toArray();

        //删除在线列表中的用户
        Redis::getInstance()->hDel(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $userInfo['id']);
        Redis::getInstance()->hDel(ChatRedisKey::ONLINE_FD_USER_KEY, (string) $fd);
        //通知好友该用户登陆状态
        $this->container->get(FriendWsTask::class)->friendOnlineAndOfflineNotify($userInfo, WsMessage::FRIEND_OFFLINE_MESSAGE);
    }
}