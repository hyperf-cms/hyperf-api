<?php
namespace App\Service\Laboratory;

use App\Constants\UploadCode;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use function mysql_xdevapi\getSession;

/**
 * 聊天模块上传服务类
 * Class UploadService
 * @package App\Service\Laboratory
 * @Author YiYuan-Lin
 * @Date: 2021/3/18
 */
class UploadService extends BaseService
{
    use Singleton;

    /**
     * @Inject()
     * @var Filesystem
     */
    private $filesystem;

    /**
     * 上传图片根据base64格式
     * @param string $file
     * @param string $savePath
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadSinglePicByBase64(string $file, string $savePath = '') : array
    {
        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid());
        $dir = './runtime/temp/';
        $imageInfo = base64DecImg($file, $dir, $fileName);
        $uploadPath = $savePath . '/' . $fileName . '.' .$imageInfo['ext'];

        //外网访问的路径
        $fileUrl = env('OSS_URL') . $uploadPath;
        $stream = fopen($imageInfo['storage_dir'], 'r+');
        $this->filesystem->writeStream(
            $uploadPath,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        unlink($imageInfo['storage_dir']);
        return [
            'fileName' => $fileName,
            'url' => $fileUrl
        ];
    }

    /**
     * 上传图片
     * @param object $file
     * @param string $savePath
     * @param string $messageId
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadPic(object $file, string $savePath = '', string $messageId = '') : array
    {
        if ($file->getSize() > 5242880) $this->throwExp(UploadCode::ERR_UPLOAD_SIZE, '上传图片不能超过5M');
        //得到上传文件的后缀
        $fileExt = getExtByFile($file->getClientFilename());

        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid())  . '.' . $fileExt;
        $uploadPath = $savePath . '/' . $fileName;

        //外网访问的路径
        $fileUrl = env('OSS_URL') . $uploadPath;

        $stream = fopen($file->getRealPath(), 'r+');
        $this->filesystem->writeStream(
            $uploadPath,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        return [
            'fileName' => $fileName,
            'url' => $fileUrl,
            'messageId' => $messageId,
        ];
    }

    /**
     * 上传文件
     * @param object $file
     * @param string $savePath
     * @param string $messageId
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadFile(object $file, string $savePath = '', string $messageId = '') : array
    {
        if ($file->getSize() > 20971520) $this->throwExp(UploadCode::ERR_UPLOAD_SIZE, '上传文件不能超过20M');
        //得到上传文件的后缀
        $fileExt = getExtByFile($file->getClientFilename());

        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid())  . '.' . $fileExt;
        $uploadPath = $savePath . '/' . $fileName;

        //外网访问的路径
        $fileUrl = env('OSS_URL') . $uploadPath;

        $stream = fopen($file->getRealPath(), 'r+');
        $this->filesystem->writeStream(
            $uploadPath,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        return [
            'fileName' => $fileName,
            'fileExt' => $fileExt,
            'url' => $fileUrl,
            'messageId' => $messageId,
        ];
    }
}
