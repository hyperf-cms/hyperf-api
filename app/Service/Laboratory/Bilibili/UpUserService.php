<?php
namespace App\Service\Laboratory\Bilibili;

use App\Foundation\Traits\Singleton;
use App\Model\Laboratory\Bilibili\UpUser;
use App\Service\BaseService;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

class UpUserService extends BaseService
{
    use Singleton;

    /**
     * UP主信息（名称、性别、头像、描述、个人认证信息、大会员状态、直播间地址、预览图、标题、房间号、观看人数、直播间状态[开启/关闭]等）
     * @var string
     */
    private $upUserInfoApi = 'https://api.bilibili.com/x/space/acc/info?mid=';

    /**
     * UP主粉丝数、关注数
     * @var string
     */
    private $upUserStatApi  = 'https://api.bilibili.com/x/relation/stat?vmid=';

    /**
     * UP主总播放数、总专栏浏览数
     * @var string
     */
    private $upUserUpStatApi  = 'https://api.bilibili.com/x/space/upstat?mid=';

    /**
     * UP主充电信息（月充电人数、月充电用户、总充电人数）
     * @var string
     */
    private $upUserElecApi  = 'https://api.bilibili.com/x/ugcpay-rank/elec/month/up?up_mid=';

    /**
     * 记录Up主数据
     * @param array $upUserMid
     * @return bool
     * @throws \Exception
     */
    public function recordUpUserInfoFromBilibili(array $upUserMid) : bool
    {
        if (empty($upUserMid)) return false;

        foreach ($upUserMid as $mid) {
            $upUserInfo = $this->getUpUserInfoFromBilibili($mid);
            if (!empty($upUserInfo)) {
                $updateData['name'] = $upUserInfo['name'] ?? '';
                $updateData['sex'] = $upUserInfo['sex'] ?? '';
                $updateData['sign'] = $upUserInfo['sign'] ?? '';
                $updateData['face'] = $upUserInfo['face'] ?? '';
                $updateData['level'] = $upUserInfo['level'] ?? '';
                $updateData['top_photo'] = $upUserInfo['top_photo'] ?? '';
                $updateData['birthday'] = $upUserInfo['birthday'] ?? '';
                $updateData['following'] = $upUserInfo['following'] ?? 0;
                $updateData['follower'] = $upUserInfo['follower'] ?? 0;
                $updateData['video_play'] = $upUserInfo['archive']['view'] ?? 0;
                $updateData['readling'] = $upUserInfo['article']['view'] ?? 0;
                $updateData['likes'] = $upUserInfo['likes'] ?? 0;
                $updateData['recharge_month'] = $upUserInfo['count'] ?? 0;
                $updateData['recharge_total'] = $upUserInfo['total'] ?? 0;
                $updateData['live_room_info'] = empty($upUserInfo['live_room']) ? '' : json_encode($upUserInfo['live_room']);
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                UpUser::where('mid', $mid)->update($updateData);
            }
        }

        return true;
    }

    /**
     * 根据Mid从Bilibili获取Up主数据
     * @param string $upUserMid
     * @return array
     * @throws \Exception
     */
    public function getUpUserInfoFromBilibili(string $upUserMid) : array
    {
        if (empty($upUserMid)) return [];
        $upUserInfo = curl_get($this->upUserInfoApi . $upUserMid);
        $upUserStat = curl_get($this->upUserStatApi . $upUserMid);
        //这个接口比较特殊，需要用到cookie
        $upUserUpStat = curl_get($this->upUserUpStatApi . $upUserMid, [], [],  config('bilibili.cookie'));
        $upUserElec = curl_get($this->upUserElecApi . $upUserMid);

        return  [
            'name' => $upUserInfo['data']['name'] ?? '',
            'sex' => $upUserInfo['data']['sex'] ?? '',
            'sign' => $upUserInfo['data']['sign'] ?? '',
            'face' => $upUserInfo['data']['face'] ?? '',
            'level' => $upUserInfo['data']['level'] ?? '',
            'top_photo' => $upUserInfo['data']['top_photo'] ?? '',
            'birthday' => $upUserInfo['data']['birthday'] ?? '',
            'following' => $upUserStat['data']['following'] ?? 0,
            'follower' => $upUserStat['data']['follower'] ?? 0,
            'video_play' => $upUserUpStat['data']['archive']['view'] ?? 0,
            'readling' => $upUserUpStat['data']['article']['view'] ?? 0,
            'likes' => $upUserUpStat['data']['likes'] ?? 0,
            'recharge_month' => $upUserElec['data']['count'] ?? 0,
            'recharge_total' => $upUserElec['data']['total'] ?? 0,
            'live_room_info' => empty($upUserInfo['data']['live_room']) ? '' : json_encode($upUserInfo['data']['live_room']),
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
        $minUpUserReport = $query->select(Db::raw(
            'min(following) as following, 
                   min(follower) as follower,
                   min(video_play) as video_play,
                   min(readling) as readling,
                   min(likes) as likes,
                   min(recharge_total) as recharge_total
        '))->first()->toArray();
        $upUserReport = array_column($upUserReport, null, 'time');

        $rows = [];
        $list = [];
        foreach ($timestampList as $ts) {
            $dataDate = date('Y-m-d', $ts);
            if (!empty($upUserReport[$ts]['following'])) $list['following'][$dataDate][$ts] = intval($upUserReport[$ts]['following']);
            if (!empty($upUserReport[$ts]['follower'])) $list['follower'][$dataDate][$ts] = intval($upUserReport[$ts]['follower']);
            if (!empty($upUserReport[$ts]['likes'])) $list['likes'][$dataDate][$ts] = intval($upUserReport[$ts]['likes']);
            if (!empty($upUserReport[$ts]['recharge_total'])) $list['recharge_total'][$dataDate][$ts] = intval($upUserReport[$ts]['recharge_total']);
            if (!empty($upUserReport[$ts]['video_play'])) $list['video_play'][$dataDate][$ts] = intval($upUserReport[$ts]['video_play']);
            if (!empty($upUserReport[$ts]['readling'])) $list['readling'][$dataDate][$ts] = intval($upUserReport[$ts]['readling']);
        }

        foreach ($list as $key => $value) {
            $rows[$key]['columns'] = ['time'];
            for ($i = 0; $i < 24; $i ++) {
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
        $rows['follower']['label'] = '粉丝数';
        $rows['follower']['desc'] = '截止到当前时间（小时），时间范围内的粉丝数变化趋势对比。';
        $rows['follower']['chartSettings']['min'] = [$minUpUserReport['follower']];
        $rows['likes']['label'] = '获赞数';
        $rows['likes']['desc'] = '截止到当前时间（小时），时间范围内的获赞数变化趋势对比。';
        $rows['likes']['chartSettings']['min'] = [$minUpUserReport['likes']];
        $rows['recharge_total']['label'] = '总充电数';
        $rows['recharge_total']['desc'] = '截止到当前时间（小时），时间范围内的总充电数变化趋势对比。';
        $rows['recharge_total']['chartSettings']['min'] = [$minUpUserReport['recharge_total']];
        $rows['following']['label'] = '关注数';
        $rows['following']['desc'] = '截止到当前时间（小时），时间范围内的实时关注数变化趋势对比。';
        $rows['following']['chartSettings']['min'] = [$minUpUserReport['following']];
        $rows['video_play']['label'] = '视频播放数';
        $rows['video_play']['desc'] = '截止到当前时间（小时），时间范围内的视频播放数趋势对比。';
        $rows['video_play']['chartSettings']['min'] = [$minUpUserReport['video_play']];
        $rows['readling']['label'] = '阅读数';
        $rows['readling']['desc'] = '截止到当前时间（小时），时间范围内的阅读数变化趋势对比。';
        $rows['readling']['chartSettings']['min'] = [$minUpUserReport['readling']];

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

            if (empty($upUserReport[$key + 1])) continue;
            $upUserReport[$key]['following_trend'] = $value['following'] - $upUserReport[$key + 1]['following'];
            $upUserReport[$key]['follower_trend'] = $value['follower'] - $upUserReport[$key + 1]['follower'];
            $upUserReport[$key]['video_play_trend'] = $value['video_play'] - $upUserReport[$key + 1]['video_play'];
            $upUserReport[$key]['readling_trend'] = $value['readling'] - $upUserReport[$key + 1]['readling'];
            $upUserReport[$key]['likes_trend'] = $value['likes'] - $upUserReport[$key + 1]['likes'];
            $upUserReport[$key]['recharge_total_trend'] = $value['recharge_total'] - $upUserReport[$key + 1]['recharge_total'];
            $upUserReport[$key]['recharge_month_trend'] = $value['recharge_month'] - $upUserReport[$key + 1]['recharge_month'];
        }

        return $upUserReport;
    }
}