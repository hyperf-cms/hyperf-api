<?php
namespace App\Foundation\Traits;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use App\Service\System\LoginLogService;
use App\Service\System\OperateLogService;
use App\Model\System\LoginLog;
use App\Model\System\OperateLog;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

trait ApiTrait
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function success(array $data = [], string $message = '')
    {
        return $this->response->json($this->formatResponse($data, $message));
    }

    /**
     * 成功响应消息
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function successByMessage(string $message = '')
    {
        return $this->response->json($this->formatResponse([], $message));
    }


    /**
     * 错误响应
     * @param int $statusCode
     * @param string|null $message
     * @param bool $isRecordLog
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function error(int $statusCode = StatusCode::ERR_EXCEPTION, string $message = null, bool $isRecordLog = true)
    {
        $message = $message ?? StatusCode::ERR_EXCEPTION;

        $targetUrl = $this->request->getUri()->getPath();

        if ($targetUrl == '/auth/login') {
            //记录登陆异常日志
            $loginLogData = LoginLogService::getInstance()->collectLoginLogInfo();
            $loginLogData['response_code'] = $statusCode;
            $loginLogData['response_result'] = $message;
            LoginLog::add($loginLogData);
        }else if ($isRecordLog) {
            //记录操作异常日志
            $logData = OperateLogService::getInstance()->collectLogInfo();
            if(!empty($logData)) {
                $logData['response_result'] = $message;
                $logData['response_code'] = $statusCode;
                if (!empty($logData['action'])) OperateLog::add($logData);
            }
        }
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
