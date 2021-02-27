<?php

declare(strict_types=1);

namespace App\Model\System;

use App\Model\Model;

/**
 * 操作日志模型类
 * Class OperateLog
 * @package App\Model\System
 * @Author YiYuan-Lin
 * @Date: 2021/2/25
 */
class OperateLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'operate_log';

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
     * 记录操作日志
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