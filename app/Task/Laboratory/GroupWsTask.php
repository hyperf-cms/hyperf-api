<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Constants\Laboratory\GroupEvent;
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
        $message['content'] = '';

        foreach ($uidFdList as $key => $value) {
            $sendMessage['type'] = $event;
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }
}
