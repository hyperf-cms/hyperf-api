<?php
declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use App\Model\Laboratory\GroupChatHistory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;

/**
 * 组模块
 * Class GroupController
 * @Controller(prefix="laboratory/chat_module/group")
 */
class GroupController extends AbstractController
{
    /**
     * 获取历史记录
     * @RequestMapping(path="history_message", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function historyMessage()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID参数不允许为空');
        $messageQuery = GroupChatHistory::query()->where('to_group_id', $contactId)->where('type', '!=', 'event');
        if (!empty($this->request->query('date'))) {
            $beginTime = $this->request->query('date');
            $endTime = $this->request->query('date') + 86400000;
            $messageQuery->whereBetween('send_time', [$beginTime, $endTime]);
        }
        if(!empty($this->request->query('content'))) {
            $messageQuery->where('content', 'like', '%' . $this->request->query('content') . '%');
        }

        $total = $messageQuery->count();
        $messageQuery = $this->pagingCondition($messageQuery, $this->request->all());
        $messageList = $messageQuery->orderBy('send_time', 'desc')->get()->toArray();

        $list = [];
        foreach ($messageList as $key => $value) {
            $sendTime = intval($value['send_time'] / 1000);
            $list[] = [
                'id' => $value['message_id'],
                'status' => $value['status'],
                'type' => $value['type'],
                'fileSize' => $value['file_size'],
                'fileName' => $value['file_name'],
                'sendTime' => date('Y-m-d', $sendTime) == date('Y-m-d') ? date('H:i:s', $sendTime) : date('Y-m-d, H:i:s', $sendTime) ,
                'content' => $value['content'],
                'avatar' => User::query()->where('id', $value['from_uid'])->value('avatar'),
                'displayName' => User::query()->where('id', $value['from_uid'])->value('desc'),
            ];
        }
        return $this->success([
            'list' => $list,
            'total' => $total
        ]);
    }
}