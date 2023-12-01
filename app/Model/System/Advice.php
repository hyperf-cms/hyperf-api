<?php

declare (strict_types=1);
namespace App\Model\System;

use App\Model\Model;
class Advice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'advice';
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
    /**
     * 与user表关联
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function getUserName()
    {
        return $this->belongsTo('App\\Model\\Auth\\User', 'user_id', 'id');
    }
}