<?php
declare(strict_types=1);

namespace App\Controller\Laboratory;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class SendMessageController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage($server, Frame $frame): void
    {
        var_dump($frame->data);
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen($server, Request $request): void
    {
        $server->push($request->fd, 'Opened' . $request->fd);
    }
}