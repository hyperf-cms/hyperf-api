<?php
declare(strict_types=1);

/**
 * 路由控制中心
 */
use Hyperf\HttpServer\Router\Router;
use App\Middleware\RequestMiddleware;

// ================== 登陆相关路由 ==================
Router::addGroup('/auth',function (){
        Router::post('/login','App\Controller\Auth\LoginController@login');
        Router::post('/logout','App\Controller\Auth\LoginController@logout', ['middleware' => [RequestMiddleware::class]]);
});

Router::addGroup('/test',function (){
        Router::get('/index','App\Controller\IndexController@index');
    },
    ['middleware' => [RequestMiddleware::class]]
);