<?php

declare (strict_types=1);
namespace App\Controller\Blog;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\Blog\PhotoAlbum;
use App\Model\System\DictData;
use App\Model\System\GlobalConfig;
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
 */
#[Controller(prefix: 'blog/picture_module/album')]
class AlbumController extends AbstractController
{
    #[Inject]
    private PhotoAlbum $photoAlbum;

    /**
     * 列表
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'list', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function index()
    {
        $photoAlbumQuery = $this->photoAlbum->newQuery();
        $albumName = $this->request->input('album_name') ?? '';
        $albumStatus = $this->request->input('album_status') ?? '';
        if (!empty($albumName)) {
            $photoAlbumQuery->where('album_name', 'like', '%' . $albumName . '%');
        }
        if (strlen($albumStatus) > 0) {
            $photoAlbumQuery->where('album_status', $albumStatus);
        }
        $total = $photoAlbumQuery->count();
        $photoAlbumQuery = $this->pagingCondition($photoAlbumQuery, $this->request->all());
        $data = $photoAlbumQuery->get();
        return $this->success(['list' => $data, 'total' => $total]);
    }

    /**
     * 相册选项列表
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'album_option', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function albumOptionList()
    {
        $photoAlbumQuery = $this->photoAlbum->newQuery();
        $photoAlbumQuery = $photoAlbumQuery->select('id', 'album_name');
        $data = $photoAlbumQuery->get();
        return $this->success(['list' => $data]);
    }

    /**
     * 添加
     * @Author YiYuan
     * @Date 2023/12/1
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '添加相册操作')]
    #[RequestMapping(path: 'store', methods: array('POST'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function store()
    {
        $postData = $this->request->all();
        $params = ['album_name' => $postData['album_name'] ?? '', 'album_desc' => $postData['album_desc'] ?? '', 'album_type' => $postData['album_type'] ?? 1, 'album_status' => $postData['album_status'] ?? 1, 'album_author' => $postData['album_author'] ?? '', 'album_cover' => $postData['album_cover'] ?? '', 'album_question' => $postData['album_question'] ?? '', 'album_answer' => $postData['album_answer'] ?? '', 'album_sort' => $postData['album_sort'] ?? 99];
        //配置验证
        $rules = ['album_name' => 'required'];
        //错误信息
        $message = ['album_name.required' => '[album_name]缺失'];
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
        if (!$photoAlbumObj->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '添加相册错误');
        }
        return $this->successByMessage('添加相册成功');
    }

    /**
     * 获取编辑选项
     * @Author YiYuan
     * @Date 2023/12/1
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[RequestMapping(path: 'edit/{id}', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function edit(int $id)
    {
        $dictDataInfo = PhotoAlbum::findById($id);
        if (empty($dictDataInfo)) {
            $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取相册信息失败');
        }
        return $this->success(['list' => $dictDataInfo]);
    }

    /**
     * 修改
     * @Author YiYuan
     * @Date 2023/12/1
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '修改相册')]
    #[RequestMapping(path: 'update/{id}', methods: array('PUT'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function update(int $id)
    {
        if (empty($id)) {
            $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        }
        $postData = $this->request->all();
        $params = ['id' => $id, 'album_name' => $postData['album_name'] ?? '', 'album_desc' => $postData['album_desc'] ?? '', 'album_type' => $postData['album_type'] ?? 1, 'album_status' => $postData['album_status'] ?? 1, 'album_author' => $postData['album_author'] ?? '', 'album_cover' => $postData['album_cover'] ?? '', 'album_question' => $postData['album_question'] ?? '', 'album_answer' => $postData['album_answer'] ?? '', 'album_sort' => $postData['album_sort'] ?? 99];
        //配置验证
        $rules = ['album_name' => 'required', 'id' => 'required|integer'];
        //错误信息
        $message = ['album_name.required' => '[album_name]缺失', 'id.required' => '[id]缺失', 'id.integer' => '[id] 必须为整型'];
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
        if (!$photoAlbumObj->save()) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '修改相册信息错误');
        }
        return $this->successByMessage('修改相册信息成功');
    }

    /**
     * 删除
     * @Author YiYuan
     * @Date 2023/12/1
     * @param int $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[Explanation(content: '删除相册')]
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
            if (!PhotoAlbum::whereIn('id', $idArr)->delete()) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        } else {
            if (!intval($id)) {
                $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
            }
            if (!PhotoAlbum::destroy($id)) {
                $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
            }
        }
        return $this->successByMessage('删除相册成功');
    }
}