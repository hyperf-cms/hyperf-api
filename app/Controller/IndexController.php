<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Donjan\Permission\Models\Role;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class IndexController
 * @Controller()
 */
class IndexController extends AbstractController
{
    /**
     * @Inject()
     * @var UserService
     */
    public $userService = '';

    /**
     * @RequestMapping(path="/", methods="get")
     */
    public function index()
    {
        $role = Role::create(['name' => '管理员', 'description' => '']);

        return $this->success([
            $role
        ]);
    }

}
