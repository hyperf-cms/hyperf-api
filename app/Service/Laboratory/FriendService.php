<?php
namespace App\Service\Laboratory;

use App\Constants\Laboratory\ChatRedisKey;
use App\Foundation\Traits\Singleton;
use App\Model\Laboratory\GroupRelation;
use App\Pool\Redis;
use App\Service\BaseService;

/**
 * 好友消息服务类
 * Class FriendService
 * @package App\Service\Laboratory
 * @Author YiYuan-Lin
 * @Date: 2021/5/21
 */
class FriendService extends BaseService
{
    use Singleton;

    /**
     * 获取在线用户
     * @param array $userInfo
     * @param bool $isExcludeSelf
     * @return array
     */
    public function getOnlineFriendList(array $userInfo, bool $isExcludeSelf = false)
    {
        if (empty($userInfo)) return [];
        $allOnlineFdList =  Redis::getInstance()->hGetAll(ChatRedisKey::ONLINE_USER_FD_KEY);

        $fdList = [];
        foreach ($allOnlineFdList as $uid => $fd) {
            if ($isExcludeSelf && $uid == $userInfo['id']) continue;
            array_push($fdList, [
                'uid' => $uid,
                'fd' => $fd
            ]);
        }

        return $fdList;
    }
}