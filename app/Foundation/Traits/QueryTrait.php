<?php
namespace App\Foundation\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API接口基类，负责数据返回处理
 * Author linyiyuan
 * Trait ApiTrait
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

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
