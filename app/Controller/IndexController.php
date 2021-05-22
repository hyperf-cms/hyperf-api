<?php

declare(strict_types=1);

namespace App\Controller;

use App\Foundation\Utils\GroupAvatar;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 测试控制器，一般用来测试一些代码
 * Class IndexController
 * @Controller
 */
class IndexController extends AbstractController
{
    /**
     * 获取用户数据列表
     * @RequestMapping(path="/test", methods="get,post")
     */
    public function index()
    {
       $picList = [
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face1.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face2.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face3.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face4.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face5.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face6.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face7.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face8.png',
           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face9.png',
       ];

       GroupAvatar::init($picList, false, '121312');
       $res = GroupAvatar::build();


        return $this->success([
            'list' => $res
        ]);
    }
}
