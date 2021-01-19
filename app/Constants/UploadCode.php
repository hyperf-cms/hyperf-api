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
 * Class UploadCode
 * 上传相关错误码
 * @Constants
 * @package App\Constants
 * @Author YiYuan-Lin
 * @Date: 2021/1/18
 * 自定义业务代码规范如下：
 * 上传相关，4001……
 */
class UploadCode extends AbstractConstants
{
    /**
     * @Message("上传文件类型不正确")
     */
    const ERR_UPLOAD_TYPE = 4001;

    /**
     * @Message("上传文件尺寸不正确")
     */
    const ERR_UPLOAD_SIZE = 4002;

}

