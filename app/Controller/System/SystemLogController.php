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


/**
 * Class SystemLogController
 * @Controller(prefix="setting/log_module/system_log")
 * @package App\Controller\System
 * @Author YiYuan-Lin
 * @Date: 2021/03/04
 */
class SystemLogController extends AbstractController
{
    /**
     * 日志目录
     * @var
     */
    protected $log_path;

    /**
     * 广告项目日志内容正则匹配表达式
     */
    const LOG_PATTER = '/^\[(?<datetime>.*)\]\s(?<env>\w+)\.(?<level>\w+):(?<message>.*)/m';

    /**
     * 获取系统日志列表
     * @RequestMapping(path="log_path", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getLogPath()
    {
        $this->log_path = config('log_path');

        $fileList = SystemLogService::getInstance()->scanDirectory($this->log_path);
        if (!$fileList) $this->throwExp(StatusCode::ERR_EXCEPTION, '该项目暂无日志记录文件');

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
                    if (!isset($fileTree[$key]['children'])) $fileTree[$key]['children'] = [];
                    array_unshift($fileTree[$key]['children'], [
                        'type' => 'file',
                        'path' => $files[$k]
                    ]);
                    unset($files[$k]);
                };
            }
        }
        // 如果还有文件未匹配则为一级目录下的文件
        if (!empty($files)) {
            $files = array_reverse($files);
            foreach ($files as $k => $v) {
                array_push($fileTree, [
                    'type' => 'file',
                    'path' => $v
                ]);
            }
        }

        return $this->success([
            'list' => $fileTree,
            'total' => count($fileTree),
        ]);
    }

    /**
     * 获取日志文件内容
     * @RequestMapping(path="log_content", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getLogContent()
    {
        $path = $this->request->input('file_path') ?? '';
        if (empty($path)) $this->throwExp(StatusCode::ERR_EXCEPTION, '请选择日志文件');

        // 按时间降序获取内容
        $content = SystemLogService::getInstance()->getLogContent($path, self::LOG_PATTER);
        if (!empty($content)) $content=array_reverse($content);
        $total = count($content);

        // 分页
        $curPage = $this->params['cur_page'] ?? 1;
        $pageSize = $this->params['page_size'] ?? 20;
        $contents = array_chunk($content, $pageSize);
        $content = $contents[$curPage - 1];

        return $this->success([
            'list' => $content,
            'total' => $total
        ]);
    }

    /**
     * @Explanation(content="删除日志")
     * @RequestMapping(path="destroy_log", methods="delete")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteLog()
    {
        $path = $this->request->input('path') ?? '';
        $path = urldecode($path);

        if (!file_exists($path)) $this->throwExp(StatusCode::ERR_EXCEPTION, '文件不存在');
        if (!unlink($path)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除文件成功');
    }
}