<?php

declare (strict_types=1);
namespace App\Controller\Setting;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Cron;
use App\Model\Auth\User;
use App\Model\Laboratory\FriendRelation;
use App\Model\Setting\TimedTask;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
/**
 * 定时任务管理器
 * Class TimedTaskController
 */
#[Controller(prefix: 'setting/monitoring_module/timed_task')]
class TimedTaskController extends AbstractController
{
    #[Inject]
    private TimedTask $timedTask;

    /**
     * 列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $timedTaskQuery = $this->timedTask->newQuery();
        $status = $this->request->input('status') ?? '';
        $name = $this->request->input('name') ?? '';
        $task = $this->request->input('task') ?? '';
        if (strlen($status) > 0) {
            $timedTaskQuery->where('status', $status);
        }
        if (!empty($name)) {
            $timedTaskQuery->where('name', 'like', '%' . $name . '%');
        }
        if (!empty($task)) {
            $timedTaskQuery->where('task', 'like', '%' . $task . '%');
        }
        $total = $timedTaskQuery->count();
        $timedTaskQuery = $this->pagingCondition($timedTaskQuery, $this->request->all());
        $data = $timedTaskQuery->get();
        return $this->success(['list' => $data, 'total' => $total]);
    }

    /**
     * 添加定时任务
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    #[Explanation(content: '添加定时任务')]
    #[RequestMapping(path: 'store', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function store()
    {
        $postData = $this->request->all();
        $params = ['name' => $postData['name'] ?? '', 'params' => $postData['params'] ?? '', 'task' => $postData['task'] ?? '', 'execute_time' => $postData['execute_time'] ?? '', 'status' => $postData['status'] ?? '', 'desc' => $postData['desc'] ?? ''];
        //配置验证
        $rules = ['name' => 'required', 'task' => 'required', 'execute_time' => 'required'];
        //错误信息
        $message = ['name.required' => '[name]缺失', 'task.required' => '[task]缺失', 'execute_time.required' => '[execute_time]缺失'];
        $this->verifyParams($params, $rules, $message);
        $timedTaskQuery = new TimedTask();
        $timedTaskQuery->name = $params['name'];
        $timedTaskQuery->params = json_encode($params['params']);
        $timedTaskQuery->task = $params['task'];
        $timedTaskQuery->execute_time = $params['execute_time'];
        $timedTaskQuery->status = $params['status'];
        $timedTaskQuery->desc = $params['desc'];
        $timedTaskQuery->times = 0;
        $executeTime = $params['execute_time'] ?? '';
        $nextExecuteTime = Cron::init($executeTime)->getNextRunDate()->format('Y-m-d H:i');
        $timedTaskQuery->next_execute_time = $nextExecuteTime;
        if (!$timedTaskQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '添加定时任务错误');
        }
        return $this->successByMessage('添加定时任务成功');
    }

    /**
     * 获取编辑选项
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'edit/{id}', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function edit(int $id)
    {
        $timedTaskInfo = TimedTask::findById($id);
        $timedTaskInfo['params'] = json_decode($timedTaskInfo['params'], true);
        if (empty($timedTaskInfo)) {
            $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取定时任务失败');
        }
        return $this->success(['list' => $timedTaskInfo]);
    }

    /**
     * 更新定时任务状态
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    #[Explanation(content: '更新定时任务状态')]
    #[RequestMapping(path: 'change_status/{id}', methods: array('PUT'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function changeStatus(int $id)
    {
        $status = $this->request->input('status');
        if ($status != 0 && empty($status)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, '状态参数为空');
        }
        $timedTaskInfo = TimedTask::findById($id);
        if (empty($timedTaskInfo)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, '查询不到该任务');
        }
        $executeTime = $timedTaskInfo['execute_time'] ?? '';
        $nextExecuteTime = Cron::init($executeTime)->getNextRunDate()->format('Y-m-d H:i');
        //修改状态以及下次执行时间
        TimedTask::query()->where('id', $id)->update(['status' => $status, 'next_execute_time' => $nextExecuteTime]);
        return $this->successByMessage('修改状态成功');
    }

    /**
     * 编辑
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    #[Explanation(content: '编辑定时任务')]
    #[RequestMapping(path: 'update/{id}', methods: array('PUT'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function update(int $id)
    {
        if (empty($id)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        }
        $postData = $this->request->all();
        $params = ['name' => $postData['name'] ?? '', 'params' => $postData['params'] ?? '', 'task' => $postData['task'] ?? '', 'execute_time' => $postData['execute_time'] ?? '', 'status' => $postData['status'] ?? '', 'desc' => $postData['desc'] ?? ''];
        //配置验证
        $rules = ['name' => 'required', 'task' => 'required', 'execute_time' => 'required'];
        //错误信息
        $message = ['name.required' => '[name]缺失', 'task.required' => '[task]缺失', 'execute_time.required' => '[execute_time]缺失'];
        $this->verifyParams($params, $rules, $message);
        $timedTaskQuery = TimedTask::findById($id);
        $timedTaskQuery->name = $params['name'];
        $timedTaskQuery->params = json_encode($params['params']);
        $timedTaskQuery->task = $params['task'];
        $timedTaskQuery->execute_time = $params['execute_time'];
        $timedTaskQuery->desc = $params['desc'];
        $executeTime = $params['execute_time'] ?? '';
        $nextExecuteTime = Cron::init($executeTime)->getNextRunDate()->format('Y-m-d H:i');
        $timedTaskQuery->next_execute_time = $nextExecuteTime;
        if (!$timedTaskQuery->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '修改定时任务错误');
        }
        return $this->successByMessage('修改定时任务成功');
    }

    /**
     * 删除
     * @Author YiYuan
     * @Date 2023/12/4
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '删除定时任务')]
    #[RequestMapping(path: 'destroy/{id}', methods: array('DELETE'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function destroy(int $id)
    {
        if ($id == 0) {
            $idArr = $this->request->input('id') ?? [];
            if (empty($idArr) || !is_array($idArr)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数类型不正确');
            }
            if (!TimedTask::whereIn('id', $idArr)->delete()) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        } else {
            if (!intval($id)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
            }
            if (!TimedTask::destroy($id)) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        }
        return $this->successByMessage('删除定时任务成功');
    }
}