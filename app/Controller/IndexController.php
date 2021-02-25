<?php

declare(strict_types=1);

namespace App\Controller;

use App\Foundation\Annotation\Explanation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

 /**
 * 测试控制器，一般用来测试一些代码
 * Class IndexController
 * @Controller
 */
class IndexController extends AbstractController
{
    /**
     * @Explanation(content="获取用户数据列表")
     * @Middleware(RequestMiddleware::class)
     * @RequestMapping(path="/test", methods="get,post")
     */
    public function index()
    {
        return $this->success(['key' => 'value'], '获取数据成功');
    }
}
