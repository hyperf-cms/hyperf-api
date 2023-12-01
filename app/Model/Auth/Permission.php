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
     * 1：目录
     * 2：菜单
     * 3：接口/接口
     */
    const DIRECTORY_TYPE = 1;
    const MENU_TYPE = 2;
    const BUTTON_OR_API_TYPE = 3;

    /**
     * 声明状态枚举
     * 1：开启
     * 0：关闭
     */
    const ON_STATUS = 1;
    const OFF_STATUS = 0;

    /**
     * 声明是否隐藏显示枚举
     * 1： 是
     * 0： 否
     */
    const IS_HIDDEN = 1;
    const IS_NOT_HIDDEN = 0;

    /**
     * 获取用户对应的菜单树状列表
     * @param Object $user [用户模型对象] $user
     * @return array
     */
    public static function getUserMenuList(Object $user) : array
    {
        $permissionList = self::getUserPermissions($user);
        $permissionList = objToArray($permissionList);
        $permissionList = array_column($permissionList, null, 'id');

        //使用引用传递递归数组
        $menuList = [];
        foreach($permissionList as $key => $value){
            if(isset($permissionList[$value['parent_id']])){
                $permissionList[$value['parent_id']]['child'][] = &$permissionList[$key];
            }else{
                $menuList[] = &$permissionList[$key];
            }
        }
        //递归过滤 不符合条件的数据
        $menuList = static::checkPermissionFilter($menuList);
        return $menuList;
    }

    /**
     * 检查权限是否需要过滤
     * @param array $item
     * @return array
     */
    private static function checkPermissionFilter(array $item) : array
    {
        if (!empty($item)) {
            foreach ($item as $key => $value) {
                if ($value['status'] == self::OFF_STATUS) unset($item[$key]);
                if ($value['type'] == self::BUTTON_OR_API_TYPE) unset($item[$key]);
                if ($value['hidden'] == self::IS_HIDDEN) unset($item[$key]);
                if (!empty($item[$key]['child']))  {
                    $item[$key]['child'] = array_values(static::checkPermissionFilter($item[$key]['child']));
                }
            }
           return array_values($item);
        }
        return [];
    }

    /**
     * 获取用户拥有所有权限
     * @param object $user [用户模型对象] $user
     * @return array
     */
    public static function getUserPermissions(object $user) : array
    {
        $allPermissions = [];
        if (empty($user)) return $allPermissions;

        $superRoleHasPermission = Permission::query()->orderBy('sort', 'asc')->get()->toArray();
        $userHasPermission = objToArray($user->getAllPermissions());
        array_multisort(array_column($userHasPermission, 'sort'), SORT_ASC, $userHasPermission);

        //判断当前登录用户是否为超级管理员,如果是的话返回所有权限
        return $user->hasRole(Role::SUPER_ADMIN) ? $superRoleHasPermission : $userHasPermission;

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