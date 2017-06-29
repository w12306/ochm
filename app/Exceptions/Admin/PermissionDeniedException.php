<?php

namespace App\Exceptions\Admin;

use Bican\Roles\Exceptions\AccessDeniedException;

/**
 * 没有权限
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Exceptions\Admin
 */
class PermissionDeniedException extends AccessDeniedException
{

    /**
     * Create a new permission denied exception instance.
     *
     * @param string $permission
     */
    public function __construct($permission)
    {
        $this->message = sprintf("你没有权限进行该操作，请联系业务线管理员。 权限代码：['%s']", $permission);
    }

}