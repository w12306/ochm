<?php

namespace App\Providers\Admin;

use Illuminate\Support\ServiceProvider;
use App\Services\Admin\MenuManager;
use App\Services\Admin\UploadService;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //后台菜单管理
        $this->app->singleton(MenuManager::class, function ($app) {
            return new MenuManager(
                app(\Illuminate\Routing\Router::class),
                app('config')['menus']
            );
        });
        
        //注册后台文件上传服务
        $this->app->singleton(UploadService::class, function ($app) {
            return new UploadService(
                $app->request,
                env('RESOURCE_RELATIVE_PATH'),
                env('SAVED_UPLOAD_RELATIVE_PATH'),
                env('TEMP_UPLOAD_RELATIVE_PATH')
            );
        });
    }

}