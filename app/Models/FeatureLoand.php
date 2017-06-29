<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * @package App\Models
 */
class FeatureLoand extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'feature_loand';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'ListingId',
        'CreditCode',
        'Rate',
        'Months',
        'Title',
        'Amount',
        'created_at',
        'updated_at',
    ];
    /**
     * @var bool
     */
    public $incrementing = false;


   

}
