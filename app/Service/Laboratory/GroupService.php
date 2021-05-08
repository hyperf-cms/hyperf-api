<?php
namespace App\Service\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Foundation\Traits\Singleton;
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
     * @return array
     */
    public function getOnlineGroupMemberFd(string $groupId)
    {
        if (empty($groupId)) return [];

        //获取所有组员列表
        $uidList = GroupRelation::query()->where('group_id', $groupId)->pluck('uid');
        $fdList = [];
        foreach ($uidList as $uid) {
            if (!empty($fd = Redis::getInstance()->hget(ChatRedisKey::ONLINE_USER_FD_KEY, (string) $uid))) array_push($fdList, [
                'uid' => $uid,
                'fd' => $fd
            ]);
        }

        return $fdList;
    }
}