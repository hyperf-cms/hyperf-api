<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Auth\User;
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


    public function handle()
    {
        //初始化添加一个默认用户以及一个超级管理员角色
        if (User::query()->where('username', 'admin@admin.com')->first()) {
            $this->line('该用户已经创建成功' . PHP_EOL, 'warning');
        }

        $user = new User();
        $user->username = 'admin@admin.com';
        $user->password = md5('admin@admin.com');
        $user->status = 1;
        $user->last_login = time();
        $user->desc = '超级用户';
        $user->mobile = '13211035441';
        $user->avatar = '';
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

        $super_role = Role::create($super_role);
        $default_role = Role::create($default_role);

        //添加默认角色到默认用户
        $user->assignRole($super_role->name);

        // 通过内置方法 line 在 Console 输出 Hello Hyperf.
        $this->line('初始化用户成功' . PHP_EOL . '默认用户名：admin@admin.com' . PHP_EOL . '默认密码：admin@admin.com' . PHP_EOL, 'info');
    }
}