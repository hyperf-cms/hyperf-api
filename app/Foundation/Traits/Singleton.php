<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:56
 */

namespace App\Foundation\Traits;

use Hyperf\Context\Context;

/**
 * 构建单例基类
 * Trait Singleton
 * @package App\Foundation\Traits
 */
trait Singleton
{
    protected $instanceKey;

    public static function getInstance($params = [], $refresh = false)
    {
        $key = get_called_class();
        $instance = null;
        if (Context::has($key)) {
            $instance = Context::get($key);
        }

        if ($refresh || is_null($instance) || ! $instance instanceof static) {
            $instance = new static(...$params);
            Context::set($key, $instance);
        }

        return $instance;
    }
}