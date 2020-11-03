<?php
namespace App\Foundation\Traits;

/**
 * 查询基类
 * Trait QueryTrait
 * @package App\Foundation\Traits
 */
trait QueryTrait
{
    /**
     * 处理分页条件
     *
     * @param $query
     * @param $params
     * @return mixed
     */
    public function pagingCondition($query, $params)
    {
        $cur_page   = $params['cur_page'] ?? 1;
        $page_size  = $params['page_size'] ?? 20;

        $offset = ($cur_page- 1) * $page_size;
        $limit  = $page_size;
        $query = $query->offset($offset)->limit($limit);

        return $query;
    }
}
