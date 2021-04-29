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
     * 建立组与用户联系
     * @param int $uid
     * @param int $groupId
     * @return bool
     */
    public static function buildRelation(int $uid, int $groupId)
    {
        $model = new static;
        $model->uid = $uid;
        $model->group_id = $groupId;
        return $model->save();
    }
}