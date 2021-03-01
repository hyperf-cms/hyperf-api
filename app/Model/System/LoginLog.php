<?php

declare(strict_types=1);

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
     *
     * @var string
     */
    protected $table = 'login_log';

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
     * 记录操登陆
     * @param array $logData
     * @return bool
     */
    public static function add(array $logData = []) : bool
    {
        if (empty($logData)) return false;
        $operateLog = new static;
        $operateLog->created_at = date('Y-m-d H:i:s');
        $operateLog->updated_at = date('Y-m-d H:i:s');

        foreach ($logData as $key => $value) {
            $operateLog->{$key} = $value;
        }

        if (!$operateLog->save()) return false;
        return true;
    }
}