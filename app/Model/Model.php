<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\DbConnection\Traits\HasContainer;
use Hyperf\DbConnection\Traits\HasRepository;

abstract class Model extends BaseModel
{
    use HasContainer;
    use HasRepository;

    /**
     * 根据ID获取单条数据
     * @param int $id
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    static function findById(int $id)
    {
        if (empty($id)) return [];

        return static::query()->where('id', $id)->first();
    }

}
