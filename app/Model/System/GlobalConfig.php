<?php

declare(strict_types=1);

namespace App\Model\System;

use App\Model\Model;

/**
 * 参数配置
 * Class GlobalConfig
 * @package App\Model\System
 * @Author YiYuan-Lin
 * @Date: 2021/6/10
 */
class GlobalConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'global_config';

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
}