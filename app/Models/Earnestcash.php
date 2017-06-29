<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 保证金
 *
 * @package App\Models
 */
class Earnestcash extends Model
{

    /**
     * @var string
     */
    protected $table = 'earnestcash';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'earnestcash_key',
        'partner_id',
        'reception_time',
        'bill_type',
        'bill_num',
        'bank',
        'amount',
        'last_amount',
        'remark',
        'isshow',
    ];


    /**
     * 更新预付款编号
     */
    public function updateKey()
    {
        $this->earnestcash_key = sprintf('YF%\'010d', $this->attributes['id']);
        $this->save();

        $this->fresh();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partner()
    {
        return $this->belongsTo(
            PartnersModel::class,
            'partner_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function earnestcashMortgage()
    {
        return $this->hasMany(EarnestcashMortgage::class, 'earnestcash_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function earnestcashRefund()
    {
        return $this->hasMany(EarnestcashRefund::class, 'earnestcash_id', 'id');
    }

}
