<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Model\System\DictData;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 字典数据控制器
 * Class IndexController
 * @Controller(prefix="setting/system_module/dict_data")
 */
class DictDataController extends AbstractController
{
    /**
     * @Inject()
     * @var DictData
     */
    private $dictData;

    /**
     * 获取字典类型列表
     * @RequestMapping(path="list", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     */
    public function index()
    {
        $dictDataQuery = $this->dictData->newQuery();
        $status = $this->request->input('status');
        $dictLabel = $this->request->input('dict_label') ?? '';
        $dictType = $this->request->input('dict_type') ?? '';

        if (!empty($dictLabel)) $dictDataQuery->where('dict_label', 'like', '%' . $dictLabel . '%');
        if (!empty($dictType)) $dictDataQuery->where('dict_type', 'like', '%' . $dictType . '%');
        if (strlen($status) > 0) $dictDataQuery->where('status', $status);

        $total = $dictDataQuery->count();
        $dictDataQuery = $this->pagingCondition($dictDataQuery, $this->request->all());
        $data = $dictDataQuery->get();

        return $this->success([
            'list' => $data,
            'total' => $total,
        ]);
    }

    /**
     * 添加字典数据
     * @RequestMapping(path="store", methods="post")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     */
    public function store()
    {
        $postData = $this->request->all();
        $params = [
            'dict_type' => $postData['dict_type'] ?? '',
            'dict_label' => $postData['dict_label'] ?? '',
            'dict_value' => $postData['dict_value'] ?? '',
            'dict_sort' => $postData['dict_sort'] ?? 1,
            'status' => $postData['status'] ?? 1,
            'remark' => $postData['remark'] ?? '',
        ];
        //配置验证
        $rules = [
            'dict_type' => 'required',
            'dict_label' => 'required',
            'dict_value' => 'required',
        ];
        //错误信息
        $message = [
            'dict_type.required' => '[dict_type]缺失',
            'dict_label.required' => '[dict_label]缺失',
            'dict_value.required' => '[dict_value]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $dictDataQuery = new DictData();
        $dictDataQuery->dict_type = $params['dict_type'];
        $dictDataQuery->dict_label = $params['dict_label'];
        $dictDataQuery->dict_value = $params['dict_value'];
        $dictDataQuery->dict_sort = $params['dict_sort'];
        $dictDataQuery->status = $params['status'];
        $dictDataQuery->remark = $params['remark'];
        $dictDataQuery->created_at = date('Y-m-d, H:i:s');
        $dictDataQuery->updated_at = date('Y-m-d, H:i:s');
        if (!$dictDataQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '添加字典数据错误');

        return $this->successByMessage('添加字典数据成功');
    }

    /**
     * 获取单个字典数据信息
     * @param int $id
     * @RequestMapping(path="edit/{id}", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function edit(int $id)
    {
        $dictDataInfo = DictData::findById($id);
        if (empty($dictDataInfo)) $this->throwExp(StatusCode::ERR_USER_ABSENT, '获取字典信息失败');

        return $this->success([
            'list' => $dictDataInfo
        ]);
    }

    /**
     * 修改字典数据信息
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
            'dict_type' => $postData['dict_type'] ?? '',
            'dict_label' => $postData['dict_label'] ?? '',
            'dict_value' => $postData['dict_value'] ?? '',
            'dict_sort' => $postData['dict_sort'] ?? 1,
            'status' => $postData['status'] ?? 1,
            'remark' => $postData['remark'] ?? '',
        ];
        //配置验证
        $rules = [
            'dict_type' => 'required',
            'dict_label' => 'required',
            'dict_value' => 'required',
        ];
        //错误信息
        $message = [
            'dict_type.required' => '[dict_type]缺失',
            'dict_label.required' => '[dict_label]缺失',
            'dict_value.required' => '[dict_value]缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $dictDataQuery = DictData::findById($id);
        $dictDataQuery->dict_type = $params['dict_type'];
        $dictDataQuery->dict_label = $params['dict_label'];
        $dictDataQuery->dict_value = $params['dict_value'];
        $dictDataQuery->dict_sort = $params['dict_sort'];
        $dictDataQuery->status = $params['status'];
        $dictDataQuery->remark = $params['remark'];
        $dictDataQuery->updated_at = date('Y-m-d, H:i:s');
        if (!$dictDataQuery->save()) $this->throwExp(StatusCode::ERR_EXCEPTION, '修改字典数据错误');

        return $this->successByMessage('修改字典数据成功');
    }

    /**
     * 删除字典数据
     * @param int $id
     * @RequestMapping(path="destroy/{id}", methods="delete")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function destroy(int $id)
    {
        if (!intval($id)) $this->throwExp(StatusCode::ERR_VALIDATION, '参数错误');
        if (!DictData::destroy($id)) $this->throwExp(StatusCode::ERR_EXCEPTION, '删除失败');

        return $this->successByMessage('删除字典数据成功');
    }

}