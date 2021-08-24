<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Bilibili;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Queue;
use App\Job\Bilibili\VideoInfoRecordJob;
use App\Model\Laboratory\Bilibili\Video;
use App\Model\Laboratory\Bilibili\VideoReport;
use App\Service\Laboratory\Bilibili\UpUserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * Bilibili视频
 * Class VideoController
 * @Controller(prefix="laboratory/bilibili_module/video")
 */
class VideoController extends AbstractController
{
    /**
     * @Inject()
     * @var Video
     */
    private $video;

    /**
     * @Inject()
     * @var VideoReport
     */
    private $videoReport;

    /**
     * @Inject()
     * @var Queue
     */
    private $queue;

    /**
     * @Explanation(content="视频录入")
     * @RequestMapping(path="video_add", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \Exception
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function videoAdd()
    {
        $videoInfo = $this->request->all()['video_info'] ?? [];
        if (empty($videoInfo)) $this->throwExp(StatusCode::ERR_VALIDATION, '视频链接信息不能为空');

        //是否存在空的URL
        $isExistEmptyUrl = false;
        $addBVidArr = [];
        foreach ($videoInfo as $video) {
            $videoUrl = $video['video_url'] ?? '';
            $timedStatus = $video['timed_status'] ?? '';
            if (empty($videoUrl)) {
                $isExistEmptyUrl = true;
                continue;
            }
            $lastString = basename($videoUrl);
            $bvid = explode('?', $lastString)[0] ?? '';
            if (empty($bvid)) continue;

            $video = new Video();
            $video->bvid = $bvid;
            $video->timed_status = $timedStatus;
            $video->save();
            $addBVidArr[] = $bvid;
        }
        //推送一个队列，异步获取Up主信息
        $this->queue->push(new VideoInfoRecordJob([
            'video_bvid' => $addBVidArr,
        ]));

        if ($isExistEmptyUrl) return $this->successByMessage('录入视频成功，部分Url条目为空录入失败');

        return $this->successByMessage('录入视频成功');
    }

    /**
     * 获取视频标题搜索列表
     * @RequestMapping(path="video_title_search", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function videoTitleSearch()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $videoQuery = $this->video->newQuery();
        if (!empty($bvid)) $videoQuery->where('bvid', $bvid);

        $list = $videoQuery->limit(10)->orderBy('created_at')->get()->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['title'] = $value['title'] . '(' . $value['bvid'] . ')';
        }
        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * 获取Up用户列表
     * @RequestMapping(path="video", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function videoList()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $mid =  $this->request->input('mid') ?? '';
        $title =  $this->request->input('title') ?? '';
        $publicTime = $this->request->input('public_time') ?? '';

        $videoQuery = $this->video->newQuery();
        if (!empty($bvid)) $videoQuery->where('bvid', $mid);
        if (!empty($mid)) $videoQuery->where('mid', $mid);
        if (!empty($publicTime)) $videoQuery->whereBetween('public_time', [strtotime($publicTime[0]), strtotime($publicTime[1])]);
        if (!empty($title)) $videoQuery->where('title', 'like', '%' . $title . '%');

        $total = $videoQuery->count();
        $this->pagingCondition($videoQuery, $this->request->all());
        $list = $videoQuery->get()->toArray();

        return $this->success([
            'list' => $list,
            'total' => $total
        ]);
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
        $endTime = strtotime($date[1]) + 86400;
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
        $date = $date ?? [date('Y-m-d', strtotime('-6 days')), date('Y-m-d', time())];
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
