<?php

declare(strict_types=1);

namespace App\Controller;

use App\Foundation\Utils\GroupAvatar;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendRelation;
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

        $user = User::query()->get()->toArray();
        foreach ($user as $item) {
            foreach ($user as $key) {
                if ($key['id'] == $item['id']) continue;
                FriendRelation::query()->insert([
                    'uid' => $item['id'],
                    'friend_id' => $key['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
//       $picList = [
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face1.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face2.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face3.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face4.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face5.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face6.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face7.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face8.png',
//           'https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/admin_face/face9.png',
//       ];
//
//       GroupAvatar::init($picList, false, '121312');
//       $res = GroupAvatar::build();
//
//
//        return $this->success([
//            'list' => $res
//        ]);
    }
}
