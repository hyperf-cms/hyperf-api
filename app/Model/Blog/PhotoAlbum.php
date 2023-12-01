<?php

declare (strict_types=1);
namespace App\Model\Blog;

use App\Model\Model;
/**
 * 相册模型类
 * Class PhotoAlbum
 * @package App\Model\Blog
 * @Author YiYuan-Lin
 * @Date: 2021/4/2
 */
class PhotoAlbum extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'photo_album';
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected ?string $connection = 'default';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [];
}