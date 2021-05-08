<?php
declare(strict_types = 1);

namespace App\Controller\Laboratory\Ws;

use App\Constants\Laboratory\ChatRedisKey;
use App\Constants\Laboratory\GroupEvent;
use App\Constants\Laboratory\WsMessage;
use App\Controller\AbstractController;
use App\Foundation\Facades\MessageParser;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\Group;
use App\Model\Laboratory\GroupRelation;
use App\Pool\Redis;
use App\Task\Laboratory\GroupWsTask;
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
     * 创建组
     * @RequestMapping(path="create_group",methods="GET")
     */
    public function createGroup()
    {
        $chatMessage = MessageParser::decode(conGet('chat_message'));
        $contactData = $chatMessage['message'];

        $groupInsertData = [];
        $groupInsertData['group_id'] = getRandStr(16);
        $groupInsertData['uid'] = $contactData['creator']['id'];
        $groupInsertData['group_name'] = $contactData['name'];
        $groupInsertData['avatar'] = $contactData['avatar'] ?? '';
        $groupInsertData['size'] = $contactData['size'] ?? 200;
        $groupInsertData['introduction'] = $contactData['introduction'] ?? '';
        $groupInsertData['validation'] = $contactData['validation'] ?? 0;
        $groupInsertData['created_at'] = date('Y-m-d H:i:s');
        $groupInsertData['updated_at'] = date('Y-m-d H:i:s');
        Group::query()->insert($groupInsertData);
        GroupRelation::buildRelation($groupInsertData['uid'], $groupInsertData['group_id']);

        if (!empty($contactData['checkedContacts'])) {
            $contactIdList = array_column($contactData['checkedContacts'], 'id');
            if (!empty($contactIdList)) {
                foreach ($contactIdList as $contactId) {
                    GroupRelation::buildRelation($contactId, $groupInsertData['group_id']);
                }
            }
        }

        //推送创建组事件
        $this->container->get(GroupWsTask::class)->pushEvent(GroupEvent::CREATE_GROUP_EVENT, $groupInsertData);
    }
}

