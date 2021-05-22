<?php

declare(strict_types=1);

namespace App\Model\Laboratory;

use App\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ct_group';

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
     * 声明主键
     * @var
     */
    public $primaryKey = 'group_id';

    protected $keyType = 'string';

    /**
     * 声明是否群组标识
     * 1：是 0：否
     */
    const IS_GROUP_TYPE = 1;
    const IS_NOT_GROUP_TYPE = 0;

    /**
     * 声明群聊默认头像
     */
    const DEFAULT_GROUP_AVATAR = 'https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_4/594f172886b3617e9cf8e29cd65f342b.png';
}