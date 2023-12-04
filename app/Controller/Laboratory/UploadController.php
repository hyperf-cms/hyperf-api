<?php

declare (strict_types=1);
namespace App\Controller\Laboratory;

use App\Controller\AbstractController;
use App\Service\Laboratory\Ws\UploadService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 聊天模块上传接口控制器
 */
#[Controller(prefix: '/laboratory/chat_module')]
class UploadController extends AbstractController
{
    
    #[RequestMapping(methods: array('POST'), path: 'upload_pic_by_base64')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadPicByBase64()
    {
        $params = ['savePath' => $this->request->input('savePath'), 'file' => $this->request->input('file')];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required '];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失'];
        $this->verifyParams($params, $rules, $message);
        base64DecImg($params['file']);
        $uploadResult = UploadService::getInstance()->uploadSinglePicByBase64($params['file'], $params['savePath']);
        return $this->success($uploadResult);
    }
    
    #[RequestMapping(methods: array('POST'), path: 'upload_pic')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadPic()
    {
        $params = ['savePath' => $this->request->input('savePath') ?? '', 'file' => $this->request->file('file'), 'messageId' => $this->request->input('messageId') ?? ''];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required|file|image', 'messageId' => 'required '];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失', 'file.file' => '[file] 参数必须为文件类型', 'file.image' => '[file] 文件必须是图片（jpeg、png、bmp、gif 或者 svg）', 'messageId.required' => '[messageId]缺失'];
        $this->verifyParams($params, $rules, $message);
        $uploadResult = UploadService::getInstance()->uploadPic($params['file'], $params['savePath'], $params['messageId']);
        return $this->success($uploadResult);
    }
    
    #[RequestMapping(methods: array('POST'), path: 'upload_file')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadFile()
    {
        $params = ['savePath' => $this->request->input('savePath') ?? '', 'file' => $this->request->file('file'), 'messageId' => $this->request->input('messageId') ?? ''];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required|file', 'messageId' => 'required '];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失', 'file.file' => '[file] 参数必须为文件类型', 'messageId.required' => '[messageId]缺失'];
        $this->verifyParams($params, $rules, $message);
        $uploadResult = UploadService::getInstance()->uploadFile($params['file'], $params['savePath'], $params['messageId']);
        return $this->success($uploadResult);
    }
    
    #[RequestMapping(methods: array('POST'), path: 'upload_video')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadVideo()
    {
        $params = ['savePath' => $this->request->input('savePath') ?? '', 'file' => $this->request->file('file'), 'messageId' => $this->request->input('messageId') ?? ''];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required|file', 'messageId' => 'required '];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失', 'messageId.required' => '[messageId]缺失', 'file.file' => '[file] 参数必须为文件类型'];
        $this->verifyParams($params, $rules, $message);
        $uploadResult = UploadService::getInstance()->uploadVideo($params['file'], $params['savePath'], $params['messageId']);
        return $this->success($uploadResult);
    }
}