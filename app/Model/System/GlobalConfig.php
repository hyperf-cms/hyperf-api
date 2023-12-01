<?php

declare (strict_types=1);
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
    protected ?string $table = 'global_config';
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
     * 定义参数类型枚举
     */
    const TYPE_BY_TEXT = 'text';
    const TYPE_BY_JSON = 'json';
    const TYPE_BY_HTML = 'html';
    const TYPE_BY_BOOLEAN = 'boolean';
    /**
     * 根据KeyName获取参数信息
     * @param string $keyName
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    public static function getOneByKeyName(string $keyName)
    {
        if (empty($keyName)) {
            return [];
        }
        return static::query()->where('key_name', $keyName)->first();
    }
}