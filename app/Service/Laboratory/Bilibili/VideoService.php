<?php

namespace App\Service\Laboratory\Bilibili;

use App\Foundation\Facades\Log;
use App\Foundation\Traits\Singleton;
use App\Model\Laboratory\Bilibili\Video;
use App\Service\BaseService;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

class VideoService extends BaseService
{
    use Singleton;

    /**
     * 获取视频AV号（内含视频基本信息，若此BV对应视频属于系列视频，API会列出所有系列视频）
     * @var string
     */
    private $videoInfoApi = 'https://api.bilibili.com/x/web-interface/view?bvid=';

    /**
     * 根据用户MID获取该用户下所有视频数据
     * @var
     */
    private $videoInfoFromMidApi = 'https://api.bilibili.com/x/space/arc/search?mid=';

    /**
     * 更新视频数据
     * @param array $videoBVid
     * @return bool
     * @throws \Exception
     */
    public function recordVideoInfoFromBilibili(array $videoBVid): bool
    {
        go(function () use ($videoBVid) {
            if (empty($videoBVid)) return false;

            foreach ($videoBVid as $bvid) {
                $videoInfo = $this->getVideoInfoFromBilibili($bvid);
                if (!empty($videoInfo)) {
                    $updateData['mid'] = $videoInfo['mid'] ?? '';
                    $updateData['cover'] = $videoInfo['cover'] ?? '';
                    $updateData['title'] = $videoInfo['title'] ?? '';
                    $updateData['public_time'] = $videoInfo['public_time'] ?? '';
                    $updateData['desc'] = $videoInfo['desc'] ?? '';
                    $updateData['duration'] = $videoInfo['duration'] ?? '';
                    $updateData['view'] = $videoInfo['view'] ?? 0;
                    $updateData['danmaku'] = $videoInfo['danmaku'] ?? 0;
                    $updateData['reply'] = $videoInfo['reply'] ?? 0;
                    $updateData['favorite'] = $videoInfo['favorite'] ?? 0;
                    $updateData['coin'] = $videoInfo['coin'] ?? 0;
                    $updateData['likes'] = $videoInfo['likes'] ?? 0;
                    $updateData['dislike'] = $videoInfo['dislike'] ?? 0;
                    $updateData['owner'] = !empty($videoInfo['owner']) ? json_encode($videoInfo['owner']) : '';
                    $updateData['updated_at'] = date('Y-m-d H:i:s');
                    Video::where('bvid', $bvid)->update($updateData);
                }
            }
            return true;
        });
        return true;
    }

    /**
     * 根据BVid从Bilibili获取视频数据
     * @param string $videoBVid
     * @return array
     * @throws \Exception
     */
    public function getVideoInfoFromBilibili(string $videoBVid): array
    {
        if (empty($videoBVid)) return [];
        $videoInfo = curl_get($this->videoInfoApi . $videoBVid);

        return [
            'bvid'        => $videoInfo['data']['bvid'] ?? '',
            'mid'         => $videoInfo['data']['owner']['mid'] ?? '',
            'owner'       => $videoInfo['data']['owner'] ?? [],
            'cover'       => $videoInfo['data']['pic'] ?? '',
            'title'       => $videoInfo['data']['title'] ?? '',
            'public_time' => $videoInfo['data']['pubdate'] ?? 0,
            'desc'        => $videoInfo['data']['desc'] ?? '',
            'duration'    => $videoInfo['data']['duration'] ?? 0,
            'view'        => $videoInfo['data']['stat']['view'] ?? 0,
            'danmaku'     => $videoInfo['data']['stat']['danmaku'] ?? 0,
            'reply'       => $videoInfo['data']['stat']['reply'] ?? 0,
            'favorite'    => $videoInfo['data']['stat']['favorite'] ?? 0,
            'coin'        => $videoInfo['data']['stat']['coin'] ?? 0,
            'likes'       => $videoInfo['data']['stat']['like'] ?? 0,
            'dislike'     => $videoInfo['data']['stat']['dislike'] ?? 0,
        ];
    }

    /**
     * 根据Up主Id获取视频列表
     * @param string $mid
     * @return array
     * @throws \Exception
     */
    public function getVideoInfoFromUpUser(string $mid): array
    {
        if (empty($mid)) return [];
        $videoList = [];
        //第一次获取视频数据
        $videoInfo = curl_get($this->videoInfoFromMidApi . $mid . '&pn=1&&ps=30');

        if (!empty($videoInfo['data']['list']['vlist'])) {
            $videoList = array_merge($videoList, $videoInfo['data']['list']['vlist']);
            $pageInfo = $videoInfo['data']['page'];

            if ($pageInfo['count'] > 30) {
                for ($i = 2; $i <= ceil($pageInfo['count'] / $pageInfo['ps']); $i++) {
                    $temp = curl_get($this->videoInfoFromMidApi . $mid . '&pn=' . $i . '&&ps=30');
                    $videoList = array_merge($videoList, $temp['data']['list']['vlist']);
                }
            }
        }

        return $videoList;
    }

    /**
     * 视频数据趋势图表
     * @param Builder $query
     * @param array $timestampList
     * @return array
     */
    public function videoChartTrend(Builder $query, array $timestampList = []): array
    {
        $query->orderBy('time');
        $videoReport = $query->get([
            'time',
            'view',
            'danmaku',
            'reply',
            'favorite',
            'coin',
            'likes',
            'dislike'
        ])->toArray();
        $minVideoReport = $query->select(Db::raw(
            'min(view) as view, 
                   min(danmaku) as danmaku,
                   min(reply) as reply,
                   min(favorite) as favorite,
                   min(coin) as coin,
                   min(likes) as likes,
                   min(dislike) as dislike
        '))->first()->toArray();
        $videoReport = array_column($videoReport, null, 'time');

        $rows = [];
        $list = [];
        foreach ($timestampList as $ts) {
            $dataDate = date('Y-m-d', $ts);
            if (!empty($videoReport[$ts]['view'])) $list['view'][$dataDate][$ts] = intval($videoReport[$ts]['view']);
            if (!empty($videoReport[$ts]['likes'])) $list['likes'][$dataDate][$ts] = intval($videoReport[$ts]['likes']);
            if (!empty($videoReport[$ts]['favorite'])) $list['favorite'][$dataDate][$ts] = intval($videoReport[$ts]['favorite']);
            if (!empty($videoReport[$ts]['coin'])) $list['coin'][$dataDate][$ts] = intval($videoReport[$ts]['coin']);
            if (!empty($videoReport[$ts]['danmaku'])) $list['danmaku'][$dataDate][$ts] = intval($videoReport[$ts]['danmaku']);
            if (!empty($videoReport[$ts]['reply'])) $list['reply'][$dataDate][$ts] = intval($videoReport[$ts]['reply']);
            if (!empty($videoReport[$ts]['dislike'])) $list['dislike'][$dataDate][$ts] = intval($videoReport[$ts]['dislike']);
        }

        foreach ($list as $key => $value) {
            $rows[$key]['columns'] = ['time'];
            for ($i = 0; $i < 24; $i++) {
                $temp = [];
                foreach ($value as $k => $v) {
                    $temp['time'] = $i;
                    //如果某个时间点数据为空，则拿其上个时间点数据作为补充
                    $temp[$k] = $value[$k][strtotime($k) + ($i * 3600)] ?? '';
                    if ($i == 0) {
                        $rows[$key]['columns'][] = $k;
                    }
                }
                $rows[$key]['rows'][] = $temp;
            }
        }
        $rows['view']['label'] = '视频播放数';
        $rows['view']['desc'] = '截止到当前时间（小时），时间范围内的视频播放数变化趋势对比。';
        $rows['view']['chartSettings']['min'] = [$minVideoReport['view']];
        $rows['danmaku']['label'] = '弹幕数';
        $rows['danmaku']['desc'] = '截止到当前时间（小时），时间范围内的弹幕数变化趋势对比。';
        $rows['danmaku']['chartSettings']['min'] = [$minVideoReport['danmaku']];
        $rows['reply']['label'] = '评论数';
        $rows['reply']['desc'] = '截止到当前时间（小时），时间范围内的评论数变化趋势对比。';
        $rows['reply']['chartSettings']['min'] = [$minVideoReport['reply']];
        $rows['favorite']['label'] = '收藏数';
        $rows['favorite']['desc'] = '截止到当前时间（小时），时间范围内的实时收藏数变化趋势对比。';
        $rows['favorite']['chartSettings']['min'] = [$minVideoReport['favorite']];
        $rows['coin']['label'] = '硬币枚数';
        $rows['coin']['desc'] = '截止到当前时间（小时），时间范围内的硬币枚数趋势对比。';
        $rows['coin']['chartSettings']['min'] = [$minVideoReport['coin']];
        $rows['likes']['label'] = '获赞数';
        $rows['likes']['desc'] = '截止到当前时间（小时），时间范围内的获赞数趋势对比。';
        $rows['likes']['chartSettings']['min'] = [$minVideoReport['likes']];

        return $rows;
    }

    /**
     * 获取视频数据报表
     * @param Builder $query
     * @return array
     */
    public function videoDataReport(Builder $query): array
    {
        $query->orderBy('time', 'desc');
        $videoReport = $query->get([
            'time',
            'view',
            'danmaku',
            'reply',
            'favorite',
            'coin',
            'likes',
            'dislike'
        ])->toArray();

        foreach ($videoReport as $key => $value) {
            $videoReport[$key]['time'] = date('Y-m-d H:i', $value['time']);

            if (empty($videoReport[$key + 1])) continue;
            $videoReport[$key]['view_trend'] = $value['view'] - $videoReport[$key + 1]['view'];
            $videoReport[$key]['danmaku_trend'] = $value['danmaku'] - $videoReport[$key + 1]['danmaku'];
            $videoReport[$key]['reply_trend'] = $value['reply'] - $videoReport[$key + 1]['reply'];
            $videoReport[$key]['favorite_trend'] = $value['favorite'] - $videoReport[$key + 1]['favorite'];
            $videoReport[$key]['coin_trend'] = $value['coin'] - $videoReport[$key + 1]['coin'];
            $videoReport[$key]['likes_trend'] = $value['likes'] - $videoReport[$key + 1]['likes'];
            $videoReport[$key]['dislike_trend'] = $value['dislike'] - $videoReport[$key + 1]['dislike'];
        }

        return $videoReport;
    }
}