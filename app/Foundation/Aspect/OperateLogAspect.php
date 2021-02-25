<?php
namespace App\Foundation\Aspect;

use App\Controller\IndexController;
use App\Foundation\Annotation\Explanation;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 操作日志类切面（用于记录操作日志）
 * @Aspect
 */
class OperateLogAspect extends AbstractAspect
{
    /**
     * @Inject()
     * @var RequestInterface
     */
    protected $request;

    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $classes = [
        IndexController::class,
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 在调用前进行某些处理
        $requireParams = $this->request->getQueryParams();
        $explanation = AnnotationCollector::getMethodsByAnnotation(Explanation::class);
        $targetClass = $explanation[0]['class'];
        $targetMethod = $explanation[0]['method'];
        $annotation = $explanation[0]['annotation'];
        $userInfo = ConGet('user_info');
        $result = $proceedingJoinPoint->process();

        $responseResult = $result->__toString();

        //请求后处理
        $logData = [];
        $logData['action'] = $annotation->content ?? '';
        $logData['data'] = json_encode($requireParams) ?? '';
        $logData['username'] = $userInfo['username'] ?? '';
        $logData['operator'] = $userInfo['desc'] ?? '';
        $logData['dealResult'] = json_encode($responseResult);
        $logData['uid'] = $userInfo['id'];
        $logData['target_class'] = $targetClass;
        $logData['target_method'] = $targetMethod;
        OperateLog::recordLog($logData);

        return $result;
    }
}