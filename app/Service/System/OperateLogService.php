<?php
namespace App\Service\System;

use App\Foundation\Annotation\Explanation;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Router\Dispatched;

/**
 * 操作日志服务类
 * Class OperateLogService
 * @package App\Service\System
 * @Author YiYuan-Lin
 * @Date: 2020/12/16
 */
class OperateLogService extends BaseService
{
    use Singleton;

    /**
     * 收集操作日志信息
     * @return array
     */
    public function collectLogInfo() : array
    {
        //获取请求参数
        $requireParams = $this->request->all();
        //获取目标控制器以及方法
        $requestController = $this->request->getAttribute(Dispatched::class)->handler->callback;
        $actionController = $requestController[0];
        $actionMethod = $requestController[1];
        //获取请求路由
        $actionUrl = $this->request->getUri()->getPath();
        //获取注解信息
        $explanation = AnnotationCollector::getMethodsByAnnotation(Explanation::class);
        $classMethodsExplanation = [];
        foreach ($explanation as $key => $value) {
            $classMethodsExplanation[$value['class']][$value['method']] = $value['annotation']->content;
        }
        $content = $classMethodsExplanation[$actionController][$actionMethod] ?? '';
        if (empty($content))  return [];

        //获取用户信息
        $userInfo = conGet('user_info');
        if (empty($userInfo)) return [];

        return [
            'action' => $content ?? '',
            'data' => json_encode($requireParams) ?? [],
            'username' => $userInfo['username'] ?? '',
            'operator' => $userInfo['desc'] ?? '',
            'uid' => $userInfo['id'] ?? '',
            'target_class' => $actionController ?? '',
            'target_method' => $actionMethod ?? '',
            'target_url' => $actionUrl ?? '',
            'request_ip' => getClientIp($this->request) ?? '',
            'request_method' => ucwords($this->request->getMethod()) ?? '',
        ];
    }


}
