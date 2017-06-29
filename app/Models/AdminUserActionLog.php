<?php

namespace App\Models;

use Bican\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * 管理员操作日志
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Models
 */
class AdminUserActionLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_user_action_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'main_id',
        'message',
        'change_log',
        'admin_user_id',
        'created_at',
    ];

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id', 'id');
    }

}
