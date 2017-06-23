<?php namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use Illuminate\Http\RedirectResponse;
use App;

/**
 * 未登陆验证中间件（如果登陆则跳转）
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Middleware
 */
class Guest
{

    /**
     * The Guard implementation.
     *
     * @var Guard|AdminGuard
     */
    protected $auth;


    /**
     * @param Guard|AdminGuard $auth
     */
    public function __construct(AdminGuard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check()) {
            return new RedirectResponse(url('/authed/dashboard'));
        }

        return $next($request);
    }

}
