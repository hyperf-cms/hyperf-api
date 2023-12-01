<?php

declare (strict_types=1);
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
 */
#[Controller(prefix: 'common/upload')]
class UploadController extends AbstractController
{
    
    #[RequestMapping(methods: array('POST'), path: 'single_pic')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadSinglePic()
    {
        $params = ['savePath' => $this->request->input('savePath'), 'file' => $this->request->file('file')];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required |file|image'];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失', 'file.file' => '[file] 参数必须为文件类型', 'file.image' => '[file] 文件必须是图片（jpeg、png、bmp、gif 或者 svg）'];
        $this->verifyParams($params, $rules, $message);
        $uploadResult = UploadService::getInstance()->uploadSinglePic($this->request->file('file'), $params['savePath']);
        return $this->success($uploadResult, '上传图片成功');
    }
    
    #[RequestMapping(methods: array('POST'), path: 'single_pic_by_base64')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadSinglePicByBase64()
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
    
    #[RequestMapping(methods: array('POST'), path: 'single_pic_by_blob')]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    public function uploadSinglePicByBlob()
    {
        $params = ['savePath' => $this->request->input('save_path'), 'file' => $this->request->file('file')];
        //配置验证
        $rules = ['savePath' => 'required', 'file' => 'required|file'];
        $message = ['savePath.required' => '[savePath]缺失', 'file.required' => '[file]缺失', 'file.file' => '[file] 参数必须为文件类型'];
        $this->verifyParams($params, $rules, $message);
        $uploadResult = UploadService::getInstance()->uploadSinglePicByBlob($params['file'], $params['savePath']);
        return $this->success($uploadResult);
    }
}