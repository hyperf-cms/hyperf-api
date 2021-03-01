<?php
if (!function_exists('get_os')) {
    /**
     * 获取操作系统
     * @return string
     */
    function get_os() {
        $request = new \Hyperf\HttpServer\Request();
        if (!empty($request->getHeader('user-agent'))) {
            $OS = $request->getHeader('user-agent')[0];
            if (preg_match('/win/i', $OS)) {
                $OS = 'Windows';
            } elseif (preg_match('/mac/i', $OS)) {
                $OS = 'MAC';
            } elseif (preg_match('/linux/i', $OS)) {
                $OS = 'Linux';
            } elseif (preg_match('/unix/i', $OS)) {
                $OS = 'Unix';
            } elseif (preg_match('/bsd/i', $OS)) {
                $OS = 'BSD';
            } else {
                $OS = 'Other';
            }
            return $OS;
        } else {
            return "获取访客操作系统信息失败！";
        }
    }
}


if (!function_exists('get_browser_os')) {
    /**
     * 获取浏览器型号
     * @return string
     */
    function get_browser_os() {
        $request = new \Hyperf\HttpServer\Request();
        if (!empty($request->getHeader('user-agent'))) {
            $br = $request->getHeader('user-agent')[0];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } elseif (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } elseif (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } elseif (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } elseif (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return "获取浏览器信息失败！";
        }
    }
}