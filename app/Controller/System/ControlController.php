<?php

declare (strict_types=1);
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
 */
#[Controller(prefix: 'setting/technique_module/control')]
class ControlController extends AbstractController
{
    
    #[Inject]
    private GlobalConfig $globalConfig;
    
    #[RequestMapping(methods: array('GET'), path: 'list')]
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
    
    #[RequestMapping(methods: array('POST'), path: 'change_control')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function changeControl()
    {
        $requestData = $this->request->all();
        $params = ['key' => $requestData['key'] ?? '', 'value' => $requestData['value'] ?? ''];
        $rules = ['key' => 'required', 'value' => 'required'];
        $message = ['key.required' => 'key 缺失', 'value.required' => 'value 缺失'];
        $this->verifyParams($params, $rules, $message);
        $configQuery = GlobalConfig::where('key_name', $params['key'])->first();
        $configQuery->data = $params['value'];
        if (!$configQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '修改失败');
        }
        return $this->successByMessage('修改成功');
    }
}