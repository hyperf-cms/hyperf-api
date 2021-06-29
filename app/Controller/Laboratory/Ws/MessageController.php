<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\Group;
use App\Pool\Redis;
use App\Task\Laboratory\FriendWsTask;
use App\Task\Laboratory\GroupWsTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 消息控制器
 * Class MessageController
 * @package App\Controller\Laboratory\Ws
 * @Controller(prefix="message",server="ws")
 */
class MessageController extends AbstractController
{
    /**
     * 发送信息
     * @RequestMapping(path="forward_message",methods="POST")
     */
    public function forwardMessage()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];

        $content = json_encode($contactData['message']);
        $user = $contactData['user'];
        foreach ($contactData['contact'] as $item) {
            if ($item['is_group'] == 1) {
                $groupInfo = Group::query()->where('group_id', $item['id'])->first();
                if (empty($groupInfo)) continue;
                $groupInfo = objToArray($groupInfo);
                $this->container->get(GroupWsTask::class)->forwardMessage($groupInfo, $user, $content);
            }else {
                $userInfo = User::query()->where('id', $item['id'])->first();
                if (empty($userInfo)) continue;
                $userInfo = objToArray($userInfo);
                $this->container->get(FriendWsTask::class)->forwardMessage($userInfo, $user, $content);
            }
        }
    }

}

