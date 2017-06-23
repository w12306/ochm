<?php
namespace App\Http\Controllers\Admin;

use App\Constants\ApiConstant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 登陆授权管理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct(
    ) {
    }

    public function index(Request $request)
    {
        dd(date('Y-m-d H:i:s',time()).'ssss');

    }
    
    public function postLogin(Request $request)
    {
    }

    public function getLogin()
    {
        return redirect($this->loginPath);
    }

    /**
     * 登出并跳转到登陆页面
     */
    public function getLogout()
    {
        $this->auth->logout();
        //跳转到UAMS
        return redirect(route('admin.login'));
    }
}