<?php
//
//declare(strict_types=1);
//
//namespace App\Command;
//
//use App\Model\Auth\User;
//use App\Model\System\DictData;
//use App\Model\System\DictType;
//use App\Model\System\GlobalConfig;
//use Hyperf\Command\Command as HyperfCommand;
//use Hyperf\Command\Annotation\Command;
//use Donjan\Permission\Models\Permission;
//use Donjan\Permission\Models\Role;
//
///**
// * @Command
// */
//class InitDataSeederCommand extends HyperfCommand
//{
//    /**
//     * 执行的命令行
//     *
//     * @var string
//     */
//    protected $name = 'init:data_seeder';
//
//    public function configure()
//    {
//        parent::configure();
//        $this->setHelp('初始化数据，权限数据，字典数据');
//        $this->setDescription('初始化数据，权限数据，字典数据');
//    }
//
//    /**
//     * 命令执行方法
//     */
//    public function handle()
//    {
//        //创建权限
//        $permissionList = config('permissionData.permission_list');
//        foreach ($permissionList as $permission) {
//            if (empty(Permission::query()->find($permission['id']))) Permission::query()->insert($permission);
//            $this->line('添加权限成功----------------------------' . $permission['display_name']);
//        }
//
//        //初始化字典数据
//        $dictTypeList = config('dictData.dict_type');
//        foreach ($dictTypeList as $dictType) {
//            if (empty(DictType::query()->find($dictType['dict_id']))) DictType::query()->insert($dictType);
//        }
//        $dictDataList = config('dictData.dict_data');
//        foreach ($dictDataList as $dictData) {
//            if (empty(DictData::query()->find($dictData['dict_code']))) DictData::query()->insert($dictData);
//        }
//        $this->line('迁移字典数据成功', 'info');
//    }
//}