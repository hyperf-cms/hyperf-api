<?php
declare(strict_types=1);

/**
 * 路由控制中心
 */
use Hyperf\HttpServer\Router\Router;
use App\Middleware\RequestMiddleware;

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});