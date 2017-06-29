<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * 大分类首页
 *
 * @package App\Http\Controllers\Admin
 */
class DashboardController extends Controller
{

    public function __construct()
    {

    }

    /**
     * 配置
     *
     * @return \Illuminate\View\View
     */
    public function getConfig()
    {
        return view('admin.dashboard.config');
    }

    /**
     * 广告管理
     *
     * @return \Illuminate\View\View
     */
    public function getAd()
    {
        return view('admin.dashboard.ad');
    }

}
