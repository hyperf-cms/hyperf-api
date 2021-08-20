<?php
declare(strict_types=1);

namespace App\Controller\Laboratory\Bilibili;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Foundation\Utils\Queue;
use App\Job\Bilibili\UpUserInfoRecordJob;
use App\Model\Laboratory\Bilibili\UpUser;
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
}
