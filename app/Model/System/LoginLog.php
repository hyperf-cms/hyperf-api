<?php

declare (strict_types=1);
namespace App\Model\System;

use App\Model\Model;
/**
 * 登陆日志模型类
 * Class LoginLog
 * @package App\Model\System
 * @Author YiYuan-Lin
 * @Date: 2021/3/1
 */
class LoginLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'login_log';

    /**
     * The connection name for the model.
     */
    protected ?string $connection = 'default';

    /**
     * 是否自定更新时间戳
     */
    public bool $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [];
}