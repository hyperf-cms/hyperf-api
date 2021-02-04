<?php
namespace App\Http\Service\Setting;

use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;


class ServerMonitorService extends BaseService
{
    use Singleton;


    public function getCpuInfo()
    {
        // 获取CPU信息
        $cpuInfo = [];
        if (false === ($str = @file("/proc/cpuinfo"))) return false;
        $str = implode("", $str);
        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
        @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $boGomIps);
        if(false !== is_array($model[1])){
            $cpuInfo['cpu']['num'] = sizeof($model[1]);
            $cpuInfo['cpu']['num_text'] = str_replace(array(1,2,4,8,16),array('单','双','四','八','十六'),$cpuInfo['cpu']['num']).'核';
            for($i = 0; $i < $cpuInfo['cpu']['num']; $i++){
                $cpuInfo['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';
                $cpuInfo['cpu']['mhz'][] = $mhz[1][$i];
                $cpuInfo['cpu']['cache'][] = $cache[1][$i];
                $cpuInfo['cpu']['bogomips'][] = $boGomIps[1][$i];
            }
            $x1 = ($res['cpu']['num' ]== 1) ? '' : '×' . $res['cpu']['num'];
            $mhz[1][0] = '|频率:' . $mhz[1][0];
            $cache[1][0] = '|二级缓存:' . $cache[1][0];
            $boGomIps[1][0] = '|Bogomips:' . $boGomIps[1][0];
            $cpuInfo['cpu']['model'][] = $model[1][0].$mhz[1][0] . $cache[1][0] . $boGomIps[1][0] . $x1;
            if(false !== is_array($res['cpu']['model'])) $cpuInfo['cpu']['model'] = implode("<br/>", $cpuInfo['cpu']['model']);
            if(false !== is_array($res['cpu']['mhz'])) $cpuInfo['cpu']['mhz'] = implode("<br/>", $cpuInfo['cpu']['mhz']);
            if(false !== is_array($res['cpu']['cache'])) $cpuInfo['cpu']['cache'] = implode("<br/>", $cpuInfo['cpu']['cache']);
            if(false !== is_array($res['cpu']['bogomips'])) $cpuInfo['cpu']['bogomips'] = implode("<br/>", $cpuInfo['cpu']['bogomips']);
        }
    }

}