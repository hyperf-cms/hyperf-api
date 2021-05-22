<?php

namespace App\Foundation\Utils;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use App\Service\Common\UploadService;

/**
 * 根据头像合成群聊头像
 * Class GroupAvatar
 * @package App\Foundation\Utils
 * @Author YiYuan-Lin
 * @Date: 2021/5/22
 */
class GroupAvatar
{
    /**
     * 图片列表
     * @var array
     */
    private static $picList = [];

    /**
     * 是否保存
     * @var bool
     */
    private static $isSave = true;

    /**
     * 保存路径
     * @var string
     */
    private static $savePath = '';

    /**
     * 画布宽度
     * @var int
     */
    public static $width = 150;

    /**
     * 画布高度
     * @var int
     */
    public static $height = 150;

    /**
     * 初始化一些参数
     * @param array $picList
     * @param bool $isSave
     * @param string $savePath
     */
    public static function init(array $picList, bool $isSave = true, string $savePath = 'chat/group/avatar')
    {
        self::$picList = $picList;
        self::$isSave = $isSave;
        self::$savePath = $savePath;
    }

    /**
     * 生成图片
     * @return bool|string
     * @throws \League\Flysystem\FileExistsException
     */
    public static function build() : string
    {
        //验证参数
        if(empty(self::$picList)) throw new BusinessException(StatusCode::ERR_VALIDATION, '图片列表数据不能为空');
        //如果需要保存，需要传保存地址
        if(self::$isSave && empty(self::$savePath)) throw new BusinessException(StatusCode::ERR_VALIDATION, '图片保存路径不能为空');

        $res = self::generateCanvas();
        if (!$res) throw new BusinessException(StatusCode::ERR_EXCEPTION, '合成图片失败');
        return $res;
    }

    /**
     * 生成画布
     * @return bool|string
     * @throws \League\Flysystem\FileExistsException
     */
    private static function generateCanvas()
    {
        // 只操作前9个图片
        $picList = array_slice(self::$picList, 0, 9);
        //新建一个真彩色图像作为背景
        $background = imagecreatetruecolor(self::$width, self::$height);
        //为真彩色画布创建白灰色背景，再设置为透明
        $color = imagecolorallocate($background, 202, 201, 201);
        imagefill($background, 0, 0, $color);
        imageColorTransparent($background, $color);
        //根据图片个数设置图片位置
        $picCount = count($picList);
        $lineArr = array();//需要换行的位置
        $space_x = 3;
        $space_y = 3;
        $line_x = 0;
        switch($picCount) {
            case 1:
                //正中间
                $start_x = intval(self::$width / 4); // 开始位置X
                $start_y = intval(self::$height / 4); // 开始位置Y
                $pic_w = intval(self::$width / 2); // 宽度
                $pic_h = intval(self::$height / 2); // 高度
                break;
            case 2:
                //中间位置并排
                $start_x = 2;
                $start_y = intval(self::$height / 4) + 3;
                $pic_w = intval(self::$width / 2) - 5;
                $pic_h = intval(self::$height / 2) - 5;
                $space_x = 5;
                break;
            case 3:
                $start_x = 40; // 开始位置X
                $start_y = 5; // 开始位置Y
                $pic_w = intval(self::$width / 2) - 5; // 宽度
                $pic_h = intval(self::$height / 2) - 5; // 高度
                $lineArr = array(2);
                $line_x = 4;
                break;
            case 4:
                $start_x = 4; // 开始位置X
                $start_y = 5; // 开始位置Y
                $pic_w = intval(self::$width / 2) - 5; // 宽度
                $pic_h = intval(self::$height / 2) - 5; // 高度
                $lineArr = array(3);
                $line_x = 4;
                break;
            case 5:
                $start_x = 30; // 开始位置X
                $start_y = 30; // 开始位置Y
                $pic_w = intval(self::$width / 3) - 5; // 宽度
                $pic_h = intval(self::$height / 3) - 5; // 高度
                $lineArr = array(3);
                $line_x = 5;
                break;
            case 6:
                $start_x = 5; // 开始位置X
                $start_y = 30; // 开始位置Y
                $pic_w = intval(self::$width / 3) - 5; // 宽度
                $pic_h = intval(self::$height / 3) - 5; // 高度
                $lineArr = array(4);
                $line_x = 5;
                break;
            case 7:
                $start_x = 53; // 开始位置X
                $start_y = 5; // 开始位置Y
                $pic_w = intval(self::$width / 3) - 5; // 宽度
                $pic_h = intval(self::$height / 3) - 5; // 高度
                $lineArr = array(2,5);
                $line_x = 5;
                break;
            case 8:
                $start_x = 30; // 开始位置X
                $start_y = 5; // 开始位置Y
                $pic_w = intval(self::$width / 3) - 5; // 宽度
                $pic_h = intval(self::$height / 3) - 5; // 高度
                $lineArr = array(3,6);
                $line_x = 5;
                break;
            case 9:
                $start_x = 5; // 开始位置X
                $start_y = 5; // 开始位置Y
                $pic_w = intval(self::$width / 3) - 5; // 宽度
                $pic_h = intval(self::$height / 3) - 5; // 高度
                $lineArr = array(4,7);
                $line_x = 5;
                break;
        }
        foreach($picList as $k => $pic_path ) {
            $kk = $k + 1;
            if ( in_array($kk, $lineArr) ) {
                $start_x = $line_x;
                $start_y = $start_y + $pic_h + $space_y;
            }
            //获取图片文件扩展类型和mime类型，判断是否是正常图片文件
            //非正常图片文件，相应位置空着，跳过处理
            $image_mime_info = @getimagesize($pic_path);
            if($image_mime_info && !empty($image_mime_info['mime'])){
                $mime_arr = explode('/',$image_mime_info['mime']);
                if(is_array($mime_arr) && $mime_arr[0] == 'image' && !empty($mime_arr[1])){
                    switch($mime_arr[1]) {
                        case 'jpg':
                        case 'jpeg':
                            $imageCreateFromJpeg = 'imagecreatefromjpeg';
                            break;
                        case 'png':
                            $imageCreateFromJpeg = 'imagecreatefrompng';
                            break;
                        case 'gif':
                        default:
                            $imageCreateFromJpeg = 'imagecreatefromstring';
                            $pic_path = file_get_contents($pic_path);
                            break;
                    }
                    //创建一个新图像
                    $resource = $imageCreateFromJpeg($pic_path);
                    //将图像中的一块矩形区域拷贝到另一个背景图像中
                    // $start_x,$start_y 放置在背景中的起始位置
                    // 0,0 裁剪的源头像的起点位置
                    // $pic_w,$pic_h copy后的高度和宽度
                    imagecopyresized($background, $resource, $start_x, $start_y, 0, 0, $pic_w, $pic_h, imagesx($resource), imagesy($resource));
                }
            }
            // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $start_x = $start_x + $pic_w + $space_x;
        }
        ob_start();
        imagejpeg($background);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($background);

        $fileUrl = UploadService::getInstance()->uploadPicByContent($imageData, self::$savePath);

        return $fileUrl;
    }
}