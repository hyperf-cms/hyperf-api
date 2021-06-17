<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\System\GlobalConfig;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 后台控制相关控制器
 * Class ControlController
 * @Controller(prefix="setting/technique_module/control")
 */
class ControlController extends AbstractController
{
    /**
     * @Inject()
     * @var GlobalConfig
     */
    private $globalConfig;

    /**
     * 获取后台控制开关参数列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function getConfigList()
    {
        $query = $this->globalConfig->query();
        $list = $query->where('type', GlobalConfig::TYPE_BY_BOOLEAN)->get()->toArray();

        $result = [];
        foreach ($list as $key => $value) {
            $result[$value['key_name']] = (bool) ($value['data']);
        }
        return $this->success([
            'list' => $result
        ]);
    }

    /**
     * 开关控制
     * @RequestMapping(path="change_control", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function changeControl()
    {
        $requestData = $this->request->all();
        $params = [
            'key' => $requestData['key'] ?? '',
            'value' => $requestData['value'] ?? '',
        ];
        $rules = [
            'key' => 'required',
            'value' => 'required',
        ];
        $message = [
            'key.required' => 'key 缺失',
            'value.required' => 'value 缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $configQuery = GlobalConfig::where('key_name', $params['key'])->first();

        $configQuery->data = $params['value'];
        if (!$configQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改失败');

        return $this->successByMessage('修改成功');
    }
}