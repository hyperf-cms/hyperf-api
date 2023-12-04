<?php

declare(strict_types=1);

namespace App\Foundation\Utils;
use App\Constants\StatusCode;
// 导入要请求接口对应的Request类
// 导入要请求接口对应的Request类
// 导入可选配置类
use App\Exception\Handler\BusinessException;
use App\Foundation\Facades\Log;
use TencentCloud\Sms\V20210111\SmsClient;
// 导入要请求接口对应的Request类
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
// 导入要请求接口对应的Request类
use TencentCloud\Cvm\V20170312\Models\DescribeInstancesRequest;
use TencentCloud\Cvm\V20170312\Models\Filter;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;
// 导入可选配置类
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Profile\ClientProfile;


/**
 * 短信工具类
 * Class Sms
 * @package App\Foundation\Utils
 * @Author BaiHong
 * @Date: 2022/11/21
 */
class Sms
{
    /**
     * Date 2022/12/13
     * Author YiYuan
     * 发送短信工具类的参数
     * @param string $code 待发送的验证码
     * @param string $minute 验证码有效时间
     * @param string $mobile 下发手机号码
     * @return bool
     */
    public static function sendMsgByCode(string $code, string $minute, string $mobile, string $msgTemplate = '')
    {
        $config = [];
        $config['template_id'] = $msgTemplate;
        $config['template_param_set'][] = $code;
        $config['template_param_set'][] = $minute;
        $config['phone_number_set'][] = $mobile;
        $res = self::sendSms($config);

        if (empty($res)) return false;

        return true;
    }

    /**
     * 发送短信工具类的参数
     * @param array $config
     * config['sign_name'] string 已审核通过的签名
     * config['template_id'] string 已审核通过的模板 ID
     * config['template_param_set'] array of string 模板参数 模板参数的个数需要与 templateId 对应模板的变量个数保持一致，若无模板参数，则设置为空
     * config['phone_number_set'] array of string 下发手机号码 +[国家或地区码][手机号] 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号
     * @return bool
     */
    static function sendSms(array $config){

        try {
            /**
             * 实例化一个认证对象，入参需要传入腾讯云账户密钥对secretId，secretKey。
             * 这里采用的是从环境变量读取的方式，需要在环境变量中先设置这两个值。
             */
            $cred = new Credential(getenv("TENCENTCLOUD_SECRET_ID"), getenv("TENCENTCLOUD_SECRET_KEY"));

            // 实例化要请求产品(sms)的client对象
            $client = new SmsClient($cred, "ap-guangzhou");

            // 实例化一个 sms 发送短信请求对象,每个接口都会对应一个request对象
            $req = new SendSmsRequest();

            /**
             * 填充请求参数,这里request对象的成员变量即对应接口的入参
             * 短信应用ID: 短信SdkAppId在 [短信控制台] 添加应用后生成的实际SdkAppId，示例如1400006666
             */
            $req->SmsSdkAppId = getenv("SMS_SDK_APPID") ?? '';

            $req->SignName = $config['sign_name'] ?? '宾果网络科技';

            $req->TemplateId = $config['template_id'] ?? '';

            $req->TemplateParamSet = $config['template_param_set'] ?? [];

            $req->PhoneNumberSet = $config['phone_number_set'] ?? [];

            // 通过client对象调用SendSms方法发起请求。注意请求方法名与请求对象是对应的
            // 返回的resp是一个SendSmsResponse类的实例，与请求对象对应
            $resp = $client->SendSms($req);
            $resArr = json_decode($resp->toJsonString(),true);

            if (!empty($resArr) && !empty($resArr['SendStatusSet']) && !empty($resArr['SendStatusSet'][0]) && is_array($resArr['SendStatusSet'][0])){
                if ($resArr['SendStatusSet'][0]['Code'] == 'Ok') return true;
                Log::logicalError()->info(json_encode($resArr));
                return false;
            }

            return false;
        }
        catch(TencentCloudSDKException $e) {
            Throw new BusinessException(500, $e->getMessage());
        }
    }
}

