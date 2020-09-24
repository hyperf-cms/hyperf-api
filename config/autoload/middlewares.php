<?php

declare(strict_types=1);

/**
 * 全局注册中间件
 * @Author YiYuan-Lin
 * @date 2020/09/21 11:03
 */
return [
    'http' => [
        \App\Middleware\CorsMiddleware::class,
    ],
];
