<?php

declare(strict_types=1);

namespace App\Model\Laboratory;

use App\Model\Model;

class GroupChatHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ct_group_chat_history';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * 添加组聊天记录
     * @param array $message
     * @param int $receptionState
     * @return bool
     */
    static function addMessage(array $message, int $receptionState = 0)
    {
        if (empty($message)) return false;

        $model = new self();
        $model->message_id = $message['id'];
        $model->type = $message['type'];
        $model->status = 'succeed';
        $model->send_time = $message['sendTime'];
        $model->content = $message['content'];
        $model->file_size = $message['fileSize'] ?? 0;
        $model->file_name = $message['fileName'] ?? '';
        $model->to_group_id = $message['toContactId'];
        $model->from_uid = $message['fromUser']['id'] ?? 0;
        $model->reception_state = $receptionState;

        return $model->save();
    }
}