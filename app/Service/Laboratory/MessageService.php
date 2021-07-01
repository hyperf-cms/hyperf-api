<?php
namespace App\Service\Laboratory;

use App\Foundation\Traits\Singleton;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\GroupChatHistory;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Swoole\Http\Request;


/**
 * 消息服务类
 * Class MessageService
 * @package App\Service\Setting
 * @Author YiYuan-Lin
 * @Date: 2021/3/12
 */
class MessageService extends BaseService
{
    use Singleton;

    /**
     * 格式化转发类型消息
     * @param string $content
     * @param array $fromUser
     * @return array
     */
    public function formatForwardMessage(string $content, array $fromUser)
    {
        if (empty($content)) return [];
        $content = json_decode($content, true);
        if (is_null($content)) return [];

        $messageIdList = array_column($content, 'id');
        $isGroup = $content[0]['is_group'] ?? false;

        $messageQuery = $isGroup == true ? GroupChatHistory::query() : FriendChatHistory::query();
        $messageList = $messageQuery->whereIn('message_id', $messageIdList)->orderBy('send_time', 'asc')->get()->toArray();Dispatched;
        if (empty($messageList)) return [];
        foreach ($messageList as $key => $value) {
            if ($value['from_uid'] != 0) $messageList[$key]['fromUser'] = [
                'id' => $value['from_uid'],
                'avatar' => User::query()->where('id', $value['from_uid'])->value('avatar') ?? '',
                'displayName' => User::query()->where('id', $value['from_uid'])->value('desc') ?? '',
            ];
        }
        $total = count($messageList);

        return [
            'message' => $messageList,
            'fromUser' => $fromUser,
            'total' => $total
        ];
    }


}