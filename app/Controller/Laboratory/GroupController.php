<?php
declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use App\Model\Laboratory\Group;
use App\Model\Laboratory\GroupChatHistory;
use App\Model\Laboratory\GroupRelation;
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
        $messageQuery = GroupChatHistory::query()->where('to_group_id', $contactId)->where('type', '!=', GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_EVENT);
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

    /**
     * 获取群文件
     * @RequestMapping(path="group_file", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function groupFile()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) $this->throwExp(StatusCode::ERR_VALIDATION, '群ID参数不允许为空');
        $groupFileQuery = GroupChatHistory::query()->where('to_group_id', $contactId)->where('type', GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_FILE);
        if (!empty($this->request->query('date'))) {
            $beginTime = $this->request->query('date');
            $endTime = $this->request->query('date') + 86400000;
            $groupFileQuery->whereBetween('send_time', [$beginTime, $endTime]);
        }
        if(!empty($this->request->query('file_name'))) {
            $groupFileQuery->where('file_name', 'like', '%' . $this->request->query('file_name') . '%');
        }

        $groupFileQuery = $groupFileQuery->with("getFromUser:id,desc");
        $total = $groupFileQuery->count();
        $groupFileQuery = $this->pagingCondition($groupFileQuery, $this->request->all());
        $groupFileList = $groupFileQuery->orderBy('send_time', 'desc')->get()->toArray();

        return $this->success([
            'list' => $groupFileList,
            'total' => $total
        ]);
    }

    /**
     * 获取群照片
     * @RequestMapping(path="group_album", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function groupAlbum()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) $this->throwExp(StatusCode::ERR_VALIDATION, '群ID参数不允许为空');
        $groupFileQuery = GroupChatHistory::query()->where('to_group_id', $contactId)->where('type', GroupChatHistory::GROUP_CHAT_MESSAGE_TYPE_IMAGE);
        if (!empty($this->request->query('date'))) {
            $beginTime = $this->request->query('date');
            $endTime = $this->request->query('date') + 86400000;
            $groupFileQuery->whereBetween('send_time', [$beginTime, $endTime]);
        }
        if(!empty($this->request->query('file_name'))) {
            $groupFileQuery->where('file_name', 'like', '%' . $this->request->query('file_name') . '%');
        }
        $groupFileQuery = $groupFileQuery->with("getFromUser:id,desc,avatar");
        $total = $groupFileQuery->count();
        $groupFileQuery = $this->pagingCondition($groupFileQuery, $this->request->all());
        $groupFileList = $groupFileQuery->orderBy('send_time', 'desc')->get()->toArray();

        return $this->success([
            'list' => $groupFileList,
            'srcList' => array_column($groupFileList, 'content'),
            'total' => $total
        ]);
    }

    /**
     * 获取群邀请
     * @RequestMapping(path="group_invite", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function groupInvite()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) $this->throwExp(StatusCode::ERR_VALIDATION, '群ID参数不允许为空');
        if (empty($groupInfo = Group::findById($contactId))) $this->throwExp(StatusCode::ERR_EXCEPTION, '该组不存在');

        $groupMemberUidList = GroupRelation::query()->where('group_id', $contactId)->pluck('uid');
        $groupMemberList = User::query()->select('id', 'desc', 'avatar')->whereIn('id', $groupMemberUidList)->get()->toArray();
        $contactsSource = User::query()->select('id', 'desc', 'avatar')->whereNotIn('id', $groupMemberUidList)->get()->toArray();

        return $this->success([
            'group_member_list' => $groupMemberList,
            'contacts_source' => $contactsSource,
        ]);
    }

    /**
     * 获取群员管理列表
     * @RequestMapping(path="group_member_manage", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     */
    public function groupMemberManage()
    {
        $contactId = $this->request->query('contact_id') ?? '';
        if (empty($contactId)) $this->throwExp(StatusCode::ERR_VALIDATION, '群ID参数不允许为空');
        if (empty($groupInfo = Group::findById($contactId))) $this->throwExp(StatusCode::ERR_EXCEPTION, '该组不存在');

        $groupMemberQuery = (new GroupRelation())->setTable('a')->from('ct_group_relation as a')->where('a.group_id', $contactId);
        $groupMemberQuery = $groupMemberQuery->leftJoin('users as b', 'a.uid', '=', 'b.id');
        $groupMemberQuery = $groupMemberQuery->select('a.*', 'b.id', 'b.desc', 'b.avatar');

        if (!empty($this->request->query('desc')))  $groupMemberQuery->where('b.desc', 'like', '%' . $this->request->query('desc') . '%');
        $total = $groupMemberQuery->count();
        $groupMemberQuery = $this->pagingCondition($groupMemberQuery, $this->request->all());
        $groupMemberList = $groupMemberQuery->orderBy('level', 'asc')->get()->toArray();

        return $this->success([
            'list' => $groupMemberList,
            'total' => $total,
        ]);
    }
}
