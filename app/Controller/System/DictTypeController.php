<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Foundation\Annotation\Explanation;
use App\Model\System\DictData;
use App\Model\System\DictType;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 字典类型控制器
 * Class IndexController
 * @Controller(prefix="setting/system_module/dict_type")
 */
class DictTypeController extends AbstractController
{
    /**
     * @Inject()
     * @var DictType
     */
    private $dictType;

    /**
     * 获取字典类型列表
     * @RequestMapping(path="list", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function index()
    {
        $dictTypeQuery = $this->dictType->newQuery();
        $status = $this->request->input('status') ?? '';
        $dictName = $this->request->input('dict_name') ?? '';
        $dictType = $this->request->input('dict_type') ?? '';

        if (!empty($dictName)) $dictTypeQuery->where('dict_name', 'like', '%' . $dictName . '%');
        if (!empty($dictType)) $dictTypeQuery->where('dict_type', 'like', '%' . $dictType . '%');
        if (strlen($status) > 0) $dictTypeQuery->where('status', $status);

        $total = $dictTypeQuery->count();
        $dictTypeQuery = $this->pagingCondition($dictTypeQuery, $this->request->all());
        $data = $dictTypeQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * @Explanation(content="添加字典类型")
     * @RequestMapping(path="store", methods="post")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'dict_name' => $postData['dict_name'] ?? '',
            'dict_type' => $postData['dict_type'] ?? '',
            'status' => $postData['status'] ?? 1,
            'remark' => $postData['remark'] ?? '',
        ];
        //配置验证
        $rules = [
            'dict_name' => 'required|min:2|max:60|',
            'dict_type' => 'required|unique:dict_type',
        ];
        //错误信息
        $message = [
            'dict_name.required' => '[dict_name]缺失',
            'dict_name.min' => '[dict_name]最少2位',
            'dict_name.max' => '[dict_name]最多60位',
            'dict_type.required' => '[dict_type]缺失',
            'dict_type.unique' => '[dict_type]已经存在',
        ];
        $this->verifyParams($params, $rules, $message);

        $dictTypeQuery = new DictType();
        $dictTypeQuery->dict_name = $params['dict_name'];
        $dictTypeQuery->dict_type = $params['dict_type'];
        $dictTypeQuery->status = $params['status'];
        $dictTypeQuery->remark = $params['remark'];
        $dictTypeQuery->created_at = date('Y-m-d, H:i:s');
        $dictTypeQuery->updated_at = date('Y-m-d, H:i:s');
        if (!$dictTypeQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加字典类型错误');

        return $this->successByMessage('添加字典类型成功');
    }

    /**
     * 获取单个字典类型信息
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $dictTypeInfo = DictType::findById($id);
        if (empty($dictTypeInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取字典信息失败');

        return $this->success([
            'list' => $dictTypeInfo
        ]);
    }

    /**
     * @Explanation(content="修改字典类型资料")
     * @param int $id
     * @RequestMapping(path="update/{id}", methods="put")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(int $id)
    {
        if (empty($id)) $this->throwExp(StatusCode::ERR_VALIDATION, 'ID 不能为空');
        $postData = $this->request->all();
        $params = [
            'dict_name' => $postData['dict_name'] ?? '',
            'dict_type' => $postData['dict_type'] ?? '',
            'status' => $postData['status'] ?? 1,
            'remark' => $postData['remark'] ?? '',
        ];
        $rules = [
            'dict_name' => 'required|min:4|max:18|',
            'dict_type' => 'required',
        ];
        $message = [
            'dict_name.required' => '[dict_name]缺失',
            'dict_name.min' => '[dict_name]最少4位',
            'dict_name.max' => '[dict_name]最多18位',
            'dict_type.required' => '[dict_type]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $dictTypeQuery = DictType::findById($id);
        $dictTypeQuery->dict_name = $params['dict_name'];
        $dictTypeQuery->dict_type = $params['dict_type'];
        $dictTypeQuery->status = $params['status'];
        $dictTypeQuery->remark = $params['remark'];
        $dictTypeQuery->updated_at = date('Y-m-d, H:i:s');
        if (!$dictTypeQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改字典类型错误');

        return $this->successByMessage('修改字典类型成功');
    }

    /**
     * @Explanation(content="删除字典类型")
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middlewares({
     *     @Middleware(RequestMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!DictType::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除字典类型成功');
    }

}