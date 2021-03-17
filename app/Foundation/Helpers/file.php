<?php
/**
 * 文件相关函数
 */
if (!function_exists('base64DecImg')) {
    /**
     * 反编译data/base64数据流并创建图片文件
     * @author Yiyuan-Lin
     * @param string $baseData  data/base64数据流
     * @param string $dir 存放图片文件目录
     * @param string $fileName   图片文件名称(不含文件后缀)
     * @return mixed 返回新创建文件路径或布尔类型
     */
    function base64DecImg($baseData, $dir = '', $fileName = ''){
        $expData = explode(';', $baseData);
        $postfix   = explode('/', $expData[0]);
        if(strstr($postfix[0], 'image') ){
            if(!is_readable($dir)) mkdir($dir, 0700);
            $postfix   = $postfix[1] == 'jpeg' ? 'jpg' : $postfix[1];
            $storageDir = $dir . $fileName . '.' . $postfix;
            $export = base64_decode(str_replace("{$expData[0]};base64,", '', $baseData));
            file_put_contents($storageDir, $export);
            return [
                'storage_dir' => $storageDir,
                'ext' => $postfix,
            ];
        }
    }
}

