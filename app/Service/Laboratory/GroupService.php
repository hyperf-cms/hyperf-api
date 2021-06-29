<?php
namespace App\Service\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Foundation\Traits\Singleton;
use App\Model\Auth\User;
use App\Model\Laboratory\GroupChatHistory;
use App\Model\Laboratory\GroupRelation;
use App\Pool\Redis;
use App\Service\BaseService;

/**
 * 组消息服务类
 * Class GroupService
 * @package App\Service\Laboratory
 * @Author YiYuan-Lin
 * @Date: 2021/5/8
 */
class GroupService extends BaseService
{
    use Singleton;

    /**
     * 根据组ID获取在线组员FD
     * @param string $groupId
     * @param array $contactData
     * @param bool $isExcludeSelf
     * @return array
     */
    public function getOnlineGroupMemberFd(string $groupId, array $contactData = [], bool $isExcludeSelf = false) : array
    {
        if (empty($groupId)) return [];

        //获取所有组员列表
        $uidList = GroupRelation::query()->where('group_id', $groupId)->pluck('uid');
        $fdList = [];
        foreach ($uidList as $uid) {
            //判断如果排除本身，则只获取群其他成员fd
            if ($isExcludeSelf && !empty($contactData) && $uid == $contactData['fromUser']['id']) continue;
            if (!empty($fd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $uid))) array_push($fdList, [
                'uid' => $uid,
                'fd' => $fd
            ]);
        }
        return $fdList;
    }

    /**
     * 获取不在线用户列表
     * @param string $groupId
     * @return array
     */
    public function getUnOnlineGroupMember(string $groupId) : array
    {
        if (empty($groupId)) return [];

        //获取所有组员列表
        $uidList = GroupRelation::query()->where('group_id', $groupId)->pluck('uid')->toArray();
        foreach ($uidList as $key => $value) {
            //判断是否在线，在线则剔除
            if (!empty(Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $value))) {
                unset($uidList[$key]);
            }
        }
        return $uidList;
    }
}