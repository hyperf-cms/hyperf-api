<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
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
        $user_id = $this->request->input('user_id');
        $userInfo = $this->userService->getInfoById($user_id);

        return $this->success([
               'list' => $userInfo,
           ]);

    }

}
