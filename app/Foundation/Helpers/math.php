<?php
/**
 * 数学相关函数
 * create by linyiyuan
 */

if (!function_exists('base62_encode')) {
    /**
     * Convert a 10 base numeric string to a 62 base string
     *
     * @param  int $value
     * @return string
     */
    function base62_encode($value)
    {
        return to_base($value, 62);
    }
}

if (!function_exists('base62_decode')) {
    /**
     * Convert a string from base 62 to base 10 numeric string
     *
     * @param  string $value
     * @return int
     */
    function base62_decode($value)
    {
        return to_base10($value, 62);
    }
}

if (!function_exists('to_base')) {

    /**
     * Convert a numeric string from base 10 to another base.
     *
     * @param $value  decimal string
     * @param int $b base , max is 62
     * @return string
     */
    function to_base($value, $b = 62)
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $r = $value % $b;
        $result = $base[$r];
        $q = floor($value / $b);

        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $result = $base[$r] . $result;
        }

        return $result;
    }
}

if (!function_exists('to_base10')) {
    /**
     * Convert a string from a given base to base 10.
     * @return string
     */
    function to_base10($value, $b = 62)
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $limit = strlen($value);
        $result = strpos($base, $value[0]);

        for ($i = 1; $i < $limit; $i++) {
            $result = $b * $result + strpos($base, $value[$i]);
        }

        return $result;
    }
}

if (!function_exists('md5_rand')) {
    /**
     * Convert a string from a given base to base 10.
     *
     * @return string
     */
    function md5_rand()
    {
        $time = time();
        $numRand = getRandStr(10);

        return strtoupper(md5($time . $numRand));
    }
}

if (!function_exists('code62')) {
    /**
     * 生成短网址
     *
     * @param $x
     * @return string
     */
    function code62($x)
    {
        $show = '';
        while($x > 0){
            $s = $x % 62;
            if ($s > 35){
                $s = chr($s + 61);
            }elseif($s > 9 && $s <= 35){
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x / 62);
        }
        return $show;
    }
}

if (!function_exists('short_url')) {
    /**
     * 生成短链
     *
     * @param $url
     * @return string
     */
    function short_url($url)
    {
        $url = crc32($url);
        $result = sprintf("%u",$url);

        return code62($result);
    }
}

if (!function_exists('calc_float')) {
    function calc_float($type, $m, $n, $scale = 2) {
        $result = '';

        switch ($type) {
            case 'add':
                $result = bcadd($m, $n, $scale);
                break;
            case 'sub':
                $result = bcsub($m, $n, $scale);
                break;
            case 'mul':
                $result = bcmul($m, $n, $scale);
                break;
            case 'div':
                $result = bcdiv($m, $n, $scale);
                break;
        }

        return $result;
    }
}

if (!function_exists('generate_rand_id')) {
    function generate_rand_id() {
        return getRandStr(8) . '-' . getRandStr(4) . '-' . getRandStr(4) . '-' . getRandStr(4) . '-' . getRandStr(12);
    }
}

