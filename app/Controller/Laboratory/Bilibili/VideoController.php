<?php

declare (strict_types=1);
namespace App\Controller\Laboratory\Bilibili;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Queue;
use App\Job\Bilibili\VideoInfoRecordJob;
use App\Model\Laboratory\Bilibili\Video;
use App\Model\Laboratory\Bilibili\VideoReport;
use App\Service\Laboratory\Bilibili\UpUserService;
use App\Service\Laboratory\Bilibili\VideoService;
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
 */
#[Controller(prefix: 'laboratory/bilibili_module/video')]
class VideoController extends AbstractController
{
    #[Inject]
    private Video $video;
    
    #[Inject]
    private VideoReport $videoReport;
    
    #[Inject]
    private Queue $queue;

    /**
     * 添加视频
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '添加视频')]
    #[RequestMapping(path: 'video_add', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
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
            if (empty($bvid)) {
                continue;
            }
            $video = new Video();
            $video->bvid = $bvid;
            $video->timed_status = $timedStatus;
            $video->save();
            $addBVidArr[] = $bvid;
        }
        //推送一个队列，异步获取Up主信息
        $this->queue->push(new VideoInfoRecordJob(['video_bvid' => $addBVidArr]));
        if ($isExistEmptyUrl) {
            return $this->successByMessage('录入视频成功，部分Url条目为空录入失败');
        }
        return $this->successByMessage('录入视频成功');
    }

    /**
     * 视频标题搜索
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'video_title_search', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function videoTitleSearch()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $videoQuery = $this->video->newQuery();
        if (!empty($bvid)) {
            $videoQuery->where('bvid', $bvid);
        }
        $list = $videoQuery->limit(10)->orderBy('public_time', 'desc')->get()->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['title'] = $value['title'] . '(' . $value['bvid'] . ')';
        }
        return $this->success(['list' => $list]);
    }

    /**
     * 视频列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'video', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function videoList()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $mid = $this->request->input('mid') ?? '';
        $title = $this->request->input('title') ?? '';
        $publicTime = $this->request->input('public_time') ?? '';
        $videoQuery = $this->video->newQuery();
        if (!empty($bvid)) {
            $videoQuery->where('bvid', $mid);
        }
        if (!empty($mid)) {
            $videoQuery->where('mid', $mid);
        }
        if (!empty($publicTime)) {
            $videoQuery->whereBetween('public_time', [strtotime($publicTime[0]), strtotime($publicTime[1])]);
        }
        if (!empty($title)) {
            $videoQuery->where('title', 'like', '%' . $title . '%');
        }
        $total = $videoQuery->count();
        $this->pagingCondition($videoQuery, $this->request->all());
        $list = $videoQuery->orderBy('created_at', 'desc')->get()->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['public_time'] = date('Y-m-d H:i:s', $value['public_time']);
            $owner = json_decode($value['owner'], true);
            $list[$key]['name'] = $owner['name'];
            $list[$key]['duration'] = floor($value['duration'] / 60) . '分' . $value['duration'] % 60 . '秒';
        }
        return $this->success(['list' => $list, 'total' => $total]);
    }

    /**
     * 视频趋势图表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'video_chart_trend', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function videoChartTrend()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $date = $this->request->input('date') ?? '';
        $videoReportQuery = $this->videoReport->newQuery();
        if (empty($bvid)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, '请填写搜索视频ID');
        }
        $videoReportQuery->where('bvid', $bvid);
        // 处理时间
        $date = $date ?? [date('Y-m-d', strtotime('-6 days')), date('Y-m-d', time())];
        $beginTime = strtotime($date[0]);
        $endTime = strtotime($date[1]);
        $range = getRangeBetweenTime($beginTime, $endTime);
        if ($range > 7) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '时间范围不能超过7天');
        }
        $timestampList = [];
        for ($i = $beginTime; $i < $endTime; $i = $i + 3600) {
            $timestampList[] = $i;
        }
        $videoReportQuery->where('time', '>=', $beginTime);
        $videoReportQuery->where('time', '<=', $endTime);
        $rows = VideoService::getInstance()->videoChartTrend($videoReportQuery, $timestampList);
        return $this->success(['rows' => $rows]);
    }

    /**
     * Up视频数据报表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'video_data_report', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function upUserDataReport()
    {
        $bvid = $this->request->input('bvid') ?? '';
        $date = $this->request->input('date') ?? '';
        $videoReportQuery = $this->videoReport->newQuery();
        if (empty($bvid)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, '请填写视频ID');
        }
        $videoReportQuery->where('bvid', $bvid);
        // 处理时间
        $date = empty($date) ? [date('Y-m-d', time()), date('Y-m-d', time())] : $date;
        $beginTime = strtotime($date[0]);
        $endTime = strtotime($date[1]) + 86400;
        $videoReportQuery->where('time', '>=', $beginTime);
        $videoReportQuery->where('time', '<=', $endTime);
        $total = $videoReportQuery->count();
        $videoReportQuery = $this->pagingCondition($videoReportQuery, $this->request->all());
        $list = VideoService::getInstance()->videoDataReport($videoReportQuery);
        return $this->success(['list' => $list, 'total' => $total]);
    }
}