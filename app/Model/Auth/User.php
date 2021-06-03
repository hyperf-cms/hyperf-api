<?php

declare(strict_types=1);

namespace App\Model\Auth;

use App\Model\Model;
use Donjan\Permission\Traits\HasRoles;

class User extends Model
{
    use HasRoles;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

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
     * 定义状态枚举
     */
    const STATUS_ON = 1;
    const STATUS_OFF= 0;

    /**
     * 定义性别
     */
    const SEX_BY_MALE = 1;
    const SEX_BY_Female = 0;

    /**
     * 根据用户ID获取用户信息
     * @param $id
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    static function getOneByUid($id)
    {
        if (empty($id)) return [];

        $query = static::query();
        $query = $query->where('id', $id);

        return $query->first();
    }
}