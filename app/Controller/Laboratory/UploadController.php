<?php

declare(strict_types=1);

namespace App\Controller\Laboratory;

use App\Middleware\RequestMiddleware;
use App\Controller\AbstractController;
use App\Service\Laboratory\UploadService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 聊天模块上传接口控制器
 * @Controller(prefix="/laboratory/chat_module")
 */
class UploadController extends AbstractController
{
    /**
     * 上传单张图片接口
     * @RequestMapping(path="upload_pic_by_base64", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadPicByBase64()
    {
        $params = [
            'savePath' => $this->request->input('savePath'),
            'file' => $this->request->input('file'),
        ];
        //配置验证
        $rules = [
            'savePath' => 'required',
            'file' => 'required ',
        ];
        $message = [
            'savePath.required' => '[savePath]缺失',
            'file.required' => '[file]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        base64DecImg($params['file']);
        $uploadResult = UploadService::getInstance()->uploadSinglePicByBase64($params['file'], $params['savePath']);
        return $this->success($uploadResult);
    }

    /**
     * 上传图片
     * @RequestMapping(path="upload_file", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadFile()
    {
        $params = [
            'savePath' => $this->request->input('savePath') ?? '',
            'file' => $this->request->file('file'),
            'messageId' => $this->request->input('messageId') ?? ''
        ];
        //配置验证
        $rules = [
            'savePath' => 'required',
            'file' => 'required ',
            'messageId' => 'required ',
        ];
        $message = [
            'savePath.required' => '[savePath]缺失',
            'file.required' => '[file]缺失',
            'messageId.required' => '[messageId]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $uploadResult = UploadService::getInstance()->uploadFile($params['file'], $params['savePath'], $params['messageId']);
        return $this->success($uploadResult);
    }
}