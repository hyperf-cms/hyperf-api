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

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Class StatusCode
 * 错误码枚举类
 * @Constants
 * @package App\Constants
 * @Author YiYuan-Lin
 * @Date: 2020/9/18
 * 自定义业务代码规范如下：
 * 授权相关，1001……
 * 用户相关，2001……
 * 业务相关，3001……
 */
class StatusCode extends AbstractConstants
{
    /**
     * @Message("ok")
     */
    const SUCCESS = 200;

    /**
     * @Message("Internal Server Error!")
     */
    const ERR_SERVER = 500;

    /**
     * @Message("无权限访问！")
     */
    const ERR_NOT_ACCESS = 1001;

    /**
     * @Message("令牌过期！")
     */
    const ERR_EXPIRE_TOKEN = 1002;

    /**
     * @Message("令牌无效！")
     */
    const ERR_INVALID_TOKEN = 1003;

    /**
     * @Message("令牌不存在！")
     */
    const ERR_NOT_EXIST_TOKEN = 1004;



    /**
     * @Message("请登录！")
     */
    const ERR_NOT_LOGIN = 2001;

    /**
     * @Message("用户信息错误！")
     */
    const ERR_USER_INFO = 2002;

    /**
     * @Message("用户不存在！")
     */
    const ERR_USER_ABSENT = 2003;


    /**
     * @Message("业务逻辑异常！")
     */
    const ERR_EXCEPTION = 3001;

    /**
     * 用户相关逻辑异常
     * @Message("用户密码不正确！")
     */
    const ERR_EXCEPTION_USER = 3002;

    /**
     * 文件上传
     * @Message("文件上传异常！")
     */
    const ERR_EXCEPTION_UPLOAD = 3003;


}
