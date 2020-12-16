<?php
namespace App\Http\Service\Auth;

use App\Constants\StatusCode;
use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;
use App\Model\Auth\User;
use App\Model\System\OperateLog;
use Hyperf\Di\Annotation\Inject;
use phpDocumentor\Reflection\Types\Integer;

/**
 * 操作日志服务类
 * Class OperateLogService
 * @package App\Http\Service\Auth
 * @Author YiYuan-Lin
 * @Date: 2020/12/16
 */
class OperateLogService extends BaseService
{
    use Singleton;


    /**
     * 添加操作日志
     * @param integer [用户ID] $uid
     * @param string [操作] $action
     * @param string [返回数据] $data
     * @param string [处理结果] $dealResult
     * @return bool
     */
   public function add(Integer $uid, string $action = '', string $data = '', string $dealResult = '') : bool
   {
       if (empty($uid)) return false;
       if (empty($action)) return false;

       //获取操作用户信息
       $userInfo = User::getOneByUid($uid);

       $username = $userInfo->username;
       $operator = $userInfo->desc;

       //初始化日志对象
       $operatorLog = new OperateLog();
       $operatorLog->action    = $action;
       $operatorLog->data      = $data;
       $operatorLog->uid       = $uid;
       $operatorLog->username  = $username;
       $operatorLog->operator  = $operator;
       $operatorLog->dealResult = $dealResult;

       $operatorLog->save();
       return true;
   }
}
