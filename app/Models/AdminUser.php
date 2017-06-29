<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Common\Packages\Admin\Contracts\UserIdentity;
use Bican\Roles\Traits\HasRoleAndPermission;
use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

/**
 * 后台 用户类
 *
 * @package App\Models
 */
class AdminUser extends Model implements AuthenticatableContract,
                                         UserIdentity,
                                         HasRoleAndPermissionContract
{
    use Authenticatable, HasRoleAndPermission;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'status',
        'username',
        'real_name',
        'password',
        'email',
        'uams_user_id',
        'last_token',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * 用户状态
     *
     * @var array
     */
    static public $statusList = [
        0 => '已停用',
        1 => '正常',
    ];

    const STATUS_SUSPENDED = 0;
    const STATUS_NORMAL    = 1;


    /**
     * TODO
     */
    public function teams()
    {
        return $this->hasMany(
            AdminUserTeam::class,
            'admin_user_id',
            'id'
        );
    }

    /**
     * User belongs to many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            config('roles.models.role'),
            'role_user',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * User belongs to many permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userPermissions()
    {
        return $this->belongsToMany(
            config('roles.models.permission'),
            'permission_user',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->real_name;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * 得到UAMS的用户ID
     *
     * @return string
     */
    public function getUamsUserId()
    {
        return (int)$this->uams_user_id;
    }

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        return ($this->attributes['status']==1)?'启用':'禁用';
    }

}
