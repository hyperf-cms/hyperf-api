<?php
namespace App\Http\Service\System;

use App\Foundation\Annotation\Explanation;
use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Router\Dispatched;

/**
 * 登陆日志服务类
 * Class LoginLogService
 * @package App\Http\Service\System
 * @Author YiYuan-Lin
 * @Date: 2020/12/16
 */
class LoginLogService extends BaseService
{
    use Singleton;

    /**
     * 收集操作日志信息
     * @return array
     */
    public function collectLoginLogInfo() : array
    {
        //获取用户信息
        $userInfo = ConGet('user_info');
        $loginIp = getClientIp($this->request) ?? '';
        $loginAddress = ip_to_address($loginIp);
        $userAgent = $this->request->header('user-agent');



    }

}
