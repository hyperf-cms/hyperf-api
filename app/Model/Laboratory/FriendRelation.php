<?php

declare(strict_types=1);

namespace App\Model\Laboratory;

use App\Model\Model;

class FriendRelation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ct_friend_relation';

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
     * 用户在线状态
     * 1： 在线
     * 0： 不在线
     */
    const FRIEND_ONLINE_STATUS = 1;
    const FRIEND_ONLINE_STATUS_NO = 0;

    /**
     * 与user表关联
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function getUser() {
        return $this->belongsTo('App\Model\Auth\User', 'friend_id', 'id');
    }
}