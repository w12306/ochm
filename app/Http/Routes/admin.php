<?php

/**
 * 管理后台路由配置
 */

/**
 * 监听二级域名：admin.xxx.com
 * 管理后台的Controller位于Controllers/Admin下
 */
Route::group(['domain' => env('APP_DOMAIN_ADMIN'), 'namespace' => 'Admin'], function () {


    //首页（跳转登陆页）
    Route::get('/', function () {
        return response()->redirectToRoute('admin.login');
    });
    Route::get('/', [
        'as'         => 'admin.index',
        'uses'       => 'AuthController@index',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 用户权限相关
    |--------------------------------------------------------------------------
    */

    //管理后台登陆页
    Route::get('login', [
        'middleware' => ['admin.guest'],
        'as'         => 'admin.login',
        'uses'       => 'AuthController@getLogin',
    ]);
    //管理后台登陆页
    Route::get('get-user', [
        'middleware' => ['admin.guest'],
        'as'         => 'admin.get-user',
        'uses'       => 'AuthController@getUserInfo',
    ]);


    //管理后台登陆请求
    Route::post('login', [
        'uses' => 'AuthController@postLogin',
    ]);



    /*
    |--------------------------------------------------------------------------
    | 需要权限验证的操作
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'authed', 'middleware' => ['admin.auth']], function () {

        //管理员登陆落地页
        Route::get('dashboard', [
            'as'   => 'admin.dashboard',
            'uses' => 'CommonController@dashboard',
        ]);

        //后台用户修改自己密码
        Route::get('password', [
            'as'   => 'admin.password',
            'uses' => 'PermissionController@editUserPw',
        ]);

        /**
         * 顶部导航栏指向板块的落地页
         */
        Route::group(['prefix' => 'common-api'], function () {
            //获得公用地区数据
            Route::get('area', [
                'as'   => 'admin.common-api.area',
                'uses' => 'CommonDataApiController@getArea',
            ]);

        });

        /**
         * 顶部导航栏指向板块的落地页
         */
        Route::group(['prefix' => 'dashboard'], function () {

            //业务管理
            Route::get('business', [
                'as'         => 'admin.business',
                'uses'       => 'BusinessController@getList',
                'middleware' => 'permission:[any]' . implode('|', [
                        'business.view',
                        'business.create',
                        'business.edit',
                        'business.delete',
                        'business.audit',
                    ]),
            ]);

            //TODO 配置
            Route::get('config', [
                'as'         => 'admin.config',
                'uses'       => 'DashboardController@getConfig',
                'middleware' => 'permission:[any]' . implode('|', [
                        'xxx.xxx',
                    ]),
            ]);

        });




    });


});





