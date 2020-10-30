<?php

namespace App\Service;

use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Context;

class IndexService
{
    use Singleton;

    public $params = [];

    public function test()
    {
        return Context::get('params') ?? [];
    }
}