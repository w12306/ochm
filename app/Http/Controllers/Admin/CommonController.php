<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use Common\Packages\Admin\Contracts\Guard;
use Cache;
use App;
use Predis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Routing\Router;

/**
 * 在所有项目的管理后台都具有的操作就存在这个控制器
 * 例如登陆后的落地页
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers
 */
class CommonController extends Controller
{

    /**
     * 权限管理类
     *
     * @var Guard
     */
    protected $auth;

    public function  __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * 后台落地页
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request, Router $router)
    {
        return view('admin.dashboard');
    }




}
