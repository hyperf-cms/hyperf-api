<?php

declare(strict_types=1);

namespace App\Model\Laboratory\Bilibili;

use App\Model\Model;

/**
 * UP主数据采集表
 * Class UpUserReport
 * @package App\Model\Laboratory\Bilibili
 * @Author YiYuan-Lin
 * @Date: 2021/8/20
 */
class UpUserReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bili_up_user_report';

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