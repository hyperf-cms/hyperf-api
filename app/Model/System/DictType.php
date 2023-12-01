<?php

declare (strict_types=1);
namespace App\Model\System;

use App\Model\Model;
class DictType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'dict_type';
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
    protected string $primaryKey = 'dict_id';
}