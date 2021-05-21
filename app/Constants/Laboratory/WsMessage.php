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
     * @Message("用户上线")
     */
    const FRIEND_ONLINE_MESSAGE = 'friend_online_message';

    /**
     * @Message("用户下线")
     */
    const FRIEND_OFFLINE_MESSAGE = 'friend_offline_message';

    /**
     * @Message("拉取好友信息")
     */
    const MESSAGE_TYPE_PULL_FRIEND_MESSAGE = 'friend_history_message';

    /**
     * @Message("拉取组信息")
     */
    const MESSAGE_TYPE_PULL_GROUP_MESSAGE = 'group_history_message';

    /**
     * @Message("好友撤回信息")
     */
    const MESSAGE_TYPE_FRIEND_WITHDRAW_MESSAGE = 'friend_withdraw_message';

    /**
     * @Message("群聊撤回消息")
     */
    const MESSAGE_TYPE_GROUP_WITHDRAW_MESSAGE = 'group_withdraw_message';
}
