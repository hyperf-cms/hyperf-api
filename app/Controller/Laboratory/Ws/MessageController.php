<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Pool\Redis;
use App\Task\Laboratory\GroupWsTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 消息控制器
 * Class MessageController
 * @package App\Controller\Laboratory\Ws
 * @Controller(prefix="message",server="ws")
 */
class MessageController extends AbstractController
{
    /**
     * 发送信息
     * @RequestMapping(path="forward_message",methods="POST")
     */
    public function forwardMessage()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];

        $content = json_encode($contactData['message']);
        $user = $contactData['user'];
        foreach ($contactData['contact'] as $item) {
            if ($item['is_group'] == 1) {
                $this->container->get(GroupWsTask::class)->forwardMessage($item, $user, $content);
            }else {
                var_dump($item);
            }
        }
    }

}

