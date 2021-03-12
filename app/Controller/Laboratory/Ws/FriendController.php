<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Laboratory\FriendChatHistory;
use App\Pool\Redis;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class FriendController
 * @package App\Controller\Laboratory\Ws
 * @Controller(prefix="friend",server="ws")
 */
class FriendController extends AbstractController
{
    /**
     * @RequestMapping(path="send_message",methods="GET")
     */
    public function sendMessage()
    {
            $chatMessage = MessageParser::decode(conGet('chat_message'));
            $contactData = $chatMessage['message'];
            $contactId = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $contactData['toContactId']);

            $receptionState = empty($contactId) ? 0 : 1;
            //添加聊天记录
            FriendChatHistory::addMessage($contactData, $receptionState);

            $contactData['status'] = 'succeed';
            $contactData['toContactId'] = $contactData['fromUser']['id'];

            unset($contactData['fromUser']['unread']);
            unset($contactData['fromUser']['lastSendTime']);
            unset($contactData['fromUser']['lastContent']);

            return [
                'message' => [
                    'id' => $contactData['id'],
                    'status' => 'succeed',
                    'type' => $contactData['type'],
                    'sendTime' => $contactData['sendTime'],
                    'content' => $contactData['content'],
                    'toContactId' => $contactData['fromUser']['id'],
                    'fromUser' => $contactData['fromUser'],
                ],
                'fd' => $contactId
            ];
    }

    /**
     * @RequestMapping(path="unread_message",methods="GET")
     */
    public function getUnreadMessage()
    {

    }

    /**
     * @RequestMapping(path="read",methods="GET")
     */
    public function read()
    {

    }
}

