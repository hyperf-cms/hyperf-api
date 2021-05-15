<?php

declare(strict_types=1);

namespace App\Model\Laboratory;

use App\Model\Model;

class GroupRelation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ct_group_relation';

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
     * 群成员级别
     * 0：群主 1：管理员 2：普通成员
     */
    const GROUP_MEMBER_LEVEL_LORD = 0;
    const GROUP_MEMBER_LEVEL_MANAGER= 1;
    const GROUP_MEMBER_LEVEL_MEMBER = 2;

    /**
     * 建立组与用户联系
     * @param int $uid
     * @param string $groupId
     * @param int $level
     * @return bool
     */
    public static function buildRelation(int $uid, string $groupId, int $level = self::GROUP_MEMBER_LEVEL_MEMBER)
    {
        $model = new static;
        $model->uid = $uid;
        $model->group_id = $groupId;
        $model->level = $level;
        return $model->save();
    }

    /**
     * 获取用户信息
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function getUserInfo()
    {
        return $this->belongsTo("App\Model\Auth\User", 'uid', 'id');
    }
}