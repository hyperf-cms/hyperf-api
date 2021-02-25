<?php
declare(strict_types=1);

namespace App\Foundation\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * 注释解释注解类，用户记录操作日志等操作
 * @Annotation
 * @Target({"METHOD"})
 */
class Explanation extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $content = '';
}

