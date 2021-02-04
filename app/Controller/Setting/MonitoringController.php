<?php

declare(strict_types=1);

namespace App\Controller\Setting;

use App\Controller\AbstractController;
use App\Http\Service\Setting\ServerMonitorService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\RequestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * 监控控制器
 * Class IndexController
 * @Controller(prefix="setting/monitoring_module")
 */
class MonitoringController extends AbstractController
{
    /**
     * 获取服务监控
     * @RequestMapping(path="server", methods="get")
     * @Middleware(RequestMiddleware::class)
     * @Middleware(PermissionMiddleware::class)
     */
    public function server()
    {

        $cpuInfo = ServerMonitorService::getInstance()->getCpuInfo();
        return $cpuInfo;
        //NETWORK//UPTIME
        if(false===($str=@file("/proc/uptime")))return false;
        $str = explode(' ',implode("",$str));
        $str=trim($str[0]);
        $min=$str/60;
        $hours=$min/60;
        $days=floor($hours/24);
        $hours=floor($hours-($days*24));
        $min=floor($min-($days*60*24)-($hours*60));
        if($days!==0)$res['uptime']=$days."天";
        if($hours!==0)$res['uptime'].=$hours."小时";
        $res['uptime'].=$min."分钟";
        //MEMORY
        if(false===($str=@file("/proc/meminfo")))return false;
        $str=implode("",$str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s",$str,$buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s",$str,$buffers);
        $res['mem_total']=round($buf[1][0]/1024,2);
        $res['mem_free']=round($buf[2][0]/1024,2);
        $res['mem_buffers']=round($buffers[1][0]/1024,2);
        $res['mem_cached']=round($buf[3][0]/1024,2);
        $res['mem_used']=$res['mem_total']-$res['mem_free'];
        $res['mem_percent']=(floatval($res['mem_total'])!=0)?round($res['mem_used']/$res['mem_total']*100,2):0;
        $res['mem_real_used']=$res['mem_total']-$res['mem_free']-$res['mem_cached']-$res['mem_buffers'];//真实内存使用
        $res['mem_real_free']=$res['mem_total']-$res['mem_real_used'];//真实空闲
        $res['mem_real_percent']=(floatval($res['mem_total'])!=0)?round($res['mem_real_used']/$res['mem_total']*100,2):0;//真实内存使用率
        $res['mem_cached_percent']=(floatval($res['mem_cached'])!=0)?round($res['mem_cached']/$res['mem_total']*100,2):0;//Cached内存使用率
        $res['swap_total']=round($buf[4][0]/1024,2);
        $res['swap_free']=round($buf[5][0]/1024,2);
        $res['swap_used']=round($res['swap_total']-$res['swap_free'],2);
        $res['swap_percent']=(floatval($res['swap_total'])!=0)?round($res['swap_used']/$res['swap_total']*100,2):0;
//LOADAVG
        if(false===($str=@file("/proc/loadavg"))) return false;
        $str=explode(' ',implode("",$str));
        $str=array_chunk($str,4);
        $res['load_avg']=implode('',$str[0]);
        return$res;




        return getrusage();
    }
}
