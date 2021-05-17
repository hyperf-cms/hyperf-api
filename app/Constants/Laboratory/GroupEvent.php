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
 * 组消息事件枚举
 * Class GroupEvent
 * @Constants
 * @package App\Constants\Laboratory
 * @Author YiYuan-Lin
 * @Date: 2021/5/8
 */
class GroupEvent extends AbstractConstants
{
    /**
     * 创建组事件
     */
    const CREATE_GROUP_EVENT = 'create_group';

    /**
     * 修改群组操作
     */
    const EDIT_GROUP_EVENT = 'edit_group';


    /**
     * 新加入组员事件
     */
    const NEW_MEMBER_JOIN_GROUP_EVENT = 'new_member_join_group';

    /**
     * 组员退群事件
     */
    const GROUP_MEMBER_EXIT = 'group_member_exit';

}
