<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use Common\Packages\Uams\Authenticate\Contracts\Factory as UamsManager;
use Common\Packages\Uams\Contracts\Api as UamsApiContract;
use Illuminate\Contracts\Auth\Guard;
use App\Repositories\AdminUserRepository;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * 【ABMP已全面切换到用户名密码登录，UAMS登录暂时废弃】
 * 管理员权限接入UAMS相关操作
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers
 */
class UamsAuthController extends Controller
{

    /**
     * @var AdminGuard|Guard
     */
    protected $auth;

    /**
     * UAMS API对象
     *
     * @var UamsApiContract
     */
    protected $uamsApi;

    /**
     * 登陆地址
     *
     * @var string
     */
    protected $loginPath;


    /**
     * @param Guard|AdminGuard $auth
     * @param UamsApiContract  $uamsApi
     */
    public function __construct(AdminGuard $auth,
                                UamsApiContract $uamsApi)
    {
        $this->auth      = $auth;
        $this->uamsApi   = $uamsApi;
        $this->loginPath = $uamsApi->loginUrl();
    }

    /**
     * 登出并跳转到UAMS
     */
    public function getLogout()
    {
        $this->auth->logout();

        //跳转到UAMS
        return redirect($this->loginPath());
    }

    /**
     * 登陆页（跳转到UAMS）
     */
    public function getLogin()
    {
        return redirect($this->loginPath());
    }

    /**
     * 得到登陆失败的错误信息
     *
     * @return string
     */
    protected function getFailedLoginMessage()
    {
        return '登录失败，请从UAMS（统一用户中心）登陆。';
    }

    /**
     * 得到登陆成功后的跳转URL
     *
     * @return string
     */
    public function redirectPath()
    {
        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/authed/dashboard';
    }

    /**
     * 得到登陆地址
     *
     * @return string
     */
    public function loginPath()
    {
        return $this->loginPath;
    }

    /**
     * UAMS跳转回调登陆
     */
    public function getUamsLogin(UamsManager $uamsAuthManager,
                                 AdminUserRepository $adminUserRepository,
                                 SessionInterface $session)
    {
        //验证回调地址，并从UAMS得到用户信息
        $user = $uamsAuthManager->with('callback')->user();

        //使用UAMS用户获取本地用户或者生成新本地用户
        $user = $adminUserRepository->findByUsernameOrCreate($user);

        //生成新的last_token
        //last_token字段是用来限制用户只能在一处登陆
        $lastToken = Str::random(8);
        $adminUserRepository->update($user->id, [
            'last_token' => $lastToken,
        ]);
        $session->set('last_token', $lastToken);

        //设置登陆
        $this->auth->login($user, true);

        //刷新UAMS菜单缓存
        //$this->clearUamsMenuCache();
        return redirect()->intended($this->redirectPath());
    }

    /**
     * 刷新UAMS菜单缓存
     */
    protected function clearUamsMenuCache()
    {
        $uamsMenu = App::make('Common\Packages\Uams\One\Menu');
        $uamsMenu->clearCache();
    }

    /**
     * ［测试环境］绕过UAM，直接登陆
     */
    public function getDirectLogin(UamsManager $uamsAuthManager, AdminUserRepository $adminUserRepository)
    {
        //必须为开发环境
        if ( ! app()->environment('local')) {
            return response('unauthorized', 500);
        }

        //使用UAMS用户获取本地用户或者生成新本地用户
        $user = $adminUserRepository->find(124);

        //设置登陆
        $this->auth->login($user, true);

        //刷新UAMS菜单缓存
        $this->clearUamsMenuCache();

        return redirect()->intended($this->redirectPath());
    }

}
