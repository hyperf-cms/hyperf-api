<?php
namespace App\Service\Laboratory;

use App\Foundation\Traits\Singleton;
use App\Model\Auth\User;
use App\Service\BaseService;

/**
 * 消息服务类
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

        //修改用户ID为WS分配的唯一ID
        $returnUserInfo['id'] = $userInfo['id'];
        $returnUserInfo['displayName'] = $userInfo['desc'];
        $returnUserInfo['avatar'] = $userInfo['avatar'];

        //获取用户联系人
        $userList = User::query()->where('id', '!=', $userInfo['id'])->get()->toArray();
        $userContactList = [];
        foreach ($userList as $key => $val) {
            $userContactList[] = [
                'id' => $val['id'],
                'displayName' => $val['desc'],
                'avatar' => $val['avatar'],
                'index' => $val['desc'],
                'unread' => 0,
                'lastContent' => '',
                'lastSendTime' => '1566047865417',
            ];
        }

        return [
            'type' => 'init',
            'user_info' => $returnUserInfo,
            'user_contact' => $userContactList
        ];
    }


    private function getUnReadMessageByUser($user)
    {

    }
}