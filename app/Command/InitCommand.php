<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Auth\User;
use App\Model\System\DictData;
use App\Model\System\DictType;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Donjan\Permission\Models\Permission;
use Donjan\Permission\Models\Role;

/**
 * @Command
 */
class InitCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     *
     * @var string
     */
    protected $name = 'init';

    public function configure()
    {
        parent::configure();
        $this->setHelp('HyperfCms 项目初始化');
        $this->setDescription('HyperfCms 项目初始化');
    }

    /**
     * 命令执行方法
     */
    public function handle()
    {
        //初始化添加一个默认用户以及一个超级管理员角色
        if (User::query()->where('username', 'admin@admin.com')->first()) {
            $this->line('该用户已经创建' . PHP_EOL, 'warning');
        }

        $user = new User();
        $user->username = 'admin@admin.com';
        $user->password = md5('admin@admin.com');
        $user->status = User::STATUS_ON;
        $user->last_login = time();
        $user->desc = '超级用户';
        $user->mobile = '1800000000';
        $user->sex = User::SEX_BY_MALE;
        $user->email = 'admin@admin.com';
        $user->avatar = 'http://landlord-res.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1, 10) .'.png';
        $user->save();
        $super_role = [
            'name' => 'super_admin',
            'guard_name' => 'web',
            'description' => '超级管理员'
        ];

        $default_role = [
            'name' => 'default_admin',
            'guard_name' => 'web',
            'description' => '普通管理员'
        ];

        $tourist_role = [
            'name' => 'tourist_admin',
            'guard_name' => 'web',
            'description' => '游客'
        ];

        //创建默认的两个角色
        $super_role = Role::create($super_role);
        $default_role = Role::create($default_role);
        $tourist_role = Role::create($tourist_role);

        //创建权限
        $permissionList = config('permissionData.permission_list');
        foreach ($permissionList as $permission) {
            if (empty(Permission::query()->find($permission['id']))) Permission::query()->insert($permission);
            $this->line('添加权限成功----------------------------' . $permission['display_name']);
        }

        //初始化字典数据
        $dictTypeList = config('dictData.dict_type');
        foreach ($dictTypeList as $dictType) {
            if (empty(DictType::query()->find($dictType['dict_id']))) DictType::query()->insert($dictType);
        }
        $dictDataList = config('dictData.dict_data');
        foreach ($dictDataList as $dictData) {
            if (empty(DictData::query()->find($dictData['dict_code']))) DictData::query()->insert($dictData);
        }
        $this->line('初始化字典数据成功', 'info');

        //添加默认角色到默认用户
        $user->assignRole($super_role->name);
        // 通过内置方法 line 在 Console 输出 Hello Hyperf.
        $this->line('初始化用户成功' . PHP_EOL . '默认用户名：admin@admin.com' . PHP_EOL . '默认密码：admin@admin.com' . PHP_EOL, 'info');
    }
}