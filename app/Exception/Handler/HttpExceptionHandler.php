<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * HTTP请求异常处理器
 * Class HttpExceptionHandler
 * @package App\Exception\Handler
 * @Author YiYuan-Lin
 * @Date: 2020/9/18
 */
class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }


    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        //判断是否是路由方法不允许
        if ($throwable instanceof MethodNotAllowedHttpException) {
            $errorJsonData = json_encode([
                'code' => $throwable->getStatusCode(),
                'message' => '路由请求方法不被允许'
            ], JSON_UNESCAPED_UNICODE);

            //阻止异常冒泡
            $this->stopPropagation();
            return $response->withStatus($throwable->getStatusCode())->withBody(new SwooleStream($errorJsonData));
        }

        //判断是否是路由存在
        if ($throwable instanceof  NotFoundHttpException) {
            $errorJsonData = json_encode([
                'code' => $throwable->getStatusCode(),
                'message' => '路由不存在'
            ], JSON_UNESCAPED_UNICODE);

            //阻止异常冒泡
            $this->stopPropagation();
            return $response->withStatus($throwable->getStatusCode())->withBody(new SwooleStream($errorJsonData));
        }

        // 交给下一个异常处理器
        return $response;
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
