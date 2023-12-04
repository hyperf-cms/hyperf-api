<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\FriendRelation;
use App\Model\Laboratory\GroupChatHistory;
use App\Pool\Redis;
use App\Service\Laboratory\Ws\FriendService;
use App\Service\Laboratory\Ws\MessageService;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Sender;

/**
 * 好友消息传递异步任务
 * Class GroupWsTask
 * @package App\Task
 * @Author YiYuan-Lin
 * @Date: 2021/3/23
 */
class FriendWsTask
{
    #[Inject]
    private Sender $sender;

    /**
     * 通知用户上线下线
     * @param array $userInfo
     * @param string $event
     * @param bool $isReconnection
     * @return bool
     */
    public function friendOnlineAndOfflineNotify(array $userInfo, string $event, bool $isReconnection = false)
    {
        if (empty($userInfo)) return false;
        //获取在线用户
        $fdList = FriendService::getInstance()->getOnlineFriendList($userInfo, true);

        //组装消息
        $message['id'] = generate_rand_id();
        $message['status'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_EVENT;
        $message['uid'] = $userInfo['id'];
        $message['sendTime'] = time() * 1000;
        $message['event'] = $event;
        $message['user_info'] = $userInfo;
        $message['online_status'] = $event == WsMessage::FRIEND_ONLINE_MESSAGE ? FriendRelation::FRIEND_ONLINE_STATUS: FriendRelation::FRIEND_ONLINE_STATUS_NO;
        $message['is_reconnection'] = $isReconnection;

        foreach ($fdList as $key => $value) {
            $sendMessage = [
                'message' => $message,
                'event' => $event
            ];
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }

    /**
     * 合并转发信息
     * @param array $userInfo
     * @param array $user
     * @param string $content
     * @return bool
     */
    function mergeForwardMessage(array $userInfo, array $user, string $content)
    {
        //添加聊天记录
        $message = [];
        $message['id'] = generate_rand_id();
        $message['from_uid'] = $user['id'];
        $message['to_uid'] = $userInfo['id'];
        $message['type'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_FORWARD;
        $message['status'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['sendTime'] = time() * 1000;
        $message['content'] = $content;
        $message['toContactId'] = $userInfo['id'];
        $message['fromUser'] = $user;
        $message['isGroup'] = false;
        $contactId = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$userInfo['id']);
        $fromUserFd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$user['id']);
        $receptionState = empty($contactId) ? FriendChatHistory::RECEPTION_STATE_NO : FriendChatHistory::RECEPTION_STATE_YES;

        //添加消息记录
        FriendChatHistory::addMessage($message, $receptionState);
        $message['content'] = MessageService::getInstance()->formatForwardMessage($message['content'], $message['fromUser']);

        $sendMessage = [
            'message' => $message,
        ];
        $this->sender->push((int) $contactId, json_encode($sendMessage));
        $this->sender->push((int) $fromUserFd, json_encode($sendMessage));
        return true;
    }

    /**
     * 逐条转发信息
     * @param array $userInfo
     * @param array $user
     * @param array $content
     * @return bool
     */
    function forwardMessage(array $userInfo, array $user, array $content)
    {
        if (is_array($content)) {
            foreach ($content as $item) {
                $messageSource = $item['is_group'] ? GroupChatHistory::query()->where('message_id', $item['id'])->first() : FriendChatHistory::query()->where('message_id', $item['id'])->first();
                $messageSource = objToArray($messageSource);
                if (empty($messageSource)) continue;
                //添加聊天记录
                $message = [];
                $message['id'] = generate_rand_id();
                $message['from_uid'] = $user['id'];
                $message['to_uid'] = $userInfo['id'];
                $message['type'] = $messageSource['type'];
                $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
                $message['sendTime'] = time() * 1000;
                $message['content'] = $messageSource['content'];
                $message['toContactId'] = $userInfo['id'];
                $message['fromUser'] = $user;
                $message['fileSize'] = $messageSource['file_size'];
                $message['fileName'] = $messageSource['file_name'];
                $message['fileExt'] = $messageSource['file_ext'];
                $message['isGroup'] = false;
                $contactId = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$userInfo['id']);
                $fromUserFd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string)$user['id']);
                $receptionState = empty($contactId) ? FriendChatHistory::RECEPTION_STATE_NO : FriendChatHistory::RECEPTION_STATE_YES;

                //添加消息记录
                FriendChatHistory::addMessage($message, $receptionState);
                if ($message['type'] == FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_FORWARD) $message['content'] = MessageService::getInstance()->formatForwardMessage($message['content'], $message['fromUser']);

                $sendMessage = [
                    'message' => $message,
                ];
                $this->sender->push((int) $contactId, json_encode($sendMessage));
                $this->sender->push((int) $fromUserFd, json_encode($sendMessage));
            }
            return true;
        }
    }

    /**
     * 维护好友关系
     * @param Model $model
     * @return bool
     */
    public function maintainFriendRelation(Model $model)
    {
        $userList = User::query()->where('id', '!=', $model['id'])->get()->pluck('id');

        foreach ($userList as $user_id) {
            FriendRelation::insert([
                'uid' => $user_id,
                'friend_id' => $model['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        //组装联系人信息
        $contact['id'] = $model['id'];
        $contact['displayName'] = $model['desc'];
        $contact['avatar'] = $model['avatar'];
        $contact['index'] = $model['desc'];
        $contact['unread'] = 0;
        $contact['lastSendTime'] = 0;
        $contact['lastContent'] = '';
        $contact['is_group'] = 0;
        $contact['status'] = FriendRelation::FRIEND_ONLINE_STATUS_NO;

        //获取在线用户
        $fdList = FriendService::getInstance()->getOnlineFriendList($model->toArray());
        //组装消息
        $message['id'] = generate_rand_id();
        $message['status'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_EVENT;
        $message['sendTime'] = time() * 1000;
        $message['event'] = WsMessage::MESSAGE_TYPE_NEW_FRIEND_JOIN;
        $message['contact'] = $contact;

        foreach ($fdList as $key => $value) {
            $sendMessage = [
                'message' => $message,
                'event' => WsMessage::MESSAGE_TYPE_NEW_FRIEND_JOIN
            ];
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }

    /**
     * 删除好友关系
     * @param Model $model
     * @return bool
     */
    public function deleteContactEvent(Model $model)
    {
        $userList = User::query()->where('id', '!=', $model['id'])->get()->pluck('id');

        //维护好友关系
        FriendRelation::query()->where('uid', $model['id'])->orWhere('friend_id', $model['id'])->delete();
        //获取在线用户
        $fdList = FriendService::getInstance()->getOnlineFriendList($model->toArray());
        //组装消息
        $message['id'] = generate_rand_id();
        $message['status'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_EVENT;
        $message['sendTime'] = time() * 1000;
        $message['event'] = WsMessage::MESSAGE_TYPE_FRIEND_DELETE;
        $message['contact_id'] = $model['id'];

        foreach ($fdList as $key => $value) {
            $sendMessage = [
                'message' => $message,
                'event' => WsMessage::MESSAGE_TYPE_FRIEND_DELETE
            ];
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }
}
