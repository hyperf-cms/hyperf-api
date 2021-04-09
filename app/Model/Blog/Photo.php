<?php

declare(strict_types=1);

namespace App\Model\Blog;

use App\Model\Model;

/**
 * 图片模型类
 * Class Photo
 * @package App\Model\Blog
 * @Author YiYuan-Lin
 * @Date: 2021/4/2
 */
class Photo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'photo';

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
     * 获取相册名
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function getPhotoAlbum()
    {
        return $this->belongsTo("App\Model\Blog\PhotoAlbum", 'photo_album', 'id');
    }
}