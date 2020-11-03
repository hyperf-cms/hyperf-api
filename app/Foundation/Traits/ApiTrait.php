<?php
namespace App\Foundation\Traits;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Illuminate\Support\Facades\Validator;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Throwable;

trait ApiTrait
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function success($data = [], $message = '')
    {
        return $this->response->json($this->formatResponse($data, $message));
    }

    /**
     * 成功响应消息
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function successByMessage($message = '')
    {
        return $this->response->json($this->formatResponse([], $message));
    }


    /**
     * 错误响应
     * @param int $statusCode
     * @param string|null $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function error(int $statusCode = StatusCode::ERR_EXCEPTION, string $message = null)
    {
        $message = $message ?? StatusCode::ERR_EXCEPTION;
        return $this->response->json($this->formatResponse([], $message, $statusCode));
    }

    /**
     * 抛出业务错误异常
     * @param int $code
     * @param string $message
     */
    public function throwExp(int $code =  StatusCode::ERR_EXCEPTION, string $message = '')
    {
        Throw new BusinessException($code, $message);
    }

    /**
     * 监听错误异常响应错误信息
     * @param Throwable $throwable
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function errorExp(Throwable $throwable)
    {
        if (!$throwable->getCode()) {
            $code = StatusCode::ERR_SERVER;
            $message = '服务器错误 ' . $throwable->getMessage() . ':: FILE:' . $throwable->getFile() . ':: LINE: ' . $throwable->getLine();
        } else {
            $code = $throwable->getCode();
            $message = $throwable->getMessage();
        }
        return $this->error($code, $message);
    }

    /**
     * 格式化API的响应数据
     * @param array  $data  返回数据
     * @param int    $statusCode  错误码
     * @param string $message 错误信息
     * @return array
     */
    protected function formatResponse(array $data = [], string $message = 'Success', int $statusCode = StatusCode::SUCCESS) : array
    {
        $return['code'] = $statusCode;
        $return['msg'] = $message;
        $return['data'] = $data;

        //记录请求参数日志记录
        if (config('response_log')) Log::responseLog()->info('返回参数：' . json_encode($return));
        return $return;
    }
}
