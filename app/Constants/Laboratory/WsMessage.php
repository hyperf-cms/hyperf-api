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

namespace App\Constants\Laboratory;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Class WsMessage
 * 聊天系统相关枚举
 * @Constants
 * @package App\Constants
 * @Author YiYuan-Lin
 * @Date: 2021/3/12
 */
class WsMessage extends AbstractConstants
{
    /**
     * @Message("信息类型为初始化")
     */
    const MESSAGE_TYPE_INIT = 'init';

    /**
     * @Message("拉取好友信息")
     */
    const MESSAGE_TYPE_PULL_FRIEND_MESSAGE = 'friend_history_message';

}
