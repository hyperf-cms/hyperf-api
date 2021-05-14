<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Constants\Laboratory\GroupEvent;
use App\Model\Auth\User;
use App\Model\Laboratory\Group;
use App\Model\Laboratory\GroupChatHistory;
use App\Model\Laboratory\GroupRelation;
use App\Service\Laboratory\GroupService;
use Hyperf\DbConnection\Db;
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
     * 创建组事件
     * @param array $groupInfo
     * @return bool
     */
    public function createGroupEvent(array $groupInfo)
    {
        if (empty($groupInfo)) return false;

        $uidFdList = GroupService::getInstance()->getOnlineGroupMemberFd($groupInfo['group_id']);
        $message = [];
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['sendTime'] = time() * 1000;

        $groupInfoTemp = [];
        $groupInfoTemp['id'] = $groupInfo['group_id'];
        $groupInfoTemp['displayName'] = $groupInfo['group_name'];
        $groupInfoTemp['avatar'] = $groupInfo['avatar'];
        $groupInfoTemp['size'] = $groupInfo['size'];
        $groupInfoTemp['content'] = '';
        $groupInfoTemp['index'] = "[0]群聊";
        $groupInfoTemp['introduction'] = $groupInfo['introduction'];
        $groupInfoTemp['is_group'] = Group::IS_GROUP_TYPE;
        $groupInfoTemp['member_total'] = 0;

        //获取组成员信息
        $groupMembersUidList = GroupRelation::query()->where('group_id', $groupInfo['group_id'])->orderBy('level', 'asc')->pluck('uid')->toArray();
        if (!empty($groupMembersUidList)) {
            $groupMembersList = User::query()->select('a.id', 'a.desc', 'a.avatar', 'b.level')
                ->from('users as a')
                ->whereIn('a.id', $groupMembersUidList)
                ->leftJoin('ct_group_relation as b', 'a.id', 'b.uid')
                ->where('b.group_id', $groupInfo['group_id'])
                ->orderBy(Db::raw('FIND_IN_SET(a.id, "' . implode(",", $groupMembersUidList) . '"' . ")"))
                ->get()->toArray();
            $groupInfoTemp['group_member'] = $groupMembersList;
            $groupInfoTemp['member_total'] = count($groupMembersList);
        }
        $message['group_info'] = $groupInfoTemp;

        foreach ($uidFdList as $key => $value) {
            $sendMessage['type'] = GroupEvent::CREATE_GROUP_EVENT;
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
