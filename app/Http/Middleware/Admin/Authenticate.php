<?php namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use Common\Exceptions\Auth\AdminException;
use Illuminate\Http\Request;
use Config;
use App;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * 登陆验证中间件（未登陆则跳转）
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Middleware
 */
class Authenticate
{

    /**
     * The Guard implementation.
     *
     * @var Guard|AdminGuard
     */
    protected $auth;

    /**
     * @var SessionInterface
     */
    protected $session;


    /**
     * @param Guard|AdminGuard $auth
     */
    public function __construct(AdminGuard $auth, SessionInterface $session)
    {
        $this->auth    = $auth;
        $this->session = $session;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->auth->guest()) {
            return $this->errorResponse($request, '请重新登陆');
        }

        //检查session中的last_token是否和数据库相同
        if (
            $this->session->get('last_token') == '' ||
            ($this->session->get('last_token') != $this->auth->user()->last_token)
        ) {
            $this->auth->logout();

            return $this->errorResponse($request, '你被强制退出（可能在其他地方登陆），请重新登陆');
        }

        return $next($request);
    }

    /**
     * 返回错误信息
     *
     * @param Request $request
     * @param         $errorMessage
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function errorResponse(Request $request, $errorMessage)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => 'error',
                'info'   => $errorMessage,
                'data'   => [],
            ]);
        } else {
            return redirect()->route('admin.login');
        }
    }

}
