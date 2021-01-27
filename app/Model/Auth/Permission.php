<?php

declare(strict_types=1);

namespace App\Model\Auth;

use Donjan\Permission\Models\Permission as DonjanPermission;

/**
 * 权限模型类
 * Class Permission
 * @package App\Model\Auth
 * @Author YiYuan-Lin
 * @Date: 2020/12/3
 */
class Permission extends DonjanPermission
{
    /**
     * 声明权限类型枚举
     * 1：菜单
     * 2：按钮
     * 3：接口
     */
    const MENU_TYPE = 1;
    const BUTTON_TYPE = 2;
    const API_TYPE = 3;

    /**
     * 声明状态枚举
     * 1：开启
     * 0：挂芭比
     */
    const ON_STATUS = 1;
    const OFF_STATUS = 0;

    /**
     * 获取用户对应的菜单树状列表
     * @param Object [用户模型对象] $user
     * @return array
     */
    public static function getUserMenuList(Object $user) : array
    {
        $permissionList = self::getUserPermissions($user);
        $permissionList = objToArray($permissionList);
        $permissionList = array_column($permissionList, null, 'id');

        foreach ($permissionList as $key => $val) {
            if ($val['status'] == self::OFF_STATUS) unset($permissionList[$key]);
            if ($val['type'] != self::MENU_TYPE) unset($permissionList[$key]);
        }
        //使用引用传递递归数组
        $menuList = [];
        foreach($permissionList as $key => $value){
            if(isset($permissionList[$value['parent_id']])){
                $permissionList[$value['parent_id']]['child'][] = &$permissionList[$key];
            }else{
                $menuList[] = &$permissionList[$key];
            }
        }

        return $menuList;
    }

    /**
     * 获取用户拥有所有权限
     * @param object [用户模型对象] $user
     * @return array
     */
    public static function getUserPermissions(object $user) : array
    {
        $allPermissions = [];
        if (empty($user)) return $allPermissions;

        //判断当前登录用户是否为超级管理员,如果是的话返回所有权限
        return $user->hasRole(Role::SUPER_ADMIN)
            ? Permission::query()->get()->toArray()
            : objToArray($user->getAllPermissions());
    }

    /**
     * 获取所有权限（树状）
     * @return array
     */
    public static function getAllPermissionByTree() : array
    {
        //获取所有权限列表
        $permissionList = static::query()->select('id', 'parent_id', 'display_name', 'name')
            ->where('status', static::ON_STATUS)
            ->orderBy('sort', 'asc')
            ->get()->toArray();
        $permissionList = array_column($permissionList, null, 'id');

        $allPermission = [];
        foreach($permissionList as $key => $value){
            if(isset($permissionList[$value['parent_id']])){
                $permissionList[$value['parent_id']]['child'][] = &$permissionList[$key];
            }else{
                $allPermission[] = &$permissionList[$key];
            }
        }

        return $allPermission;
    }
}