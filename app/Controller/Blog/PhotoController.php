<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\Blog\Photo;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 图片控制器
 * Class PhotoController
 * @Controller(prefix="blog/picture_module/photo")
 */
class PhotoController extends AbstractController
{
    /**
     * @Inject()
     * @var Photo
     */
    private $photo;

    /**
     * 获取图片列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $photoQuery = $this->photo->newQuery();
        $photoAlbum = $this->request->input('photo_album') ?? '';
        if (!empty($photoAlbum)) $photoQuery->where('photo_album', $photoAlbum);

        $total = $photoQuery->count();
        $photoQuery = $this->pagingCondition($photoQuery, $this->request->all());
        $photoQuery->with('getPhotoAlbum:id,album_name');
        $data = $photoQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * @Explanation(content="删除图片信息")
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!Photo::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除图片成功');
    }
}
