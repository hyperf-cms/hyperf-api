<?php

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Service\System\SystemLogService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SystemLogController
 * @package App\Controller\System
 * @Author YiYuan-Lin
 * @Date: 2021/03/04
 */
#[Controller(prefix: 'setting/log_module/system_log')]
class SystemLogController extends AbstractController
{
    /**
     * 日志目录
     * @var
     */
    protected $log_path;
    /**
     * 错误日志正则匹配表达式
     */
    const LOG_ERROR_PATTER = '/^(?<datetime>.*)\\|\\|(?<env>\\w+)\\|\\|(?<level>\\w+)\\|\\|(.*?)\\:(?<message>.*)/m';
    /**
     * SQL查询正则匹配表达式
     */
    const LOG_SQL_PATTER = '';

    /**
     * 错误日志列表
     * @Author YiYuan
     * @Date 2023/12/4
     * @return void
     */
    #[RequestMapping(path: 'error_log', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function errorLog()
    {
        $logPath = config('log_path') . 'hyperf_error';
        $fileList = SystemLogService::getInstance()->scanDirectory($logPath);
        if (!$fileList) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '该项目暂无日志记录文件');
        }
        $list = [];
        $total = 0;
        // 获取文件树形列表
        $files = $fileList['files'];
        $total = count($files);
        // 循环目录查找该目录下的文件
        foreach ($files as $key => $value) {
        }
    }

    /**
     * 日志路径
     * @Author YiYuan
     * @Date 2023/12/4
     * @return ResponseInterface
     */
    #[RequestMapping(path: 'log_path', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function getLogPath()
    {
        $fileList = SystemLogService::getInstance()->scanDirectory($this->log_path);
        if (!$fileList) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '该项目暂无日志记录文件');
        }
        // 获取文件树形列表
        $fileTree = [];
        $dirs = $fileList['dirs'];
        $files = $fileList['files'];
        // 循环目录查找该目录下的文件
        foreach ($dirs as $key => $value) {
            $fileTree[$key]['type'] = "dir";
            $fileTree[$key]['path'] = $value;
            $pattern = '/' . str_replace(["\\", "/"], "", $value) . '/';
            foreach ($files as $k => $v) {
                $v = str_replace(["\\", "/"], "", $v);
                if (preg_match($pattern, $v, $temp)) {
                    if (!isset($fileTree[$key]['children'])) {
                        $fileTree[$key]['children'] = [];
                    }
                    array_unshift($fileTree[$key]['children'], ['type' => 'file', 'path' => $files[$k], 'dir' => substr($value, strripos($value, "/") + 1)]);
                    unset($files[$k]);
                }
            }
        }
        // 如果还有文件未匹配则为一级目录下的文件
        if (!empty($files)) {
            $files = array_reverse($files);
            foreach ($files as $k => $v) {
                array_push($fileTree, ['type' => 'file', 'path' => $v]);
            }
        }
        return $this->success(['list' => $fileTree, 'total' => count($fileTree)]);
    }

    /**
     * 获取日志内容
     * @Author YiYuan
     * @Date 2023/12/4
     * @return ResponseInterface
     */
    #[RequestMapping(path: 'log_content', methods: array('GET'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function getLogContent()
    {
        $path = $this->request->input('file_path') ?? '';
        if (empty($path)) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '请选择日志文件');
        }
        // 按时间降序获取内容
        $content = SystemLogService::getInstance()->getLogContent($path, self::LOG_PATTER);
        if (!empty($content)) {
            $content = array_reverse($content);
        }
        $total = count($content);
        // 分页
        $curPage = $this->params['cur_page'] ?? 1;
        $pageSize = $this->params['page_size'] ?? 20;
        $contents = array_chunk($content, $pageSize);
        $content = $contents[$curPage - 1];
        return $this->success(['list' => $content, 'total' => $total]);
    }

    /**
     * 删除日志
     * @Author YiYuan
     * @Date 2023/12/4
     * @return ResponseInterface
     */
    #[Explanation(content: '删错日志操作')]
    #[RequestMapping(path: 'destroy_log', methods: array('DELETE'))]
    #[Middleware(middleware: 'App\\Middleware\\RequestMiddleware')]
    #[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
    public function deleteLog()
    {
        $path = $this->request->input('path') ?? '';
        $path = urldecode($path);
        if (!file_exists($path)) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '文件不存在');
        }
        if (!unlink($path)) {
            $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');
        }
        return $this->successByMessage('删除文件成功');
    }
}