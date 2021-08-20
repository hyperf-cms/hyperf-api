<?php
namespace App\Service\Laboratory\Bilibili;

use App\Foundation\Traits\Singleton;
use App\Model\Laboratory\Bilibili\UpUser;
use App\Service\BaseService;

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
     * 根据Up主mid获取相关数据信息
     * @param array $upUserMid
     * @return bool
     * @throws \Exception
     */
    public function recordUpUserInfoFromBilibili(array $upUserMid) : bool
    {
        if (empty($upUserMid)) return false;

        foreach ($upUserMid as $mid) {
            $upUserInfo = curl_get($this->upUserInfoApi . $mid);
            $upUserStat = curl_get($this->upUserStatApi . $mid);
            //这个接口比较特殊，需要用到cookie
            $upUserUpStat = curl_get($this->upUserUpStatApi . $mid, [], [],  config('bilibili.cookie'));
            $upUserElec = curl_get($this->upUserElecApi . $mid);

            $updateData['name'] = $upUserInfo['data']['name'] ?? '';
            $updateData['sex'] = $upUserInfo['data']['sex'] ?? '';
            $updateData['sign'] = $upUserInfo['data']['sign'] ?? '';
            $updateData['face'] = $upUserInfo['data']['face'] ?? '';
            $updateData['level'] = $upUserInfo['data']['level'] ?? '';
            $updateData['top_photo'] = $upUserInfo['data']['top_photo'] ?? '';
            $updateData['birthday'] = $upUserInfo['data']['birthday'] ?? '';
            $updateData['following'] = $upUserStat['data']['following'] ?? 0;
            $updateData['follower'] = $upUserStat['data']['follower'] ?? 0;
            $updateData['video_play'] = $upUserUpStat['data']['archive']['view'] ?? 0;
            $updateData['readling'] = $upUserUpStat['data']['article']['view'] ?? 0;
            $updateData['likes'] = $upUserUpStat['data']['likes'] ?? 0;
            $updateData['recharge_month'] = $upUserElec['data']['count'] ?? 0;
            $updateData['recharge_total'] = $upUserElec['data']['total'] ?? 0;
            $updateData['live_room_info'] = empty($upUserInfo['data']['live_room']) ? '' : json_encode($upUserInfo['data']['live_room']);
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            UpUser::where('mid', $mid)->update($updateData);
        }

        return true;
    }
}