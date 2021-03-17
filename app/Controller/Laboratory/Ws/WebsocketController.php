<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Ws;

use App\Controller\AbstractController;
use App\Pool\Redis;

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
            $dispatcher = $this->container
                ->get(DispatcherFactory::class)
                ->getDispatcher('ws');
            $dispatched = make(Dispatched::class, [
                $dispatcher->dispatch('GET', $targetUri)
            ]);
            if ($dispatched->isFound()) {
                //路由处理
                $result = call_user_func([
                    make($dispatched->handler->callback[0]),
                    $dispatched->handler->callback[1],
                ]);
                if ($result !== NULL) {
                    if (!empty($result['fd'])) $server->push((int) $result['fd'], json_encode($result['message']));
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
        //获取聊天初始化信息
        $initInfo = InitService::getInstance()->initialization();
        //将在线用户放置Redis中
        Redis::getInstance()->hSet(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $initInfo['user_info']['id'], (string) $request->fd);

        //连接信息发送
        $server->push($request->fd, MessageParser::encode($initInfo));
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
        Redis::getInstance()->hDel(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $userInfo['id']);
    }
}