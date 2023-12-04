<?php

declare (strict_types=1);
namespace App\Controller;

use App\Model\Auth\User;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 测试控制器，一般用来测试一些代码
 * Class IndexController
 */
#[Controller]
class IndexController extends AbstractController
{
    /**
     * 获取用户数据列表
     */
    #[RequestMapping(path: '/test',methods: "get,post")]
    public function index()
    {
        $user = User::findById(985);
        $user->assignRole('tourist_admin');
//        var_dump($user->assignRole('tourist_admin'));
        return $this->successByMessage('测试成功');
    }
}