<?php
namespace App\Service\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Foundation\Traits\Singleton;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\FriendRelation;
use App\Model\Laboratory\Group;
use App\Model\Laboratory\GroupChatHistory;
use App\Model\Laboratory\GroupRelation;
use App\Pool\Redis;
use App\Service\BaseService;

/**
 * 聊天初始化服务类
 * Class MessageService
 * @package App\Service\Setting
 * @Author YiYuan-Lin
 * @Date: 2021/3/12
 */
class InitService extends BaseService
{
    use Singleton;

    /**
     * 获取初始化聊天信息
     * @return array
     */
    public function initialization() : array
    {
        $returnUserInfo = [];
        $userInfo = conGet('user_info');

        $returnUserInfo['id'] = $userInfo['id'];
        $returnUserInfo['displayName'] = $userInfo['desc'];
        $returnUserInfo['avatar'] = $userInfo['avatar'];

        //获取用户联系人
        $userList = User::query()->where('id', '!=', $userInfo['id'])->get()->toArray();
        $userContactList = [];
        foreach ($userList as $key => $val) {
            $fd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $val['id']);
            $unreadMessageInfo = $this->getUnReadMessageByUser($val, $userInfo);
            $userContactList[] = [
                'id' => $val['id'],
                'displayName' => $val['desc'],
                'avatar' => $val['avatar'],
                'index' => $val['desc'],
                'unread' => $unreadMessageInfo['unread'] ?? 0,
                'status' => empty($fd) ? FriendRelation::FRIEND_ONLINE_STATUS_NO : FriendRelation::FRIEND_ONLINE_STATUS,
                'lastContent' => $unreadMessageInfo['lastContent'] ?? '',
                'lastContentType' => $unreadMessageInfo['lastContentType'] ?? '',
                'lastSendTime' => $unreadMessageInfo['lastSendTime'] ?? getMillisecond(),
            ];
        }
        //获取用户组
        $userHasGroupId = GroupRelation::query()->where('uid', $userInfo['id'])->pluck('group_id');
        $groupList = Group::query()->whereIn('group_id', $userHasGroupId)->get()->toArray();
        $userGroupList = [];
        foreach ($groupList as $key => $val) {
            $unreadMessageInfo = $this->getUnReadMessageByGroup($val, $userInfo);
            $userGroupList[] = [
                'id' => $val['group_id'],
                'displayName' => $val['group_name'],
                'avatar' => $val['avatar'],
                'index' => "[0]群聊",
                'unread' => $unreadMessageInfo['unread'] ?? 0,
                'number_total' => GroupRelation::query()->where('group_id', $val['group_id'])->count(),
                'lastContent' => $unreadMessageInfo['lastContent'] ?? '',
                'lastContentType' => $unreadMessageInfo['lastContentType'] ?? '',
                'lastSendTime' => $unreadMessageInfo['lastSendTime'] ?? getMillisecond(),
            ];
        }


        return [
            'type' => WsMessage::MESSAGE_TYPE_INIT,
            'user_info' => $returnUserInfo,
            'user_contact' => $userContactList,
            'user_group' => $userGroupList
        ];
    }

    /**
     * 根据用户获取最后一条信息以及未读信息
     * @param array $user
     * @param array $currentUserInfo
     * @return array
     */
    private function getUnReadMessageByUser(array $user, array $currentUserInfo) : array
    {
        if (empty($user)) return [];

        $unread = FriendChatHistory::query()
            ->where('to_uid', $currentUserInfo['id'])
            ->where('from_uid', $user['id'])
            ->where('reception_state', FriendChatHistory::RECEPTION_STATE_NO)
            ->count();

        $lastMessage = FriendChatHistory::query()
            ->where('to_uid', $user['id'])
            ->orWhere('from_uid', $user['id'])
            ->orderBy('send_time', 'desc')
            ->first();

        return [
            'unread' => $unread,
            'lastContent' => $lastMessage['content'],
            'lastSendTime' => intval($lastMessage['send_time']),
            'lastContentType' => $lastMessage['type']
        ];
    }

    /**
     * 根据组获取最后一条信息以及未读信息
     * @param array $groupInfo
     * @param array $currentUserInfo
     * @return array
     */
    private function getUnReadMessageByGroup(array $groupInfo, array $currentUserInfo) : array
    {
        if (empty($currentUserInfo)) return [];
        $unread = Redis::getInstance()->sCard(ChatRedisKey::GROUP_CHAT_UNREAD_MESSAGE_BY_USER . $currentUserInfo['id']);
        Redis::getInstance()->del(ChatRedisKey::GROUP_CHAT_UNREAD_MESSAGE_BY_USER . $currentUserInfo['id']);

        $lastMessage = GroupChatHistory::query()->where('to_group_id', $groupInfo['group_id'])->orderBy('send_time', 'desc')->first();
        return [
            'unread' => $unread,
            'lastContent' => $lastMessage['content'] ?? '',
            'lastSendTime' => intval($lastMessage['send_time']) ?? 0,
            'lastContentType' => $lastMessage['type'] ?? ''
        ];
    }
}