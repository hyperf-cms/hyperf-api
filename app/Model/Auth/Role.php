<?php

declare(strict_types=1);

namespace App\Model\Auth;

use Donjan\Permission\Models\Role as DonjanRole;

/**
 * 角色模型类
 * Class Role
 * @package App\Model\Auth
 * @Author YiYuan-Lin
 * @Date: 2021/1/21
 */
class Role extends DonjanRole
{
    /**
     * 声明超级管理员角色名
     */
    const SUPER_ADMIN = 'super_admin';

    /**
     * 根据橘色ID获取角色信息
     * @param $id
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    static function getOneByRoleId($id)
    {
        if (empty($id)) return [];

        $query = static::query();
        $query = $query->where('id', $id);

        return $query->first();
    }

}