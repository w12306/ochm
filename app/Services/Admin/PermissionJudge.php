<?php

namespace App\Services\Admin;

use Common\Packages\Admin\Contracts\Guard;
use App\Models\AdminUser;

/**
 * Class PermissionJudge
 *
 * @package App\Services\Admin
 */
class PermissionJudge
{

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|AdminUser
     */
    protected $adminUser;


    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth      = $auth;
        $this->adminUser = $auth->user();
    }

    /**
     * 判断有没有权限
     * 支持以下形式：
     * 1. '*'
     * 2. 'permission'
     * 3. ['condition?', ['permission1', 'permission2']]
     * condition可以为：any,all
     *
     * @param $permission
     * @return bool
     */
    public function isAllowed($permission)
    {
        /**
         * 如果是开发环境，默认打开所有权限
         */
        if (env('APP_SUPERADMIN', 0)) {
            return true;
        }
        /**
         * 如果是系统管理员 则打开所有权限
         */
        if(session('isSystem')==1){
            return true;
        }

        if ($permission == '' || $permission == '*') {
            return true;
        } elseif (is_array($permission)) {
            list($condition, $permissions) = $permission;

            if (empty($permissions)) {
                return true;
            }

            if ($condition == 'any') {
                return $this->adminUser->canOne($permissions);
            } elseif ($condition == 'all') {
                return $this->adminUser->canAll($permissions);
            }

        } else {
            return $this->adminUser->can($permission);
        }
    }


}