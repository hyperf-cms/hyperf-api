<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use App\Constants\StatusCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

/**
 * BusinessException
 * 业务异常处理类
 * @package App\Exception
 * User：YM
 * Date：2019/11/15
 * Time：下午4:24
 */
class BusinessException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = StatusCode::getMessage($code);
        }

        parent::__construct($message, $code, $previous);
    }
}
