<?php

/**
 * 公共函数
 * create by linyiyuan
 */
if (!function_exists('checkFileExists')) {
    /**
     * 检查文件是否存在
     *
     * @param $file
     * @return bool
     */
    function checkFileExists($file)
    {
        // 远程文件
        if (strtolower(substr($file, 0, 5)) == 'https') {
            // 远程文件
            $header = get_headers($file, true);
            return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));

        } elseif (strtolower(substr($file, 0, 4)) == 'http') {
            // 远程文件
            $header = get_headers($file, true);
            return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));
        } else {
            // 本地文件
            return file_exists($file);
        }
    }

}

if (!function_exists('getRandStr')) {
    /**
     * 随机生成字符串
     *
     * @param int $len
     * @return string
     */
    function getRandStr($len)
    {
        $chars = array(
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K",
            "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V",
            "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g",
            "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
            "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3",
            "4", "5", "6", "7", "8", "9"
        );

        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }

        return $output;
    }
}

if (!function_exists('byteConversion')) {
    /**
     * 将字节大小转换相对应单位
     *
     * @param int $num
     * @return string
     */
    function byteConversion($num)
    {
        $p      = 0;
        $format = 'bytes';
        if ($num > 0 && $num < 1024) {
            $p = 0;
            return number_format($num) . ' ' . $format;
        }
        if ($num >= 1024 && $num < pow(1024, 2)) {
            $p      = 1;
            $format = 'KB';
        }
        if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
            $p      = 2;
            $format = 'MB';
        }
        if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
            $p      = 3;
            $format = 'GB';
        }
        if ($num >= pow(1024, 4) && $num < pow(1024, 5)) {
            $p      = 3;
            $format = 'TB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 3) . ' ' . $format;
    }
}

if (!function_exists('getClientIp')) {
    /**
     * 获取客户端请求的IP
     *
     * @return mixed|string
     */
    function getClientIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else if(!empty($_SERVER["REMOTE_ADDR"]))
            $cip = $_SERVER["REMOTE_ADDR"];
        else
            $cip = "";
        $cip = str_replace("::ffff:", "", $cip);	//去除冗余字符
        return $cip;
    }
}

if (!function_exists('objToArray')) {
    /**
     * 对象转数组
     *
     * @param string $data
     * @return array|mixed
     */
    function objToArray($data = ''){
        if (empty($data)) return [];
        return json_decode(json_encode($data),true);
    }
}


if (!function_exists('p')) {
    // 传递数据以易于阅读的样式格式化后输出
    function p($data)
    {
        $array = [];
        // 定义样式
        echo '<pre style="display: block;font-size: 12px;line-height: 1.42857;color: #13db1d;word-break: break-all;word-wrap: break-word;background-color: #000000;border: 1px solid #CCC;border-radius: 4px;">';
        foreach($data as $key=>$value) {
            $array[$key] = json_decode(json_encode($value), true);
        }
        print_r($array);
        echo '</pre>';

    }
}

if (!function_exists('pd')) {
    // 传递数据以易于阅读的样式格式化后输出并终止
    function pd($data)
    {
        p($data);
        die;
    }
}
