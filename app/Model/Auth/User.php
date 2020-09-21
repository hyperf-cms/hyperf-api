<?php

declare(strict_types=1);

namespace App\Model\Auth;

use Hyperf\DbConnection\Model\Model;
use Donjan\Permission\Traits\HasRoles;

class User extends Model
{
    use HasRoles;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

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