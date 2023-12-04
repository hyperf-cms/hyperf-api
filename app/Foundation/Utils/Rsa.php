<?php

namespace App\Foundation\Utils;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use Cron\CronExpression;

/**
 * RSA非对称加密解密操作类
 * Class Rsa
 * @package App\Foundation\Utils
 */
class Rsa
{
    /**
     * 获取秘钥
     * @return false|resource
     */
    private static function getPrivateKey()
    {
        $dir = './runtime/key/rsa_private_key.pem';
        $content = file_get_contents($dir);

        return openssl_pkey_get_private($content);

    }

    /**
     * 获取公钥
     * @return false|resource
     */
    private static function getPublicKey()
    {
        $dir = './runtime/key/rsa_public_key.pem';
        $content = file_get_contents($dir);

        return openssl_pkey_get_public($content);
    }

    /**
     * 秘钥加密
     * @param string $data
     * @return string|null
     */
    public static function privEncrypt(string $data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return string|null
     */
    public static function publicEncrypt(string $data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data,$encrypted,self::getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 秘钥解密
     * @param string $encrypted
     * @return mixed|null
     */
    public static function privDecrypt(string $encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return mixed|null
     */
    public static function publicDecrypt(string $encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? $decrypted : null;
    }

}