<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;

/**
 * 上传服务管家
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Services\Admin
 */
class UploadServiceManager
{

    /**
     * @var UploadService
     */
    protected $uploadService;

    /**
     * @var UploadService
     */
    protected $adpsresUploadService;


    /**
     * @param Request $request
     */
    public function __construct(Application $app)
    {
        $this->uploadService = $app->make(UploadService::class);
    }

    /**
     * @return UploadService
     */
    public function getUploadService()
    {
        return $this->uploadService;
    }

}