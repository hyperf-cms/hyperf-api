<?php

declare(strict_types=1);

namespace App\Controller\Common;

use App\Middleware\RequestMiddleware;
use App\Controller\AbstractController;
use App\Service\Common\UploadService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 公共上传接口控制器
 * @Controller(prefix="common/upload")
 */
class UploadController extends AbstractController
{
    /**
     * 上传单张图片接口
     * @RequestMapping(path="single_pic", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadSinglePic()
    {
        $params = [
            'savePath' => $this->request->input('savePath'),
            'file' => $this->request->file('file'),
        ];
        //配置验证
        $rules = [
            'savePath' => 'required',
            'file' => 'required |file|image',
        ];
        $message = [
            'savePath.required' => '[savePath]缺失',
            'file.required' => '[name]缺失',
            'file.file' => '[file] 参数必须为文件类型',
            'file.image' => '[file] 文件必须是图片（jpeg、png、bmp、gif 或者 svg）',
        ];
        $this->verifyParams($params, $rules, $message);

        $uploadResult = UploadService::getInstance()->uploadSinglePic($this->request->file('file'), $params['savePath']);

        return $this->success($uploadResult, '上传图片成功');
    }
}