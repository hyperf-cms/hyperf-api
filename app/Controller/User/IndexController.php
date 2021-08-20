<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * 测试控制器，一般用来测试一些代码
 * Class IndexController
 * @AutoController()
 */
class IndexController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * 获取用户数据列表
     */
    public function index()
    {
        return 123;
    }

    public function user()
    {
        return 1;
    }
}
