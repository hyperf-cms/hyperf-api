<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Model\Laboratory\GroupChatHistory;
use App\Service\Laboratory\GroupService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Task\Annotation\Task;

/**
 * 组消息传递异步任务
 * Class GroupWsTask
 * @package App\Task
 * @Author YiYuan-Lin
 * @Date: 2021/3/23
 */
class GroupWsTask
{
    /**
     * @Inject()
     * @var \Hyperf\WebSocketServer\Sender
     */
    private $sender;

    /**
     * @Task()
     * 组事件推送
     * @param string $event
     * @param array $groupInfo
     * @return bool
     */
    public function pushEvent(string $event, array $groupInfo)
    {
        if (empty($event || empty($groupInfo))) return false;

        $uidFdList = GroupService::getInstance()->getOnlineGroupMemberFd($groupInfo['group_id']);
        $message = [];
        $message['status'] = 'succeed';
        $message['type'] = 'event';
        $message['sendTime'] = time() * 1000;
        $message['groupId'] = $groupInfo['group_id'];
        $message['avatar'] = $groupInfo['avatar'];
        $message['groupName'] = $groupInfo['group_name'] ?? '群聊__' . $groupInfo['group_id'];
        $message['index'] = "[0]群聊";
        $message['content'] = '';

        foreach ($uidFdList as $key => $value) {
            $sendMessage['type'] = $event;
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }

    /**
     * 组消息发送
     * @param string $groupId
     * @param array $message
     * @param string $event
     * @return bool
     */
    public function sendMessage(string $groupId, array $message, $event = '')
    {
        if (empty($groupId || empty($message))) return false;
        $uidFdList = GroupService::getInstance()->getOnlineGroupMemberFd($groupId);

        foreach ($uidFdList as $key => $value) {
            $sendMessage['type'] = $event;
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        //添加聊天记录
        GroupChatHistory::addMessage($message, 1);
        return true;
    }
}
