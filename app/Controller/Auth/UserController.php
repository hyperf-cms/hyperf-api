<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\StatusCode;
use App\Constants\UploadCode;
use App\Controller\AbstractController;
use App\Http\Service\Auth\UserService;
use App\Http\Service\Common\UploadService;
use App\Model\Auth\User;
use Donjan\Permission\Models\Role;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\Filesystem;

/**
 * 用户控制器
 * Class UserController
 * @Controller(prefix="setting/user_module/user")
 */
class UserController extends AbstractController
{
    /**
     * @Inject()
     * @var User
     */
    private $user;

    /**
     * @Inject()
     * @var Filesystem
     */
    private $filesystem;

    /**
     * 获取用户数据列表
     * @RequestMapping(path="list", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     */
    public function index()
    {
        $userQuery = $this->user->newQuery();

        if (!empty($this->request->input('role_name'))) {
            $role_id = Role::query()->where('name', $this->request->input('role_name'))->value('id');
            if (!empty($role_id)) {
                $userQuery->from('users as a');
                $userQuery->leftJoin('model_has_roles as b', 'a.id', 'b.model_id');
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
     * @Middleware(PermissionMiddleware::class)
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
        $user->email = $postData['email'] ?? '';
        $user->sex = $postData['sex'] ?? 0;
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
    public function edit(int $id)
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
     * 获取当前登陆用户的数据
     * @RequestMapping(path="profile", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function profile()
    {
        $userInfo = UserService::getInstance()->getUserInfoByToken();
        $roleArr = '';
        foreach ($userInfo->roles as $key => $value) {
            $roleArr .= $value['description'] . ' ';
        }
        $userInfo->last_login = date('Y-m-d H:i:s', $userInfo->last_login);

        return $this->success([
            'list' => $userInfo,
            'role' => $roleArr
        ]);
    }

    /**
     * 获取当前登陆用户的数据
     * @param int $id
     * @RequestMapping(path="profile/{id}", methods="put")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function profileEdit($id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();

        $user = User::getOneByUid($id);
        $user->email = $postData['email'] ?? '';
        $user->desc = $postData['desc'] ?? '';
        $user->mobile = $postData['mobile'] ?? '';
        $user->sex = $postData['sex'] ?? '';
        if (!$user->save()) $this->throwExp(StatusCode::ERR_EXCEPTION,  '修改用户信息失败');

        //正确返回信息
        return $this->successByMessage('修改用户成功');
    }

    /**
     * 上传用户头像
     * @RequestMapping(path="upload_avatar", methods="post")
     * @Middleware(RequestMiddleware::class)
     */
    public function uploadAvatar()
    {
        $params = [
            'savePath' => $this->request->input('save_path'),
            'file' => $this->request->file('file'),
            'id' => $this->request->input('id'),
        ];
        //配置验证
        $rules = [
            'id' => 'required',
            'savePath' => 'required',
            'file' => 'required |file',
        ];
        $message = [
            'id.required' => '[id]缺失',
            'savePath.required' => '[savePath]缺失',
            'file.required' => '[name]缺失',
            'file.file' => '[file] 参数必须为文件类型',
        ];
        $this->verifyParams($params, $rules, $message);

        if ($params['file']->getSize() > 30000000) $this->throwExp(UploadCode::ERR_UPLOAD_SIZE, '上传图片尺寸过大');

        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid())  . '.' . 'jpg';
        $uploadPath = $params['savePath'] . '/' . $fileName;

        //外网访问的路径
        $fileUrl = env('OSS_URL') . $uploadPath;

        $stream = fopen($params['file']->getRealPath(), 'r+');
        $this->filesystem->writeStream(
            $uploadPath,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        $user = User::getOneByUid($params['id']);
        $user->avatar = $fileUrl;
        $user->save();
        if (!$user->save()) $this->throwExp(StatusCode::ERR_VALIDATION, '修改用户头像失败');

        return $this->success([
            'fileName' => $fileName,
            'url' => $fileUrl
        ], '上传图片成功');
    }


    /**
     * 修改用户资料
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
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
        $user->sex = $postData['sex'] ?? '';
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
     * 删除用户
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!User::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除用户成功');
    }

    /**
     * 修改用户密码
     * @RequestMapping(path="reset_password", methods="post")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function resetPassword()
    {
        $postData = $this->request->all() ?? [];
        $params = [
            'id' => $postData['id'],
            'old_password' => $postData['old_password'] ?? '',
            'new_password' => $postData['new_password'] ?? '',
            'confirm_password' => $postData['confirm_password'] ?? '',
        ];
        //配置验证
        $rules = [
            'id' => 'required',
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ];
        $message = [
            'id.required' => '[id]缺失',
            'old_password.required' => '[old_password]缺失',
            'new_password.required' => '[new_password]缺失',
            'confirm_password.required' => '[confirm_password]缺失',
        ];

        $this->verifyParams($params, $rules, $message);
        $userInfo = User::getOneByUid($params['id']);

        if (empty($userInfo)) $this->throwExp(400, '账号不存在');
        if (md5($params['old_password']) != $userInfo['password']) $this->throwExp(StatusCode::ERR_EXCEPTION, '输入密码与原先密码不一致');
        if (md5($params['new_password']) != md5($params['confirm_password'])) $this->throwExp(StatusCode::ERR_EXCEPTION, '两次密码输入不一致');

        $userInfo->password  = md5($params['new_password']);
        $updateRes = $userInfo->save();

        if (!$updateRes) $this->throwExp(400, '修改密码失败');

        return $this->success([], '修改密码成功');
    }

}
