<?php
namespace App\Service\System;

use App\Foundation\Traits\Singleton;
use App\Foundation\Utils\FreeApi;
use App\Service\BaseService;

/**
 * 登陆日志服务类
 * Class LoginLogService
 * @package App\Service\System
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
        //获取请求参数
        $requireParams = $this->request->all();

        //获取登陆信息
        $loginIp = getClientIp($this->request) ?? '';
        $ipAddress = FreeApi::getResult($loginIp);
        $province = empty($ipAddress['province']) ? '' : $ipAddress['province'];
        $city = empty($ipAddress['city']) ? '' : $ipAddress['city'];
        $loginAddress = $province . $city;
        $browser = get_browser_os();
        $os = get_os();
        $loginTime = date('Y-m-d H:i:s');

        return [
           'username' => $requireParams['username'],
           'login_ip' => $loginIp,
           'login_address' => $loginAddress,
           'login_browser' => $browser,
           'os' => $os,
           'login_date' => $loginTime,
        ];
    }

}
