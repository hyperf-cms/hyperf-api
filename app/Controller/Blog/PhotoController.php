<?php

declare (strict_types=1);
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
 */
#[Controller(prefix: 'blog/picture_module/photo')]
class PhotoController extends AbstractController
{
    #[Inject]
    private Photo $photo;

    /**
     * 列表
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */

    #[Explanation(content: '图片列表')]
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $photoQuery = $this->photo->newQuery();
        $photoAlbum = $this->request->input('photo_album') ?? '';
        if (!empty($photoAlbum)) {
            $photoQuery->where('photo_album', $photoAlbum);
        }
        $total = $photoQuery->count();
        $photoQuery = $this->pagingCondition($photoQuery, $this->request->all());
        $photoQuery->with('getPhotoAlbum:id,album_name');
        $data = $photoQuery->get();
        return $this->success(['list' => $data, 'total' => $total]);
    }

    /**
     * 添加
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '添加图片')]
    #[RequestMapping(path: 'store', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function store()
    {
        $postData = $this->request->all();
        $params = ['photo_url' => $postData['photo_url'] ?? '', 'photo_album' => $postData['photo_album'] ?? ''];
        //配置验证
        $rules = ['photo_url' => 'required|array', 'photo_album' => 'required'];
        //错误信息
        $message = ['photo_url.required' => '[photo_url]缺失', 'photo_url.array' => '[photo_url] 类型必须为数组', 'photo_album.required' => '[photo_album]缺失'];
        $this->verifyParams($params, $rules, $message);
        if (is_array($params['photo_url'])) {
            foreach ($params['photo_url'] as $key) {
                Photo::query()->insert(['photo_url' => $key, 'photo_album' => $params['photo_album'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
        return $this->successByMessage('添加照片成功');
    }

    /**
     * 删除
     * @Author YiYuan
     * @Date 2023/12/1
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '删除图片')]
    #[RequestMapping(path: 'destroy/{id}', methods: array('DELETE'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function destroy(int $id)
    {
        if ($id == 0) {
            $idArr = $this->request->input('id') ?? [];
            if (empty($idArr) || !is_array($idArr)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数类型不正确');
            }
            if (!Photo::whereIn('id', $idArr)->delete()) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        } else {
            if (!$id) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
            }
            if (!Photo::destroy($id)) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        }
        return $this->successByMessage('删除图片成功');
    }
}