<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Exception\Handler;

use App\Exception\Handler\BusinessException;
use App\Foundation\Traits\ApiTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\Exception\InvalidConfigException;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Throwable;

/**
 * 应用异常处理中心
 * Class AppExceptionHandler
 * @package App\Exception\Handler
 * @author YiYuan-Lin
 * @date: 2020/9/18
 */
class AppExceptionHandler extends ExceptionHandler
{
    use ApiTrait;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $message = '服务器错误 ' . $throwable->getMessage() . ':: FILE:' . $throwable->getFile() . ':: LINE: ' . $throwable->getLine();

        if ($throwable instanceof TokenValidException) {
            // 阻止异常冒泡
            $this->stopPropagation();
            return $this->error($throwable->getCode(), $throwable->getMessage(), false);
        }

        if ($throwable instanceof JWTException) {
            // 阻止异常冒泡
            $this->stopPropagation();
            return $this->error($throwable->getCode(), $throwable->getMessage(), false);
        }

        // 判断是否由业务异常类抛出的异常
        if ($throwable instanceof BusinessException) {
            // 阻止异常冒泡
            $this->stopPropagation();
            return $this->error($throwable->getCode(), $throwable->getMessage());
        }
        return $this->error(500, $message);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
