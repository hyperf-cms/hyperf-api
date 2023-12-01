<?php
namespace App\Service\Common;

use App\Constants\UploadCode;
use App\Foundation\Traits\Singleton;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;

class UploadService extends BaseService
{
    use Singleton;

    #[Inject]
    private Filesystem $filesystem;

    /**
     * 上传图片
     * @param object $file
     * @param string $savePath
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadSinglePic(object $file, string $savePath = '') : array
    {
        if ($file->getSize() > 30000000) $this->throwExp(UploadCode::ERR_UPLOAD_SIZE, '上传图片尺寸过大');
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
            'url' => $fileUrl
        ];
    }

    /**
     * 上传图片
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
     * 上传图片根据blob文件类型
     * @param object $file
     * @param string $savePath
     * @return array
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadSinglePicByBlob(object $file, string $savePath = '') : array
    {
        if ($file->getSize() > 30000000) $this->throwExp(UploadCode::ERR_UPLOAD_SIZE, '上传图片尺寸过大');

        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid())  . '.' . 'jpg';
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
            'url' => $fileUrl
        ];
    }

    /**
     * 根据文件内容上传图片
     * @param $content
     * @param string $savePath
     * @return string | bool
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadPicByContent(string $content, string $savePath = '')
    {
        if (empty($content)) return false;
        //拼接得到文件名以及对应路径
        $fileName =  md5(uniqid())  . '.' . 'jpg';
        $uploadPath = $savePath . '/' . $fileName;

        //外网访问的路径
        $fileUrl = env('OSS_URL') . $uploadPath;
        $this->filesystem->write(
            $uploadPath,
            $content
        );

        return $fileUrl;
    }
}
