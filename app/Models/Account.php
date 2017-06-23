<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * @package App\Models
 */
class Account extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'account';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'open_id',
        'access_token',
        'refresh_token',
        'expires_in',
        'created_at',
        'updated_at',
    ];


    /**
     * @var bool
     */
    public $incrementing = true;


   

}
