<?php

declare(strict_types=1);

namespace App\Controller\Common;

use App\Controller\AbstractController;
use App\Model\System\GlobalConfig;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\ApplicationContext;

/**
 * @Controller(prefix="common/auth")
 */
class AuthCommonController extends AbstractController
{
    /**
     * 获取验证码操作
     * @RequestMapping(path="verification_code", methods="get")
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getVerificationCode()
    {
        $config = new \EasySwoole\VerifyCode\Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);

        $result = $code->DrawCode();
        $imageBase64Code = $result->getImageBase64();
        $code = $result->getImageCode();

        $key = md5_rand();
        $container = ApplicationContext::getContainer();
        $redis = $container->get(\Hyperf\Redis\Redis::class);
        $redis->setex($key, 60, $code);

        return $this->success([
            'code' => $imageBase64Code,
            'code_key' => $key,
        ]);
    }

    /**
     * 获取系统配置
     * @RequestMapping(path="sys_config", methods="get")
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getSysConfig()
    {
        $configList = GlobalConfig::query()->select('key_name', 'data')
            ->where('type', GlobalConfig::TYPE_BY_BOOLEAN)
            ->get()->toArray();

        $result = array_column($configList, 'key_name', 'data');
        return $this->success($result);
    }
}
