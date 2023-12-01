<?php

declare (strict_types=1);
namespace App\Controller\Laboratory;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendChatHistory;
use App\Model\Laboratory\FriendRelation;
use App\Service\Laboratory\MessageService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
/**
 * 好友模块
 * Class FriendController
 */
#[Controller(prefix: 'laboratory/chat_module/friend')]
class FriendController extends AbstractController
{
    
    #[RequestMapping(methods: array('GET'), path: 'history_message')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function historyMessage()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, 'ID参数不允许为空');
        }
        $userInfo = conGet('user_info');
        $messageQuery = FriendChatHistory::query()->where(function ($query) use($userInfo, $contactId) {
            $query->where('from_uid', $userInfo['id'])->where('to_uid', $contactId);
            if (!empty($this->request->query('date'))) {
                $beginTime = $this->request->query('date');
                $endTime = $this->request->query('date') + 86400000;
                $query->whereBetween('send_time', [$beginTime, $endTime]);
            }
            if (!empty($this->request->query('content'))) {
                $query->where('content', 'like', '%' . $this->request->query('content') . '%');
            }
        })->orWhere(function ($query) use($userInfo, $contactId) {
            $query->where('from_uid', $contactId)->where('to_uid', $userInfo['id']);
            if (!empty($this->request->query('date'))) {
                $beginTime = $this->request->query('date');
                $endTime = $this->request->query('date') + 86400000;
                $query->whereBetween('send_time', [$beginTime, $endTime]);
            }
            if (!empty($this->request->query('content'))) {
                $query->where('content', 'like', '%' . $this->request->query('content') . '%');
            }
        });
        $total = $messageQuery->count();
        $messageQuery = $this->pagingCondition($messageQuery, $this->request->all());
        $messageList = $messageQuery->orderBy('send_time', 'desc')->get()->toArray();
        $list = [];
        foreach ($messageList as $key => $value) {
            $sendTime = intval($value['send_time'] / 1000);
            //获取用户联系人
            $user = User::query()->select('id', 'desc', 'avatar')->where('id', $value['from_uid'])->first();
            $displayName = $user['desc'];
            $friendRemark = FriendRelation::query()->where('uid', $value['to_uid'])->where('friend_id', $value['from_uid'])->value('friend_remark');
            if (!empty($friendRemark) && $value['from_uid'] != $userInfo['id']) {
                $displayName = $friendRemark;
            }
            $temp = ['id' => $value['message_id'], 'status' => $value['status'], 'type' => $value['type'], 'fileSize' => $value['file_size'], 'fileName' => $value['file_name'], 'fileExt' => $value['file_ext'], 'sendTime' => date('Y-m-d', $sendTime) == date('Y-m-d') ? date('H:i:s', $sendTime) : date('Y-m-d, H:i:s', $sendTime), 'content' => $value['content'], 'avatar' => $user['avatar'], 'displayName' => $displayName, 'fromUser' => ['id' => $user['id'], 'avatar' => $user['avatar'] ?? '', 'displayName' => $displayName]];
            if ($temp['type'] == FriendChatHistory::FRIEND_CHAT_MESSAGE_TYPE_FORWARD) {
                $temp['content'] = MessageService::getInstance()->formatForwardMessage($temp['content'], $temp['fromUser']);
            }
            $list[] = $temp;
        }
        return $this->success(['list' => $list, 'total' => $total]);
    }
}