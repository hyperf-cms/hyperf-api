<?php
namespace App\Foundation\Aspect;

use App\Controller\IndexController;
use App\Foundation\Facades\Log;
use App\Service\IndexService;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * AOP面向切面测试
 * @Aspect
 */
class FooAspect extends AbstractAspect
{
    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $classes = [
        IndexController::class,
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
//        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $startTime = microtime(true);
        Log::codeDebug()->info('日志调用前');
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理
        $endTime = microtime(true);
        echo $endTime - $startTime;
        Log::codeDebug()->info('日志调用后' . round(($endTime - $startTime) * 1000, 4));
        return $result;
    }
}