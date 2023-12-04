<?php
/**
 * 异或加密/解密
 */
if (!function_exists('char_code_at')) {
    /**
     * 等同于js的charCodeAt()
     * @param string $str
     * @return array
     */
    function char_code_at(string $str) : array
    {
        $result = [];
        for($i = 0, $l = mb_strlen($str, 'utf-8'); $i < $l; ++$i) {
            $result[] = uniord(mb_substr($str, $i, 1, 'utf-8'));
        }
        return $result;
    }
}

if (!function_exists('uniord')) {
    /**
     * @param string $str
     * @param bool $from_encoding
     * @return int|mixed
     */
    function uniord(string $str, bool $from_encoding = false){
        $from_encoding = $from_encoding ? $from_encoding : 'UTF-8';
        if (strlen($str) == 1) return ord($str);
        $str = mb_convert_encoding($str, 'UCS-4BE', $from_encoding);
        $tmp = unpack('N', $str);
        return $tmp[1];
    }
}

if (!function_exists('xor_enc')) {
    /**
     * 异或加密/解密
     * @param string $str
     * @return string
     */
    function xor_enc(string $str) : string {
        $cryTxt = '';
        $key = config('global.xor_key');

        $keyLen = strlen($key);
        $strLen = mb_strlen($str);
        for ($i = 0; $i < $strLen; $i ++ ) {
            $k = ord($str[$i]) ^ ord($key[$i % $keyLen]);
            $cryTxt .= mb_convert_encoding(chr($k), 'UTF-8', 'UTF-8');
        }

        return $cryTxt;
    }
}
