<?php

/**
 * 公共数组函数
 * create by linyiyuan
 */

if (!function_exists('cleanArrayNull')) {
    /**
     * 去除数组中含有null值
     * @param $arr
     * @return array
     */
    function cleanArrayNull($arr)
    {
      if (empty($arr)) return [];

      foreach ($arr as $key => $val) {
          if (is_null($val)) unset($arr[$key]);
      }

      return $arr;
    }
}


if (!function_exists('changeKeysToNum')) {
    /**
     * 把数组的键值转换为数字整形
     * @param array $arr 待转换的数组
     * @return array
     */
    function changeKeysToNum($arr, $except = []){
        if (!is_array($arr)) return $arr;

        $i = 0;
        $new = array();
        foreach ($arr as $key => $value) {
            if(is_array($value) && !in_array($key, $except)){
                $new[$i] = changeKeysToNum($value, $except);
            } else {
                $new[$key] = $value;
            }
            $i++;
        }

        return $new;
    }
}

if (!function_exists('getArraysByLimit')) {
    /**
     * 截取数组某部分
     * @param $arr
     * @param $cur
     * @param $size
     * @return mixed
     */
    function getArraysByLimit($arr, $cur, $size) {
        if (!is_array($arr)) return $arr;

        $cur_page = $cur ?? 1;
        $page_size = $size ?? 20;

        $offset = ($cur_page- 1) * $page_size;
        $limit  = $page_size;
        $newArr = array_slice($arr, $offset, $limit);

        return $newArr;
    }
}


if (!function_exists('arrayToStringArray')) {
    /**
     * 将数组转换成字符串形式的住宿
     * @param $arr
     * @return mixed
     */
    function arrayToStringArray($arr) {
        if (!is_array($arr) || empty($arr)) return $arr;
        $str = '[';
        foreach ($arr as $key => $val) {
            if (is_string($val)) {
                $str .= '"' . $val . '",';
            }else {
                $str .= $val . ',';
            }
        }
        $str = substr($str, 0, -1);
        $str .= ']';

        return $str;
    }
}

if (!function_exists('arrayToTree')) {
    /**
     * 一维无层级字段的数组根据父级ID转换为属性结构数组
     * @param array $data [需要转换的数据]
     * @param string $pkName [主键ID字段名称]
     * @param string $pIdName [父级ID字段名称]
     * @param string $childName [保存子级数据的字段]
     * @param bool $emptyChildren [当没有子级数据时是否显示空的子级字段]
     * @param string $rootId [根ID]
     * @return array
     */
    function arrayToTree($data, $pkName='id', $pIdName='parent_id', $childName='children', $emptyChildren=false, $rootId='') {
        $returnData = [];
        foreach($data as $v){
            if($v[$pIdName] == $rootId){
                $res = arrayToTree($data, $pkName, $pIdName, $childName, $emptyChildren, $v[$pkName]);
                if(!empty($res)){
                    $v[$childName] = $res;
                } else {
                    if ($emptyChildren) {
                        $v[$childName] = [];
                    }
                }
                $returnData[] = $v;
            }
        }
        return $returnData;
    }
}
