<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\Auth\User;
use Donjan\Permission\Models\Role;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 用户控制器
 * Class UserController
 * @Controller(prefix="user")
 */
class UserController extends AbstractController
{
    /**
     * @Inject()
     * @var User
     */
    private $user;

    /**
     * 获取用户数据列表
     * @RequestMapping(path="list", methods="get")
     * @Middleware(RequestMiddleware::class)
     */
    public function index()
    {
        $userQuery = $this->user->newQuery();

        if (!empty($this->request->input('role_name'))) {
            $role_id = Role::query()->where('name', $this->request->input('role_name'))->value('id');
            if (!empty($role_id)) {
                $userQuery->from('sy_users as a');
                $userQuery->leftJoin('sy_model_has_roles as b', 'a.id', 'b.model_id');
                $userQuery->where('b.role_id', $role_id);
            }
        }
        $status = $this->request->input('status') ?? '';
        if (!empty($this->request->input('username'))) $userQuery->where('username', 'like', '%' . $this->request->input('username') . '%');
        if (!empty($this->request->input('desc'))) $userQuery->where('desc', 'like', '%' . $this->request->input('desc') . '%');
        if (strlen($status)) $userQuery->where('status', $status);
        $total = $userQuery->count();
        $userQuery = $this->pagingCondition($userQuery, $this->request->all());
        $data = $userQuery->get();

        foreach ($data as $key => $value) {
            $data[$key]['roleData'] = $value->getRoleNames();
        }

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * 添加用户
     * @RequestMapping(path="store", methods="post")
     * @Middleware(RequestMiddleware::class)
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'username' => $postData['username'] ?? '',
            'password' => $postData['password'] ?? '',
            'password_confirmation' => $postData['password_confirmation'] ?? '',
            'status' => $postData['status'] ?? 1,
            'mobile' => $postData['mobile'] ?? '',
            'roleData' => $postData['roleData'] ?? '',
        ];
        //配置验证
        $rules = [
            'username' => 'required|min:4|max:18|unique:users',
            'password' => 'required|confirmed:password_confirmation',
            'password_confirmation' => 'required',
            'status' => 'required',
            'mobile' => 'required',
            'roleData' => 'required|array',
        ];
        //错误信息
        $message = [
            'username.required' => '[username]缺失',
            'username.unique' => '该用户名已经存在',
            'password.required' => '[password]缺失',
            'confirm_password.required' => '[confirm_password]缺失',
            'roleData.required' => '[roleData]缺失',
            'roleData.array' => '[roleData]必须为数组',
            'username.min' => '[username]最少4位',
            'username.max' => '[username]最多18位',
            'password.confirmed' => '两次密码输入不一致',
            'mobile.required' => '手机号码不能为空',
        ];
        $this->verifyParams($params, $rules, $message);
        Db::beginTransaction();

        $user = new User();
        $user->username = $postData['username'];
        $user->password = md5($postData['password']);
        $user->status = $postData['status'] ?? '1';
        $user->avatar = $postData['avatar'] ?? 'http://landlord-res.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1,10) .'.png';
        $user->last_login = time();
        $user->last_ip = getClientIp($this->request);
        $user->creater = $postData['creater'] ?? '无';
        $user->desc = $postData['desc'] ?? '';
        $user->mobile = $postData['mobile'] ?? '';
        if (!$user->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加用户失败');

        //分配角色权限
        foreach ($postData['roleData'] as $key) {
            $user->assignRole($key);
        }
        DB::commit();

        return $this->successByMessage('添加用户成功');
    }

    /**
     * 获取单个用户的数据
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit($id)
    {
        $userInfo = User::getOneByUid($id);
        if (empty($userInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取用户信息失败');
        $userInfo['roleData'] = $userInfo->getRoleNames();
        unset($userInfo['roles']);

        return $this->success([
            'list' => $userInfo
        ]);
    }

    /**
     * 修改用户资料
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(int $id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();

        $params = [
            'status' => $postData['status'] ?? 1,
            'mobile' => $postData['mobile'] ?? '',
            'roleData' => $postData['roleData'] ?? '',
        ];
        //配置验证
        $rules = [
            'status'    => 'required',
            'mobile'    => 'required',
            'roleData'  => 'required|array',
        ];
        //错误信息
        $message = [
            'roleData.required' => '[roleData]缺失',
            'roleData.array' => '[roleData]必须为数组',
            'username.min' => '[username]最少4位',
            'username.max' => '[username]最多18位',
            'password.confirmed' => '两次密码输入不一致',
            'mobile.required' => '手机号码不能为空',
        ];

        // 表单验证
        $this->verifyParams($params, $rules, $message);

        //开始事务
        DB::beginTransaction();

        $user = User::getOneByUid($id);
        $user->status = $postData['status'] ?? '1';
        $user->avatar = $postData['avatar'] ?? 'http://landlord-res.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1,10) .'.png';
        $user->desc = $postData['desc'] ?? '';
        $user->mobile = $postData['mobile'] ?? '';
        if (!$user->save()) $this->throwExp(StatusCode::ERR_EXCEPTION,  '修改用户信息失败');

        //将所有角色移除并重新赋予角色
        DB::table('model_has_roles')
            ->where('model_id', $id)
            ->delete();
        foreach ($params['roleData'] as $key => $val) {
            $user->assignRole($val);
        }
        //提交事务
        DB::commit();

        //正确返回信息
        return $this->successByMessage('修改用户成功');
    }

    /**
     * 修改角色
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!User::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除用户成功');
    }
}
