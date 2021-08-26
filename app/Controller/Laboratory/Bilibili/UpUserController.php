<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Bilibili;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Queue;
use App\Job\Bilibili\SyncVideoFromUpUserJob;
use App\Job\Bilibili\UpUserInfoRecordJob;
use App\Model\Laboratory\Bilibili\UpUser;
use App\Model\Laboratory\Bilibili\UpUserReport;
use App\Service\Laboratory\Bilibili\UpUserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * Up主
 * Class UpUserController
 * @Controller(prefix="laboratory/bilibili_module/up_user")
 */
class UpUserController extends AbstractController
{
    /**
     * @Inject()
     * @var UpUser
     */
    private $upUser;

    /**
     * @Inject()
     * @var UpUserReport
     */
    private $upUserReport;

    /**
     * @Inject()
     * @var Queue
     */
    private $queue;

    /**
     * @Explanation(content="录入Up主")
     * @RequestMapping(path="up_user_add", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \Exception
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upUserAdd()
    {
        $upUserInfo = $this->request->all()['up_user_info'] ?? [];
        if (empty($upUserInfo)) $this->throwExp(StatusCode::ERR_VALIDATION, 'Up主信息不能为空');

        //是否存在空的upUserUrl
        $isExistEmptyUrl = false;
        $addMidArr = [];
        foreach ($upUserInfo as $upUser) {
            $upUserUrl = $upUser['up_user_url'] ?? '';
            $timedStatus = $upUser['timed_status'] ?? '';
            if (empty($upUserUrl)) {
                $isExistEmptyUrl = true;
                continue;
            }
            $lastString = basename($upUserUrl);
            $mid = explode('?', $lastString)[0] ?? '';
            if (empty($mid)) continue;

            $upUser = new UpUser();
            $upUser->mid = $mid;
            $upUser->timed_status = $timedStatus;
            $upUser->save();
            $addMidArr[] = $mid;
        }
        //推送一个队列，异步获取Up主信息
        $this->queue->push(new UpUserInfoRecordJob([
            'up_user_mid' => $addMidArr,
        ]));

        if ($isExistEmptyUrl) return $this->successByMessage('录入Up主成功，部分Url条目为空录入失败');

        return $this->successByMessage('录入Up主成功');
    }

    /**
     * 获取Up用户搜索列表
     * @RequestMapping(path="up_user_search", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upUserSearchList()
    {
        $mid = $this->request->input('mid') ?? '';
        $upUserQuery = $this->upUser->newQuery();
        if (!empty($mid)) $upUserQuery->where('mid', $mid);

        $list = $upUserQuery->limit(10)->orderBy('created_at')->get()->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['name'] = $value['name'] . '(' . $value['mid'] . ')';
        }
        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * 获取Up用户列表
     * @RequestMapping(path="up_user", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upUserList()
    {
        $mid = $this->request->input('mid') ?? '';
        $name =  $this->request->input('name') ?? '';
        $timedStatus =  $this->request->input('time_status') ?? '';

        $upUserQuery = $this->upUser->newQuery();
        if (!empty($mid)) $upUserQuery->where('mid', $mid);
        if (!empty($name)) $upUserQuery->where('name', 'like', '%' . $name . '%');
        if (strlen($timedStatus) > 0) $upUserQuery->where('timed_status', $timedStatus);

        $total = $upUserQuery->count();
        $this->pagingCondition($upUserQuery, $this->request->all());
        $list = $upUserQuery->orderBy('created_at', 'desc')->get()->toArray();

        return $this->success([
            'list' => $list,
            'total' => $total
        ]);
    }

    /**
     * 获取Up用户列表
     * @RequestMapping(path="sync_video_from_up_user", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function syncVideoReportFromUpUser()
    {
        $mid = $this->request->input('mid') ?? '';
        if (empty($mid)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');

        $upUser = $this->upUser->newQuery()->where('mid', $mid)->first();
        if (empty($upUser)) $this->throwExp(StatusCode::ERR_EXCEPTION, '查询不到该Up主');

        //推送一个队列，同步Up主视频信息
        $this->queue->push(new SyncVideoFromUpUserJob([
            'mid' => $mid,
        ]));

        return $this->successByMessage('正在同步视频中。。。请稍后转至视频列表查看');
    }

    /**
     * up主图表趋势
     * @RequestMapping(path="up_user_chart_trend", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upUserChartTrend()
    {
        $mid = $this->request->input('mid') ?? '';
        $date = $this->request->input('date') ?? '';

        $upUserReportQuery = $this->upUserReport->newQuery();
        if (empty($mid)) $this->throwExp(StatusCode::ERR_VALIDATION, '请填写搜索UP主mid');
        if (!empty($mid)) $upUserReportQuery->where('mid', $mid);
        // 处理时间
        $date = $date ?? [date('Y-m-d', strtotime('-6 days')), date('Y-m-d', time())];
        $beginTime = strtotime($date[0]);
        $endTime = strtotime($date[1]);
        $range = getRangeBetweenTime($beginTime, $endTime);
        if ($range > 7) $this->throwExp(StatusCode::ERR_EXCEPTION, '时间范围不能超过7天');
        $timestampList = [];
        for ($i = $beginTime; $i < $endTime; $i = $i + 3600) {
            $timestampList[] = $i;
        }
        $upUserReportQuery->where('time', '>=', $beginTime);
        $upUserReportQuery->where('time', '<=', $endTime);

        $rows = UpUserService::getInstance()->upUserChartTrend($upUserReportQuery, $timestampList);

        return $this->success([
            'rows' => $rows,
        ]);
    }

    /**
     * up主图表趋势
     * @RequestMapping(path="up_user_data_report", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upUserDataReport()
    {
        $mid = $this->request->input('mid') ?? '';
        $date = $this->request->input('date') ?? '';

        $upUserReportQuery = $this->upUserReport->newQuery();
        if (empty($mid)) $this->throwExp(StatusCode::ERR_VALIDATION, '请填写搜索UP主mid');
        if (!empty($mid)) $upUserReportQuery->where('mid', $mid);
        // 处理时间
        $date = empty($date) ? [date('Y-m-d', time()), date('Y-m-d', time())] : $date;
        $beginTime = strtotime($date[0]);
        $endTime = strtotime($date[1]) + 86400;

        $upUserReportQuery->where('time', '>=', $beginTime);
        $upUserReportQuery->where('time', '<=', $endTime);

        $total = $upUserReportQuery->count();
        $upUserReportQuery = $this->pagingCondition($upUserReportQuery, $this->request->all());

        $list = UpUserService::getInstance()->upUserDataReport($upUserReportQuery);
        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }
}
