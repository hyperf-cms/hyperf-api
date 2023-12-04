<?php

declare (strict_types=1);
namespace App\Controller\Common;

use App\Controller\AbstractController;
use App\Model\System\GlobalConfig;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
/**
 * 公共请求控制器
 */
#[Controller(prefix: 'common')]
class CommonController extends AbstractController
{
    /**
     * 获取系统配置
     * @return  \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    #[RequestMapping(path: 'sys_config', methods: array('GET'))]
    public function getSysConfig()
    {
        $configList = GlobalConfig::query()->select('key_name', 'data')->where('type', GlobalConfig::TYPE_BY_BOOLEAN)->get()->toArray();
        $result = [];
        foreach ($configList as $item) {
            $result[$item['key_name']] = boolval($item['data']);
        }
        return $this->success($result);
    }
}