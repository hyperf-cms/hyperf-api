<?php

declare (strict_types=1);
namespace App\Controller\Common;

use App\Controller\AbstractController;
use App\Model\System\GlobalConfig;
use App\Pool\Redis;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: 'common/auth')]
class AuthCommonController extends AbstractController
{
    /**
     * 获取验证码操作
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    #[RequestMapping(path: 'verification_code', methods: array('GET'))]
    public function getVerificationCode()
    {
        $config = new \EasySwoole\VerifyCode\Config();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $result = $code->DrawCode(getRandStr(4));
        $imageBase64Code = $result->getImageBase64();
        $code = $result->getImageCode();
        $key = md5_rand();
        Redis::getInstance()->setex($key, 60, $code);
        return $this->success(['code' => $imageBase64Code, 'code_key' => $key]);
    }
    /**
     * 获取系统配置
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    #[RequestMapping(path: 'sys_config', methods: array('GET'))]
    public function getSysConfig()
    {
        $configList = GlobalConfig::query()->select('key_name', 'data')->where('type', GlobalConfig::TYPE_BY_BOOLEAN)->get()->toArray();
        $result = array_column($configList, 'key_name', 'data');
        return $this->success($result);
    }
}