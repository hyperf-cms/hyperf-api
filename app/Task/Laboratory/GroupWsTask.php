<?php
declare(strict_types = 1);

namespace App\Task\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\GroupEvent;
use App\Foundation\Utils\GroupAvatar;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\Group;
use App\Model\Laboratory\GroupChatHistory;
use App\Model\Laboratory\GroupRelation;
use App\Pool\Redis;
use App\Service\Laboratory\GroupService;
use App\Service\Laboratory\MessageService;
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
        $groupInfoTemp['validation'] = $groupInfo['validation'];
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
            $sendMessage['event'] = GroupEvent::CREATE_GROUP_EVENT;
            $message['group_info']['level'] = GroupRelation::getLevelById($value['uid'], $groupInfo['group_id']);
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }

    /**
     * @Task()
     * 邀请用户进群事件
     * @param array $groupInfo
     * @param array $contactIdList
     * @return bool
     */
    public function groupMemberJoinEvent(array $groupInfo, array $contactIdList)
    {
        if (empty($groupInfo)) return false;

        //根据组ID获取该群所有在线用户
        $uidFdList = GroupService::getInstance()->getOnlineGroupMemberFd($groupInfo['group_id']);
        $message = [];
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['sendTime'] = time() * 1000;
        $message['newJoinGroupMember'] = $contactIdList;

        $groupInfoTemp = [];
        $groupInfoTemp['id'] = $groupInfo['group_id'];
        $groupInfoTemp['displayName'] = $groupInfo['group_name'];
        $groupInfoTemp['avatar'] = $groupInfo['avatar'];
        $groupInfoTemp['size'] = $groupInfo['size'];
        $groupInfoTemp['content'] = '';
        $groupInfoTemp['index'] = "[0]群聊";
        $groupInfoTemp['introduction'] = $groupInfo['introduction'];
        $groupInfoTemp['validation'] = $groupInfo['validation'];
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
            $sendMessage['event'] = GroupEvent::NEW_MEMBER_JOIN_GROUP_EVENT;
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }

    /**
     * @Task()
     * 组员退群事件
     * @param array $groupInfo
     * @param array $userInfo
     * @param string $event
     * @return bool
     */
    public function groupMemberExitEvent(array $groupInfo, array $userInfo, string $event)
    {
        if (empty($groupInfo)) return false;
        if (empty($userInfo)) return false;
        $message = [];
        $content = $event == GroupEvent::GROUP_MEMBER_EXIT_EVENT ? $userInfo['desc'] . ' 已退出群聊' : $userInfo['desc'] . ' 被踢出群聊';
        $message['id'] = generate_rand_id();
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['uid'] = $userInfo['id'];
        $message['sendTime'] = time() * 1000;
        $message['toContactId'] = $groupInfo['group_id'];
        $message['content'] = $content ?? '';
        $message['displayName'] = $groupInfo['group_name'] ?? '';
        $message['group_member'] = [];
        $message['member_total'] = [];

        $groupMembersUidList = GroupRelation::query()
            ->where('group_id', $groupInfo['group_id'])
            ->where('uid', '!=', $userInfo['id'])
            ->orderBy('level', 'asc')->pluck('uid')
            ->toArray();
        //判断组成员是否为空，获取组成员信息
        if (!empty($groupMembersUidList)) {
            $groupMembersList = User::query()->select('a.id', 'a.desc', 'a.avatar', 'b.level')
                ->from('users as a')
                ->whereIn('a.id', $groupMembersUidList)
                ->leftJoin('ct_group_relation as b', 'a.id', 'b.uid')
                ->where('b.group_id', $groupInfo['group_id'])
                ->orderBy(Db::raw('FIND_IN_SET(a.id, "' . implode(",", $groupMembersUidList) . '"' . ")"))
                ->get()->toArray();
            $message['group_member'] = $groupMembersList;
            $message['member_total'] = count($groupMembersList);
        }
        //根据组ID获取该群所有在线用户
        $this->sendMessage($groupInfo['group_id'], $message, $event);
        //删除组跟用户板绑定关系
        GroupRelation::query()->where('group_id', $groupInfo['group_id'])->where('uid', $userInfo['id'])->delete();
        return true;
    }

    /**
     * @Task()
     * 更改组员等级事件
     * @param array $groupInfo
     * @param array $userInfo
     * @param int $changeLevel
     * @return bool
     */
    public function changeGroupMemberLevel(array $groupInfo, array $userInfo, int $changeLevel)
    {
        if (empty($groupInfo)) return false;
        if (empty($userInfo)) return false;
        if (empty($changeLevel)) return false;
        //更改数据表
        GroupRelation::query()->where('group_id', $groupInfo['group_id'])->where('uid', $userInfo['id'])->update(['level' => $changeLevel]);
        $message = [];
        $content = $changeLevel == GroupRelation::GROUP_MEMBER_LEVEL_MANAGER ? $userInfo['desc'] . ' 被设为管理员' : $userInfo['desc'] . ' 被撤掉管理员';
        $message['id'] = generate_rand_id();
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['uid'] = $userInfo['id'];
        $message['sendTime'] = time() * 1000;
        $message['toContactId'] = $groupInfo['group_id'];
        $message['content'] = $content ?? '';
        $message['displayName'] = $groupInfo['group_name'] ?? '';
        $message['level'] = GroupRelation::getLevelById($userInfo['id'], $groupInfo['group_id']);
        $message['group_member'] = [];
        $message['member_total'] = [];

        $groupMembersUidList = GroupRelation::query()
            ->where('group_id', $groupInfo['group_id'])
            ->orderBy('level', 'asc')->pluck('uid')
            ->toArray();
        //判断组成员是否为空，获取组成员信息
        if (!empty($groupMembersUidList)) {
            $groupMembersList = User::query()->select('a.id', 'a.desc', 'a.avatar', 'b.level')
                ->from('users as a')
                ->whereIn('a.id', $groupMembersUidList)
                ->leftJoin('ct_group_relation as b', 'a.id', 'b.uid')
                ->where('b.group_id', $groupInfo['group_id'])
                ->orderBy(Db::raw('FIND_IN_SET(a.id, "' . implode(",", $groupMembersUidList) . '"' . ")"))
                ->get()->toArray();
            $message['group_member'] = $groupMembersList;
            $message['member_total'] = count($groupMembersList);
        }
        //根据组ID获取该群所有在线用户
        $this->sendMessage($groupInfo['group_id'], $message, GroupEvent::CHANGE_GROUP_MEMBER_LEVEL_EVENT);
        return true;
    }

    /**
     * 解散群聊
     * @param array $groupInfo
     * @param array $userInfo
     * @return bool
     */
    public function deleteGroup(array $groupInfo, array $userInfo)
    {

        if (empty($groupInfo)) return false;
        if (empty($userInfo)) return false;
        $message = [];
        $content = $userInfo['desc'] . ' 已经解散了 "' . $groupInfo['group_name'] . '" 该群聊';
        $message['id'] = generate_rand_id();
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['uid'] = $userInfo['id'];
        $message['sendTime'] = time() * 1000;
        $message['toContactId'] = $groupInfo['group_id'];
        $message['content'] = $content ?? '';
        $message['displayName'] = $groupInfo['group_name'] ?? '';

        Group::query()->where('group_id', $groupInfo['group_id'])->delete();
        GroupRelation::query()->where('group_id', $groupInfo['group_id'])->delete();
        GroupChatHistory::query()->where('to_group_id', $groupInfo['group_id'])->delete();

        //通知所有群用户
        $this->sendMessage($groupInfo['group_id'], $message, GroupEvent::DELETE_GROUP_EVENT);
        return true;
    }

    /**
     * 更新群聊头像
     * @param array $groupInfo
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     */
    public function changeGroupAvatar(array $groupInfo)
    {
        if (empty($groupInfo)) return false;
        //如果不是默认头像则不替换
        if (!strstr($groupInfo['avatar'], 'composite_avatar')) return false;
        $message = [];
        $message['id'] = generate_rand_id();
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT;
        $message['sendTime'] = time() * 1000;
        $message['toContactId'] = $groupInfo['group_id'];

        $uidList = GroupRelation::query()->where('group_id', $groupInfo['group_id'])->orderBy('created_at', 'desc')->limit(9)->pluck('uid')->toArray();
        $picList = User::query()->whereIn('id', $uidList)->pluck('avatar')->toArray();
        GroupAvatar::init($picList, false, 'chat/group/composite_avatar');
        $message['avatar'] = GroupAvatar::build();
        Group::query()->where('group_id', $groupInfo['group_id'])->update(['avatar' => $message['avatar']]);

        $this->sendMessage($groupInfo['group_id'], $message, GroupEvent::CHANGE_GROUP_AVATAR);
        return true;
    }

    /**
     * 合并转发信息
     * @param array $groupInfo
     * @param array $user
     * @param string $content
     * @return bool
     */
    function mergeForwardMessage(array $groupInfo, array $user, string $content)
    {
        //添加聊天记录
        $message = [];
        $message['id'] = generate_rand_id();
        $message['from_uid'] = $user['id'];
        $message['to_group_id'] = $groupInfo['group_id'];
        $message['type'] = GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_FORWARD;
        $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
        $message['sendTime'] = time() * 1000;
        $message['content'] = $content;
        $message['toContactId'] = $groupInfo['group_id'];
        $message['fromUser'] = $user;

        //获取不在线用户，并添加到未读历史消息中
        $unOnlineUidList = GroupService::getInstance()->getUnOnlineGroupMember($groupInfo['group_id']);
        foreach ($unOnlineUidList as $uid) {
            Redis::getInstance()->sAdd(ChatRedisKey::GROUP_CHAT_UNREAD_MESSAGE_BY_USER . $uid, $groupInfo['group_id']);
        }
        $this->sendMessage($groupInfo['group_id'], $message, GroupEvent::FORWARD_MESSAGE);
        return true;
    }

    /**
     * 逐条转发信息
     * @param array $groupInfo
     * @param array $user
     * @param array $content
     * @return bool
     */
    function forwardMessage(array $groupInfo, array $user, array $content)
    {
        if (is_array($content)) {
            foreach ($content as $item) {
                $messageSource = $item['is_group'] == true ? GroupChatHistory::query()->where('message_id', $item['id'])->first() : FriendChatHistory::query()->where('message_id', $item['id'])->first();
                $messageSource = objToArray($messageSource);
                if (empty($messageSource)) continue;
                //添加聊天记录
                $message = [];
                $message['id'] = generate_rand_id();
                $message['from_uid'] = $user['id'];
                $message['to_group_id'] = $groupInfo['group_id'];
                $message['type'] = $messageSource['type'];
                $message['status'] = GroupChatHistory::GROUP_CHAT_MESSAGE_STATUS_SUCCEED;
                $message['sendTime'] = time() * 1000;
                $message['content'] = $messageSource['content'];
                $message['toContactId'] = $groupInfo['group_id'];
                $message['fromUser'] = $user;
                $message['fileSize'] = $messageSource['file_size'];
                $message['fileName'] = $messageSource['file_name'];
                $message['fileExt'] = $messageSource['file_ext'];

                //获取不在线用户，并添加到未读历史消息中
                $unOnlineUidList = GroupService::getInstance()->getUnOnlineGroupMember($groupInfo['group_id']);
                foreach ($unOnlineUidList as $uid) {
                    Redis::getInstance()->sAdd(ChatRedisKey::GROUP_CHAT_UNREAD_MESSAGE_BY_USER . $uid, $groupInfo['group_id']);
                }
                $this->sendMessage($groupInfo['group_id'], $message, GroupEvent::FORWARD_MESSAGE);
            }
            return true;
        }
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
        if (empty($message['fromUser'])) {
            $message['fromUser']['id'] = 0;
            $message['fromUser']['displayName'] = '系统通知';
        }
        $message['isGroup'] = true;
        //添加聊天记录
        GroupChatHistory::addMessage($message, 1);
        $uidFdList = GroupService::getInstance()->getOnlineGroupMemberFd($groupId);
        if ($message['type'] == GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_FORWARD) $message['content'] = MessageService::getInstance()->formatForwardMessage($message['content'], $message['fromUser']);
        foreach ($uidFdList as $key => $value) {
            $sendMessage['event'] = $event;
            $sendMessage['message'] = $message;
            $this->sender->push((int) $value['fd'], json_encode($sendMessage));
        }
        return true;
    }
}
