<?php
namespace App\Command\Bilibili;

use App\Foundation\Facades\Log;
use App\Model\Laboratory\Bilibili\Video;
use App\Model\Laboratory\Bilibili\VideoReport;
use App\Service\Laboratory\Bilibili\VideoService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="BilibiliVideoReport", rule="00 * * * *", callback="execute", memo="BilibiliUp视频数据报表采集")
 */
class BilibiliVideoReport
{
    /**
     *
     * @Inject()
     * @var Video
     */
    private $video;

    /**
     * 定时记录视频数据变化
     * @throws \Exception
     */
    public function execute()
    {
        try {
            //先获取需要定时获取数据报表的Up主
            $videoBVidList = $this->video->newQuery()
                ->where('timed_status', Video::TIMED_STATUS_ON)
                ->pluck('bvid')->toArray();

            foreach ($videoBVidList as $bvid) {
                //获取主播数据
                $videoReport = VideoService::getInstance()->getVideoInfoFromBilibili($bvid);

                if (!empty($videoReport)) {
                    //写入数据报表
                    $insertData['time'] = strtotime(date('Y-m-d H:i'));
                    $insertData['bvid'] = $bvid;
                    $insertData['mid'] = $videoReport['mid'];
                    $insertData['view'] = $videoReport['view'] ?? 0;
                    $insertData['danmaku'] = $videoReport['danmaku'] ?? 0;
                    $insertData['reply'] = $videoReport['reply'] ?? 0;
                    $insertData['favorite'] = $videoReport['favorite'] ?? 0;
                    $insertData['coin'] = $videoReport['coin'] ?? 0;
                    $insertData['likes'] = $videoReport['likes'] ?? 0;
                    $insertData['dislike'] = $videoReport['dislike'] ?? 0;
                    VideoReport::query()->insert($insertData);

                    //修改主播信息为最新数据
                    $updateData['view'] = $videoReport['view'] ?? 0;
                    $updateData['danmaku'] = $videoReport['danmaku'] ?? 0;
                    $updateData['reply'] = $videoReport['reply'] ?? 0;
                    $updateData['favorite'] = $videoReport['favorite'] ?? 0;
                    $updateData['coin'] = $videoReport['coin'] ?? 0;
                    $updateData['likes'] = $videoReport['likes'] ?? 0;
                    $updateData['dislike'] = $videoReport['dislike'] ?? 0;
                    $updateData['owner'] = !empty($videoReport['owner']) ? json_encode($videoReport['owner']) : '';
                    $updateData['updated_at'] = date('Y-m-d H:i:s');
                    Video::where('bvid', $bvid)->update($updateData);
                }
            }
        }catch (\Exception $e) {
            Log::crontabLog()->error($e->getMessage());
            //如果报错，重新执行
            $obj = new BilibiliVideoReport();
            $obj->execute();
        }
    }
}
