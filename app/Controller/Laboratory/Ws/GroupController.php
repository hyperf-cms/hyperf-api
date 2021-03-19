<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Pool\Redis;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 组聊天控制器
 * Class GroupController
 * @package App\Controller\Laboratory\Ws
 * @Controller(prefix="group",server="ws")
 */
class GroupController extends AbstractController
{
    /**
     * 发送信息
     * @RequestMapping(path="send_message",methods="GET")
     */
    public function sendMessage()
    {
        /**
         * @var WsProtocol $protocol
         */
        $protocol = Context::get('request');
        $data     = $protocol->getData();

        $check = GroupService::checkNotGroupRelation((int)$data['from_user_id'], (int)$data['to_id']);

        if (!$check) {
            throw new ApiException(ErrorCode::GROUP_NOT_MEMBER, $data['message_id']);
        }
        $groupChatHistoryInfo = GroupService::createGroupChatHistory($data['message_id'], (int)$data['from_user_id'], (int)$data['to_id'], (string)$data['content']);

        $userInfo = UserService::findUserInfoById((int)$data['from_user_id']);

        $userIds = GroupService::getGroupRelationUserIdsById((int)$data['to_id']);
        $userIds = array_column($userIds, 'uid');

        $fds = [];

        $selfFd = $protocol->getFd();

        foreach ($userIds as $userId) {
            $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$userId, 'fd') ?? '';
            if ($fd && ($fd != $selfFd)) {
                array_push($fds, $fd);
            }
        }
        $this->container->get(GroupTask::class)->sendMessage($fds,
            $userInfo->username,
            $userInfo->avatar,
            $data['to_id'],
            UserApplication::APPLICATION_TYPE_GROUP,
            $data['content'],
            $data['message_id'],
            false,
            $data['from_user_id'],
            $groupChatHistoryInfo->created_at->getTimestamp() * 1000);

        return ['message_id' => $data['message_id'] ?? ''];
    }
}

