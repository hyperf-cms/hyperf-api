<?php

declare(strict_types=1);

namespace App\Controller;

use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\FreeApi;
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
     * 获取用户数据列表
     * @RequestMapping(path="/test", methods="get,post")
     */
    public function index()
    {
        //获取用户信息
        $userInfo = ConGet('user_info');
        $loginIp = getClientIp($this->request) ?? '';
//        $loginAddress = ip_to_address($loginIp);
        $userAgent = $this->request->header('user-agent');

        return $this->success([get_browser($this->request->getHeader('user-agent')[0])], '获取数据成功');
    }
}
