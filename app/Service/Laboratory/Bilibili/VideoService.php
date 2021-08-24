<?php
namespace App\Service\Laboratory\Bilibili;

use App\Foundation\Traits\Singleton;
use App\Model\Laboratory\Bilibili\Video;
use App\Service\BaseService;
use Hyperf\Database\Model\Builder;

class VideoService extends BaseService
{
    use Singleton;

    /**
     * 获取视频AV号（内含视频基本信息，若此BV对应视频属于系列视频，API会列出所有系列视频）
     * @var string
     */
    private $videoInfoApi = 'https://api.bilibili.com/x/web-interface/view?bvid=';

    /**
     * 更新视频数据
     * @param array $videoBVid
     * @return bool
     * @throws \Exception
     */
    public function recordVideoInfoFromBilibili(array $videoBVid) : bool
    {
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
                $updateData['like'] = $videoInfo['like'] ?? 0;
                $updateData['dislike'] = $videoInfo['dislike'] ?? 0;
                $updateData['owner'] = !empty($videoInfo['owner']) ? json_encode($videoInfo['owner']) : '';
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                Video::where('bvid', $bvid)->update($updateData);
            }
        }

        return true;
    }

    /**
     * 根据BVid从Bilibili获取视频数据
     * @param string $videoBVid
     * @return array
     * @throws \Exception
     */
    public function getVideoInfoFromBilibili(string $videoBVid) : array
    {
        if (empty($videoBVid)) return [];
        $videoInfo = curl_get($this->videoInfoApi . $videoBVid);

        return  [
            'bvid' => $videoInfo['data']['bvid'] ?? '',
            'mid' => $videoInfo['data']['owner']['mid'] ?? '',
            'owner' => $videoInfo['data']['owner'] ?? [],
            'cover' => $videoInfo['data']['pic'] ?? '',
            'title' => $videoInfo['data']['title'] ?? '',
            'public_time' => $videoInfo['data']['pubdate'] ?? 0,
            'desc' => $videoInfo['data']['desc'] ?? '',
            'duration' => $videoInfo['data']['duration'] ?? 0,
            'view' => $videoInfo['data']['stat']['view'] ?? 0,
            'danmaku' => $videoInfo['data']['stat']['danmaku'] ?? 0,
            'reply' => $videoInfo['data']['stat']['reply'] ?? 0,
            'favorite' => $videoInfo['data']['stat']['favorite'] ?? 0,
            'coin' => $videoInfo['data']['stat']['coin'] ?? 0,
            'like' => $videoInfo['data']['stat']['like'] ?? 0,
            'dislike' => $videoInfo['data']['stat']['dislike'] ?? 0,
        ];
    }

    /**
     * 获取UP主数据趋势图表
     * @param Builder $query
     * @param array $timestampList
     * @return array
     */
    public function upUserChartTrend(Builder $query, array $timestampList = []) : array
    {
        $query->orderBy('time');
        $upUserReport = $query->get([
            'time', 'following', 'follower', 'video_play', 'readling', 'likes', 'recharge_total'
        ])->toArray();
        $upUserReport = array_column($upUserReport, null, 'time');

        $rows = [];
        $list = [];
        foreach ($timestampList as $ts) {
            $dataDate = date('Y-m-d', $ts);
            $list['following'][$dataDate][] = intval($upUserReport[$ts]['following'] ?? 0);
            $list['follower'][$dataDate][] = intval($upUserReport[$ts]['follower'] ?? 0);
            $list['video_play'][$dataDate][] = intval($upUserReport[$ts]['video_play'] ?? 0);
            $list['readling'][$dataDate][] = intval($upUserReport[$ts]['readling'] ?? 0);
            $list['likes'][$dataDate][] = intval($upUserReport[$ts]['likes'] ?? 0);
            $list['recharge_total'][$dataDate][] = intval($upUserReport[$ts]['recharge_total'] ?? 0);
        }
        foreach ($list as $key => $value) {
            $rows[$key]['columns'] = ['time'];
            for ($i = 0; $i < 24; $i ++) {
                $temp = [];
                foreach ($value as $k => $v) {
                    $temp['time'] = $i;
                    $temp[$k] = $value[$k][$i] ?? 0;
                    if ($i == 0) {
                        $rows[$key]['columns'][] = $k;
                    }
                }
                $rows[$key]['rows'][] = $temp;
            }
        }
        $rows['following']['label'] = '关注数';
        $rows['following']['desc'] = '截止到当前时间（小时），时间范围内的实时关注数变化趋势对比。';
        $rows['follower']['label'] = '粉丝数';
        $rows['follower']['desc'] = '截止到当前时间（小时），时间范围内的粉丝数变化趋势对比。';
        $rows['video_play']['label'] = '视频播放数';
        $rows['video_play']['desc'] = '截止到当前时间（小时），时间范围内的视频播放数趋势对比。';
        $rows['readling']['label'] = '阅读数';
        $rows['readling']['desc'] = '截止到当前时间（小时），时间范围内的阅读数变化趋势对比。';
        $rows['likes']['label'] = '获赞数';
        $rows['likes']['desc'] = '截止到当前时间（小时），时间范围内的获赞数变化趋势对比。';
        $rows['recharge_total']['label'] = '总充电数';
        $rows['recharge_total']['desc'] = '截止到当前时间（小时），时间范围内的总充电数变化趋势对比。';

        return $rows;
    }

    /**
     * 获取UP主数据报表
     * @param Builder $query
     * @return array
     */
    public function upUserDataReport(Builder $query) : array
    {
        $query->orderBy('time', 'desc');
        $upUserReport = $query->get([
            'time', 'following', 'follower', 'video_play', 'readling', 'likes', 'recharge_total', 'recharge_month'
        ])->toArray();

        foreach ($upUserReport as $key => $value) {
            $upUserReport[$key]['time'] = date('Y-m-d H:i', $value['time']);
        }
        return $upUserReport;
    }
}