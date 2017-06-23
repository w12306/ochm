<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminUserTeam
 *
 * @package App\Models
 */
class AdminUserTeam extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_user_team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_user_id',
        'team',
        'company_ids',
        'partner_ids',
    ];
    /**
     * @var bool
     */
    public $incrementing = true;


    public function adminUser()
    {
        return $this->belongsTo(
                AdminUser::class,
                'admin_user_id',
                'id'
        );
    }

}
