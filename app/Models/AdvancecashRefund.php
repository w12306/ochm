<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 预收款-退款记录
 *
 * @package App\Models
 */
class AdvancecashRefund extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'advancecash_refund';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'advancecash_id',
        'amount',
        'date',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Advancecash()
    {
        return $this->belongsTo(Advancecash::class, 'advancecash_id');
    }

}
