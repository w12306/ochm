<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 预收款
 *
 * @package App\Models
 */
class Advancecash extends Model
{

    /**
     * @var string
     */
    protected $table = 'advancecash';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'advancecash_key',
        'partner_id',
        'reception_time',
        'bill_user',
        'bill_type',
        'bill_num',
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
        $this->advancecash_key = sprintf('YF%\'010d', $this->attributes['id']);
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
    public function advancecashMortgage()
    {
        return $this->hasMany(AdvancecashMortgage::class, 'advancecash_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function advancecashRefund()
    {
        return $this->hasMany(AdvancecashRefund::class, 'advancecash_id', 'id');
    }

}
