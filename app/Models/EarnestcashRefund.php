<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 保证金-退款记录
 *
 * @package App\Models
 */
class EarnestcashRefund extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'earnestcash_refund';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'earnestcash_id',
        'amount',
        'date',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Earnestcash()
    {
        return $this->belongsTo(Earnestcash::class, 'earnestcash_id');
    }

}
