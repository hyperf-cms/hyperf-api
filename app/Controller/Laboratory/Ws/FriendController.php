<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\GroupChatHistory;
use App\Pool\Redis;
use App\Service\Laboratory\MessageService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 好友聊天控制器
 * Class FriendController
 * @package App\Controller\Laboratory\Ws
 * @Controller(prefix="friend",server="ws")
 */
class FriendController extends AbstractController
{
    /**
     * 发送信息
     * @RequestMapping(path="send_message",methods="GET")
     */
    public function sendMessage()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];

        $contactId = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$contactData['toContactId']);
        $receptionState = empty($contactId) ? FriendChatHistory::RECEPTION_STATE_NO : FriendChatHistory::RECEPTION_STATE_YES;
        //添加聊天记录
        FriendChatHistory::addMessage($contactData, $receptionState);
        $contactData['status'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_STATUS_SUCCEED;
        $contactData['toContactId'] = $contactData['fromUser']['id'];

        unset($contactData['fromUser']['unread']);
        unset($contactData['fromUser']['lastSendTime']);
        unset($contactData['fromUser']['lastContent']);
        return [
            'message_data' => [
                'message' => [
                    'id' => $contactData['id'],
                    'status' => $contactData['status'],
                    'type' => $contactData['type'],
                    'sendTime' => $contactData['sendTime'],
                    'content' => $contactData['content'],
                    'toContactId' => $contactData['fromUser']['id'],
                    'fromUser' => $contactData['fromUser'],
                    'isGroup' => false,
                ],
                'event' => ''
            ],
            'fd' => $contactId
        ];
    }

    /**
     * 拉取信息
     * @RequestMapping(path="pull_message",methods="GET")
     */
    public function pullMessage()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];
        $userFd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $contactData['user_id']);

        $messageList = FriendChatHistory::query()
            ->where(function ($query) use ($contactData) {
                $query->where('from_uid', $contactData['user_id'])->where('to_uid', $contactData['contact_id']);
            })->orWhere(function ($query) use ($contactData) {
                $query->where('from_uid', $contactData['contact_id'])->where('to_uid', $contactData['user_id']);
            })->orderBy('id', 'desc')->limit(300)->get()->toArray();

        $messageList = array_reverse($messageList);

        //将消息置为已读
        FriendChatHistory::query()
            ->where('to_uid', $contactData['user_id'])
            ->where('from_uid', $contactData['contact_id'])
            ->update(['reception_state' => FriendChatHistory::RECEPTION_STATE_YES]);

        $list = [];
        foreach ($messageList as $key => $value) {
            $temp = [
                'id' => $value['message_id'],
                'status' => $value['status'],
                'type' => $value['type'],
                'content' => $value['content'],
                'sendTime' => intval($value['send_time']),
                'toContactId' => $value['to_uid'],
                'fileSize' => $value['file_size'],
                'fileName' => $value['file_name'],
                'fileExt' => $value['file_ext'],
                'isGroup' => false,
                'fromUser' => [
                    'id' => $value['from_uid'],
                    'avatar' => User::query()->where('id', $value['from_uid'])->value('avatar'),
                    'displayName' => User::query()->where('id', $value['from_uid'])->value('desc'),
                ],
            ];
            if ($temp['type'] == FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_FORWARD) $temp['content'] = MessageService::getInstance()->formatForwardMessage($temp['content'], $temp['fromUser']);
            $list[] = $temp;
        }
        return [
            'message_data' => [
                'friend_history_message' => $list,
                'event' => WsMessage::MESSAGE_TYPE_PULL_FRIEND_MESSAGE
            ],
            'fd' => $userFd,
        ];
    }

    /**
     * 撤回信息
     * @RequestMapping(path="withdraw_message",methods="GET")
     */
    public function withDrawMessage()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];
        $contactFd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$contactData['toContactId']);

        FriendChatHistory::query()
            ->where('message_id',  $contactData['id'])
            ->delete();
        return [
            'message_data' => [
                'message' => $contactData,
                'event' => WsMessage::MESSAGE_TYPE_FRIEND_WITHDRAW_MESSAGE
            ],
            'fd' => $contactFd,
        ];
    }
}

