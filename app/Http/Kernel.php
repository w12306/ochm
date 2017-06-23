<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'csrf'        => \App\Http\Middleware\VerifyCsrfToken::class,

        //管理后台
        'admin.auth'  => \App\Http\Middleware\Admin\Authenticate::class,
        'admin.guest' => \App\Http\Middleware\Admin\Guest::class,

        //前台及接口相关
        //'home.guest' => \App\Http\Middleware\Home\Guest::class,

        //Role
        'role'        => \App\Http\Middleware\VerifyRole::class,
        'permission'  => \App\Http\Middleware\Admin\VerifyPermission::class,
        'level'       => \App\Http\Middleware\VerifyLevel::class,
    ];
}
