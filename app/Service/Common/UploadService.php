<?php
namespace App\Http\Service\Common;

use App\Constants\StatusCode;
use App\Constants\UploadCode;
use App\Foundation\Traits\Singleton;
use App\Http\Service\BaseService;
use Hyperf\Di\Annotation\Inject;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;


class UploadService extends BaseService
{
    use Singleton;

    /**
     * @Inject()
     * @var Filesystem
     */
    private $filesystem;

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
}
