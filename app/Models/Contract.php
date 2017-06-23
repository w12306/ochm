<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合同
 *
 * @package App\Models
 */
class Contract extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'contract';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'status',
        'ckey',
        'type',
        'parent_id',
        'signtime',
        'savetime',
        'btime',
        'etime',
        'amount',
        'filepath',
        'userid',
        'remark',
    ];

    /**
     * 合同类型
     *
     * @var array
     */
    static public $typeList = [
        1 => '框架合同',
        2 => '子合同',
        3 => '独立合同',
        //4 => '特殊无合同', //应为业务关联合同时的标识属性，合同当中并不存在这个类型
    ];

    const TYPE_FRAMEWORK   = 1;
    const TYPE_SUB         = 2;
    const TYPE_INDEPENDENT = 3;

    /**
     * 合同状态
     *
     * @var array
     */
    static public $statusList = [
        0 => '未处理',
        1 => '已发合同未回',
        2 => '合同已存档',
    ];

    const STATUS_SENDED   = 1;
    const STATUS_ARCHIVED = 2;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function businesses()
    {
        return $this->belongsToMany(
            BusinessModel::class,
            'contract_business',
            'contract_id',
            'business_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function business()
    {
        return $this->hasMany(BusinessModel::class,'contract_id','id');
    }

    /**
     * @return string
     */
    public function getTypeTextAttribute()
    {
        return static::$typeList[$this->attributes['type']];
    }

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        return static::$statusList[$this->attributes['status']];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contractfiles()
    {
        return $this->hasMany(ContractFiles::class,'contract_id','id');
    }

    /**
     * 是否为框架合同
     *
     * @return bool
     */
    public function isFramework()
    {
        return $this->attributes['type'] == static::TYPE_FRAMEWORK;
    }

}
