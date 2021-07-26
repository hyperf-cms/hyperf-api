<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Auth\User;
use App\Model\System\DictData;
use App\Model\System\DictType;
use App\Model\System\GlobalConfig;
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
        $permissionList = config('permissionData');
        $this->InitPermission($permissionList);

        //初始化字典数据
        $dictList = config('dictData');
        $this->InitDict($dictList);

        $globalConfigList = config('globalConfig.global_config');
        foreach ($globalConfigList as $globalConfig) {
            if (empty(GlobalConfig::query()->find($globalConfig['id']))) GlobalConfig::query()->insert($globalConfig);
        }
        $this->line('初始化全局参数成功', 'info');

        //添加默认角色到默认用户
        $user->assignRole($super_role->name);
        // 通过内置方法 line 在 Console 输出 Hello Hyperf.
        $this->line('初始化用户成功' . PHP_EOL . '默认用户名：admin@admin.com' . PHP_EOL . '默认密码：admin@admin.com' . PHP_EOL, 'info');
    }

    public function InitPermission(array $PermissionList,$pid = 0)
    {
        foreach ($PermissionList as  $v) {
            $p = Permission::query()->where('name', $v['name'])->first();
            if(!$p){
                $p = new Permission();
                $p->parent_id       = $pid;
                $p->name            = $v['name'];
                $p->display_name    = $v['display_name'];
                $p->display_desc    = $v['display_desc'];
                $p->url             = $v['url'];
                $p->component       = $v['component'];
                $p->guard_name      = $v['guard_name'];
                $p->icon            = $v['icon'];
                $p->type            = $v['type'];
                $p->hidden          = $v['hidden'];
                $p->status          = $v['status'];
                $p->sort            = $v['sort'];
                if( !$p->save() )
                    continue;
                $this->line('添加权限成功----------------------------' . $v['display_name']);
            }else{
                $this->line('权限已存在----------------------------' . $v['display_name']);
            }
            if ( isset($v['subfield']) )
                $this->InitPermission($v['subfield'],$p->id);
        }
    }

    public function InitDict(array $DictList)
    {
        foreach ($DictList as  $v) {
            $p = DictType::query()->where('dict_type', $v['dict_type'])->first();
            if(!$p){
                $p = new DictType();
                $p->dict_type    = $v['dict_type'];
                $p->dict_name    = $v['dict_name'];
                $p->remark       = $v['remark'];
                $p->status       = $v['status'];
                if( !$p->save() )
                    continue;
            }
            if( is_array($v['dict_data']) ){
                foreach ($v['dict_data'] as $vv) {
                    $p = DictData::query()->where('dict_type', $v['dict_type'])->where('dict_label',$vv['dict_label'])->first();
                    if(!$p){
                        $p = new DictType();
                        $p->dict_sort    = $vv['dict_sort'];
                        $p->dict_label   = $vv['dict_label'];
                        $p->dict_value   = $vv['dict_value'];
                        $p->dict_type    = $v['dict_type'];
                        $p->css_class    = $vv['css_class'];
                        $p->list_class   = $vv['list_class'];
                        $p->is_default   = $vv['is_default'];
                        $p->status       = $vv['status'];
                        $p->remark       = $vv['remark'];
                        if( !$p->save() )
                            continue;
                    }
                }
            }
        }
        $this->line('初始化字典数据成功', 'info');
    }

}