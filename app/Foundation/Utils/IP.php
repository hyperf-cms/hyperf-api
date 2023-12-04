<?php

declare(strict_types=1);

namespace App\Foundation\Utils;

use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use ip2region\XdbSearcher;
use PHPMailer\PHPMailer\PHPMailer;


/**
 * IP工具类
 * Class IP
 * @package App\Foundation\Utils
 * @Author YiYuan-Lin
 * @Date: 2023/11/07
 */
class IP
{
    /**
     * 邮件类实例化
     * @var
     */
    static private $mail;

    /**
     * Mail类对象
     * @var
     */
    static public $obj = null;

    /**
     * 初始化
     * @Author YiYuan
     * @Date 2023/11/7
     * @return XdbSearcher|string
     */
    public static function instance()
    {
        //获取数据
        $xdb = BASE_PATH . '/config/autoload/asset/ip2region.xdb';

        try {
            if (!is_null(self::$obj)) return self::$obj;

            $cBuff = XdbSearcher::loadContentFromFile($xdb);
            if (null === $cBuff) throw new \Exception('failed to load content buffer from');

            self::$obj = XdbSearcher::newWithBuffer($cBuff);
            return self::$obj;
        }catch (\Exception $e) {
            Log::codeDebug()->info('获取IP位置失败:' . $e->getMessage());
            return null;
        }
    }

    /**
     * 查询位置
     * @Author YiYuan
     * @Date 2023/11/7
     * @param string $ip
     * @return string
     * @throws \Exception
     */
    public static function search(string $ip) : string
    {
        return self::instance()->search($ip) ?? '未知';
    }

    /**
     * 省份
     * @Author YiYuan
     * @Date 2023/11/7
     * @param string $ip
     * @return mixed|string
     * @throws \Exception
     */
    public static function province(string $ip)
    {
        $province = explode('|', self::search($ip))[2] ?? '未知';

        if (empty($province)) return '未知';
        return $province;
    }

    /**
     * 国家
     * @Author YiYuan
     * @Date 2023/11/7
     * @param string $ip
     * @return mixed|string
     * @throws \Exception
     */
    public static function country(string $ip)
    {
        return explode('|', self::search($ip))[0] ?? '未知';
    }

    /**
     * 城市
     * @Author YiYuan
     * @Date 2023/11/7
     * @param string $ip
     * @return mixed|string
     * @throws \Exception
     */
    public static function city(string $ip)
    {
        $city = explode('|', self::search($ip))[3] ?? '未知';

        if (empty($city)) return '未知';
        return $city;
    }
}