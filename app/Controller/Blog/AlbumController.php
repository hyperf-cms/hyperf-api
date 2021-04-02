<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\Blog\PhotoAlbum;
use App\Model\System\DictData;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 相册控制器
 * Class AlbumController
 * @Controller(prefix="blog/picture_module/album")
 */
class AlbumController extends AbstractController
{
    /**
     * @Inject()
     * @var PhotoAlbum
     */
    private $photoAlbum;

    /**
     * 获取相册列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $photoAlbumQuery = $this->photoAlbum->newQuery();
        $albumName = $this->request->input('album_name') ?? '';
        if (!empty($albumName)) $photoAlbumQuery->where('album_name', 'like', '%' . $albumName . '%');

        $total = $photoAlbumQuery->count();
        $photoAlbumQuery = $this->pagingCondition($photoAlbumQuery, $this->request->all());
        $data = $photoAlbumQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * @Explanation(content="添加相册")
     * @RequestMapping(path="store", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'album_name'  => $postData['album_name'] ?? '',
            'album_desc'  => $postData['album_desc'] ?? '',
            'album_type'  => $postData['album_type'] ?? 1,
            'album_status' => $postData['album_status'] ?? 1,
            'album_author' => $postData['album_author'] ?? '',
            'album_cover' => $postData['album_cover'] ?? '',
            'album_question' => $postData['album_question'] ?? '',
            'album_answer' => $postData['album_answer'] ?? '',
            'album_sort' => $postData['album_sort'] ?? 99
        ];
        //配置验证
        $rules = [
            'album_name'  => 'required',
        ];
        //错误信息
        $message = [
            'album_name.required' => '[album_name]缺失',
        ];
        if ($params['album_type'] == 2) {
            $rules['album_question'] = 'required';
            $rules['album_answer'] = 'required';
            $message['album_question.required'] = '[album_question]缺失';
            $message['album_answer.required'] = '[album_answer]缺失';
        }
        $this->verifyParams($params, $rules, $message);

        $photoAlbumObj = new PhotoAlbum();
        $photoAlbumObj->album_name = $params['album_name'];
        $photoAlbumObj->album_desc = $params['album_desc'];
        $photoAlbumObj->album_type = $params['album_type'];
        $photoAlbumObj->album_status = $params['album_status'];
        $photoAlbumObj->album_author = $params['album_author'];
        $photoAlbumObj->album_cover = $params['album_cover'];
        $photoAlbumObj->album_question = $params['album_question'];
        $photoAlbumObj->album_answer = $params['album_answer'];
        $photoAlbumObj->album_sort = $params['album_sort'];
        if (!$photoAlbumObj->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加相册错误');

        return $this->successByMessage('添加相册成功');
    }

    /**
     * 获取单个字典数据信息
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $dictDataInfo = PhotoAlbum::findById($id);
        if (empty($dictDataInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取相册信息失败');

        return $this->success([
            'list' => $dictDataInfo
        ]);
    }

    /**
     * @Explanation(content="修改相册信息")
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(int $id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();
        $params = [
            'id' => $id,
            'album_name'  => $postData['album_name'] ?? '',
            'album_desc'  => $postData['album_desc'] ?? '',
            'album_type'  => $postData['album_type'] ?? 1,
            'album_status' => $postData['album_status'] ?? 1,
            'album_author' => $postData['album_author'] ?? '',
            'album_cover' => $postData['album_cover'] ?? '',
            'album_question' => $postData['album_question'] ?? '',
            'album_answer' => $postData['album_answer'] ?? '',
            'album_sort' => $postData['album_sort'] ?? 99
        ];
        //配置验证
        $rules = [
            'album_name'  => 'required',
            'id'  => 'required|integer',
        ];
        //错误信息
        $message = [
            'album_name.required' => '[album_name]缺失',
            'id.required' => '[id]缺失',
            'id.integer' => '[id] 必须为整型',
        ];
        $this->verifyParams($params, $rules, $message);

        $photoAlbumObj = PhotoAlbum::findById($id);
        $photoAlbumObj->album_name = $params['album_name'];
        $photoAlbumObj->album_desc = $params['album_desc'];
        $photoAlbumObj->album_type = $params['album_type'];
        $photoAlbumObj->album_status = $params['album_status'];
        $photoAlbumObj->album_author = $params['album_author'];
        $photoAlbumObj->album_cover = $params['album_cover'];
        $photoAlbumObj->album_question = $params['album_question'];
        $photoAlbumObj->album_answer = $params['album_answer'];
        $photoAlbumObj->album_sort = $params['album_sort'];
        if (!$photoAlbumObj->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改相册信息错误');

        return $this->successByMessage('修改相册信息成功');
    }

    /**
     * @Explanation(content="删除相册信息")
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
        if (!PhotoAlbum::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除相册成功');
    }

}