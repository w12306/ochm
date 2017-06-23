<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use App\Exceptions\Admin\PermissionDeniedException;

class VerifyPermission
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     * @return void
     */
    public function __construct(AdminGuard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param         $request
     * @param Closure $next
     * @param         $permission
     * @return mixed
     * @throws PermissionDeniedException
     */
    public function handle($request, Closure $next, $permission)
    {
        /**
         * 如果是开发环境，默认打开所有权限
         */
        if (env('APP_SUPERADMIN', 0)) {
            return $next($request);
        }

        /**
         * 如果是系统管理员 则打开所有权限
         */
        if(session('isSystem')==1){
            return $next($request);
        }

        $authorized = false;

        /**
         * 支持any、all匹配
         * 例如：
         * permission:[any]p1,p2,p3
         */
        if ($this->auth->check()) {
            if (strpos($permission, '[any]') === 0) {
                $permissions = explode('|', str_replace('[any]', '', $permission));

                if ($this->auth->user()->canOne($permissions)) {
                    $authorized = true;
                }
            } elseif (strpos($permission, '[all]') === 0) {
                $permissions = explode('|', str_replace('[all]', '', $permission));

                if ($this->auth->user()->canAll($permissions)) {
                    $authorized = true;
                }
            } else {
                if ($this->auth->user()->can($permission)) {
                    $authorized = true;
                }
            }
        }

        if ($authorized) {
            return $next($request);
        } else {
            throw new PermissionDeniedException($permission);
        }
    }
}
