<?php

declare(strict_types=1);

namespace App\Model\System;

use App\Model\Model;

class DictData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dict_data';

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

    protected $primaryKey = 'dict_code';
}