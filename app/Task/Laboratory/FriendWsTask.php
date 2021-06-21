<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Constants\Laboratory\GroupEvent;
use App\Constants\Laboratory\WsMessage;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\FriendRelation;
use App\Model\Laboratory\GroupRelation;
use App\Service\Laboratory\FriendService;
use Hyperf\Di\Annotation\Inject;

/**
 * 好友消息传递异步任务
 * Class GroupWsTask
 * @package App\Task
 * @Author YiYuan-Lin
 * @Date: 2021/3/23
 */
class FriendWsTask
{
    /**
     * @Inject()
     * @var \Hyperf\WebSocketServer\Sender
     */
    private $sender;

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
}
