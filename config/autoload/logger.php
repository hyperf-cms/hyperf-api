<?php

declare(strict_types=1);


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

$appEnv = env('APP_ENV', 'dev');
$handlers = [];
$handlers = [
    // info、waring、notice日志等
    [
        'class' => App\Foundation\Handler\LogFileHandler::class,
        'constructor' => [
            'filename' => BASE_PATH . '/runtime/logs/hyperf/hyperf.log',
            'level' => Monolog\Logger::INFO,
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ]
    ],
    // debug日志
    [
        'class' => App\Foundation\Handler\LogFileHandler::class,
        'constructor' => [
            'filename' => BASE_PATH . '/runtime/logs/hyperf_debug/hyperf-debug.log',
            'level' => Monolog\Logger::DEBUG,
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ]
    ],
    // error日志
    [
        'class' => App\Foundation\Handler\LogFileHandler::class,
        'constructor' => [
            'filename' => BASE_PATH . '/runtime/logs/hyperf_error/hyperf-error.log',
            'level' => Monolog\Logger::ERROR,
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ]
    ],
];
$logConfig = [

    'default' => [
        // 配置多个handler，根据每个handel产生日志
        'handlers' => $handlers
    ],

    'code_debug' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler ::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/code_debug.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'request_log' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler ::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/request_log/request.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'response_log' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler ::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/response_log/response.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'job_log' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler ::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/job_log/job.log',
                'level' => Monolog\Logger::ERROR,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'sql_log' => [
            'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler ::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/sql_log/sql.log',
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ]
];


return $logConfig;
