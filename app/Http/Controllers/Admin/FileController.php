<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Services\Admin\UploadServiceManager;
use Linker;
use App;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * 处理文件、图片上传的控制器
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers\Admin
 */
class FileController extends Controller
{

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * 处理文件上传
     *
     * @param UploadServiceManager $uploadServiceManager
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postUploadFile(UploadServiceManager $uploadServiceManager, Request $request)
    {
        $uploadService = $uploadServiceManager->getUploadService();
        try {
            $uploaded = $uploadService->upload('Filedata', [
                'allowedMimeExtensions' => [
                    'xls',
                    'xlsx',
                    'xlam',
                    'xlsb',
                    'xlsm',
                    'xltm',
                    'xltx',
                    'doc',
                    'docx',
                    'txt',
                    'pdf',
                    'png',
                    'jpeg',
                    'zip',
                    'rar',
                ],
                'alsoAllowedMimeTypes'  => [
                    'application/vnd.ms-office',
                ],
                'maxFileSize'           => '20480',
            ]);

            return $this->ajaxSuccess('', [
                'uploaded' => $uploaded,
                'url'      => Linker::resource($uploaded)
            ]);
        } catch (\Exception $e) {
            return $this->ajaxError('上传失败：' . $e->getMessage());
        }
    }

}
