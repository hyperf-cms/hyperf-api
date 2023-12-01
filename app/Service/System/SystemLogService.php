<?php
namespace App\Service\System;

use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Filesystem\Filesystem;

/**
 * 系统日志服务类
 * Class SystemLogService
 * @package App\Service\System
 * @Author YiYuan-Lin
 * @Date: 2021/03/04
 */
class SystemLogService extends BaseService
{
    use Singleton;

    /**
     * @Inject()
     * @var Filesystem
     */
    private $filesystem;

    /**
     * 遍历目录
     * @param string [路径] $dirPath
     * @return array|bool
     */
    public function scanDirectory($dirPath)
    {
        if (!is_dir($dirPath)) return false;
        $dirPath = rtrim($dirPath,'/') . '/';
        $dirs = [ $dirPath ];

        $fileContainer = [];
        $dirContainer = [];

        do {
            $workDir = array_pop($dirs);
            $scanResult = scandir($workDir);
            foreach ($scanResult as $files) {
                if ($files == '.' || $files == '..') continue;
                $realPath = $workDir . $files;
                if (is_dir($realPath)) {
                    array_push($dirs, $realPath . '/');
                    $dirContainer[] = $realPath;
                } elseif (is_file($realPath)) {
                    $fileContainer[] = $realPath;
                }
            }
        } while ($dirs);

        return [
            'files' => $fileContainer,
            'dirs' => $dirContainer
        ];
    }

    /**
     * 获取日志内容
     * @param [文件路径] $filePath
     * @param [内容正则表达式] $pattern
     * @return array
     */
    public function getLogContent($filePath, $pattern)
    {
        $content = $this->filesystem->sharedGet($filePath);
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER, 0);

        $logs = [];
        foreach ($matches as $match) {
            $logs[] = [
                'datetime' => $match['datetime'],
                'env'      => $match['env'],
                'level'    => strtolower($match['level']),
                'message'  => trim($match['message'])
            ];
        }

        return $logs;
    }
}
