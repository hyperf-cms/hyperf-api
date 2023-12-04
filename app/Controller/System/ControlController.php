<?php

declare (strict_types=1);
namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
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
 */
#[Controller(prefix: 'setting/technique_module/control')]
class ControlController extends AbstractController
{
    #[Inject]
    private GlobalConfig $globalConfig;

    /**
     * 列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function getConfigList()
    {
        $query = $this->globalConfig->query();
        $list = $query->where('type', GlobalConfig::TYPE_BY_BOOLEAN)->get()->toArray();
        $result = [];
        foreach ($list as $key => $value) {
            $result[$value['key_name']] = (bool) $value['data'];
        }
        return $this->success(['list' => $result]);
    }

    /**
     * 修改控制状态
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '修改控制状态')]
    #[RequestMapping(path: 'change_control', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function changeControl()
    {
        $requestData = $this->request->all();
        $params = [
            'key' => $requestData['key'] ?? '',
            'value' => $requestData['value'] ?? ''
        ];
        $rules = [
            'key' => 'required',
            'value' => 'required'
        ];
        $message = [
            'key.required' => 'key 缺失',
            'value.required' => 'value 缺失'
        ];
        $this->verifyParams($params, $rules, $message);
        $configQuery = GlobalConfig::where('key_name', $params['key'])->first();
        $configQuery->data = $params['value'];
        if (!$configQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '修改失败');
        }
        return $this->successByMessage('修改成功');
    }
}