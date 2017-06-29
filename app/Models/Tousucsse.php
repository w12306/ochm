<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * @package App\Models
 */
class Tousucsse extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tousucsse';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'ListingId',
        'CreditCode',
        'Rate',
        'Months',
        'Title',
        'Amount',
        'type',
        'message',
        'result',
        'begin_time',
        'end_time',
        'created_at',
        'updated_at',
    ];
    /**
     * @var bool
     */
    public $incrementing = true;


   

}
