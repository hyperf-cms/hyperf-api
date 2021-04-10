<?php
declare(strict_types = 1);

namespace App\Task;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Task\Annotation\Task;

/**
 * 组消息传递异步任务
 * Class GroupTask
 * @package App\Task
 * @Author YiYuan-Lin
 * @Date: 2021/3/23
 */
class GroupTask
{
    /**
     * @Inject()
     * @var \Hyperf\WebSocketServer\Sender
     */
    private $sender;

    /**
     * @Task()
     * @param $fds
     * @param $username
     * @param $avatar
     * @param $groupId
     * @param $type
     * @param $content
     * @param $cid
     * @param $mine
     * @param $fromId
     * @param $timestamp
     *
     * @return bool
     */
    public function sendMessage(
        $fds,
        $username,
        $avatar,
        $groupId,
        $type,
        $content,
        $cid,
        $mine,
        $fromId,
        $timestamp
    ) {
        if (!$fds) {
            return false;
        }
        $data   = [
            'username'  => $username,
            'avatar'    => $avatar,
            'id'        => $groupId,
            'type'      => $type,
            'content'   => $content,
            'cid'       => $cid,
            'mine'      => $mine,
            'fromid'    => $fromId,
            'timestamp' => $timestamp,
        ];


        Server::sendToAll($result, $fds);
        return true;
    }

    /**
     * @Task()
     * @param int   $fd
     * @param array $data
     */
    public function agreeApply(int $fd, array $data)
    {
        $result = wsSuccess(WsMessage::WS_MESSAGE_CMD_EVENT, WsMessage::EVENT_GROUP_AGREE_APPLY, $data);
        $this->sender->push($fd, $result);
    }
}
