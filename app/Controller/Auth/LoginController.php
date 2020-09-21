<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\AbstractController;
use App\Model\Auth\User;
use Donjan\Permission\Models\Role;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class LoginController
 * @Controller()
 */
class LoginController extends AbstractController
{
    /**
     * @RequestMapping(path="/login", methods="post")
     */
    public function index()
    {
        $username = $this->request->query('username');
        $password = $this->request->query('password');

        $userInfo = User::query()->where('username', $username)->first();

        return $this->success([
            'list' => $userInfo
        ]);
    }
}
