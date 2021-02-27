<?php
namespace App\Foundation\Aspect;

use App\Controller\Auth\UserController;
use App\Controller\IndexController;
use App\Foundation\Annotation\Explanation;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;

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
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
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
        try {
            // 在调用前进行某些处理
            $requireParams = $this->request->all();

            $requestController = $this->request->getAttribute(Dispatched::class)->handler->callback;
            $actionController = $requestController[0];
            $actionMethod = $requestController[1];
            $actionUrl = $this->request->getUri()->getPath();
            $explanation = AnnotationCollector::getMethodsByAnnotation(Explanation::class);
            $classMethodsExplanation = [];
            foreach ($explanation as $key => $value) {
                $classMethodsExplanation[$value['class']][$value['method']] = $value['annotation']->content;
            }
            $content = $classMethodsExplanation[$actionController][$actionMethod];
            $userInfo = ConGet('user_info');

            $logData = [];
            $logData['action'] = $content ?? '';
            $logData['data'] = json_encode($requireParams) ?? '';
            $logData['username'] = $userInfo['username'] ?? '';
            $logData['operator'] = $userInfo['desc'] ?? '';
            $logData['uid'] = $userInfo['id'];
            $logData['target_class'] = $actionController;
            $logData['target_method'] = $actionMethod;
            $logData['target_url'] = $actionUrl;
            $logData['request_ip'] = getClientIp($this->request);
            $logData['request_method'] = ucwords($this->request->getMethod());

            $result = $proceedingJoinPoint->process();

            //请求后处理
            $responseResult = json_decode($this->result->__toString(), true);
            $logData['response_result'] = $responseResult['msg'];
            $logData['response_code'] = $responseResult['code'];
            OperateLog::add($logData);

            return $result;
        }catch (\Exception $e) {
            $logData['response_result'] = $e->getMessage();
            $logData['response_code'] = $e->getCode();
            OperateLog::add($logData);
        }
    }
}