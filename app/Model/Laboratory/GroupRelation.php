<?php

declare(strict_types=1);

namespace App\Model\Laboratory;

use App\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

/**
 * 群聊与组员关系
 * Class GroupRelation
 * @package App\Model\Laboratory
 * @Author YiYuan-Lin
 * @Date: 2021/5/22
 */
class GroupRelation extends Model
{
    use SoftDeletes;

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

    /**
     * 根据用户ID获取群聊等级
     * @param int $uid
     * @param string $groupId
     * @return \Hyperf\Utils\HigherOrderTapProxy|mixed|void
     */
    public static function getLevelById(int $uid, string $groupId)
    {
        if (empty($uid) || empty($groupId)) return false;
        return static::query()->where('uid', $uid)->where('group_id', $groupId)->value('level');
    }
}