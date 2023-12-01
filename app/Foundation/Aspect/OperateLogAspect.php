<?php
namespace App\Foundation\Aspect;

use App\Foundation\Annotation\Explanation;
use App\Service\System\OperateLogService;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 操作日志类切面（用于记录操作日志）
 * @Author YiYuan
 * @Date 2023/12/1
 * Class OperateLogAspect
 */
#[Aspect]
class OperateLogAspect extends AbstractAspect
{
    #[Inject]
    protected RequestInterface $request;

    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [
        Explanation::class
    ];

    /**
     * 记录操作日志切面
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws \Hyperf\Di\Exception\Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        var_dump(1);
        // 在调用前进行某些处理
        $logData = OperateLogService::getInstance()->collectLogInfo();

        $result = $proceedingJoinPoint->process();

        //请求后处理
        $responseResult = json_decode($result->__toString(), true);
        $logData['response_result'] = $responseResult['msg'];
        $logData['response_code'] = $responseResult['code'];
        if (!empty($logData['action'])) OperateLog::add($logData);

        return $result;
    }
}