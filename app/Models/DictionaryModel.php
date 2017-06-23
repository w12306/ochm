<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class DictionaryModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'dictionary';

    protected $primaryKey = 'id';

    public $incrementing = false;


    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'type',
            'key',
            'value',
            'parent_id',
            'v1',
            'v2',
            'status',
            'remark',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];

    /**数据项类型
     * @var array
     */
    static public $typeData=[
            'business_cooperation'   =>'合作方式',
            'business_type' =>'业务类型',
            'business_line' =>'业务线',
            'dept'          =>'部门',
            'team'          =>'执行小组',
            'product_type'  =>'产品类型',
            'bank'          =>'回款银行',
            'backcash_type' =>'回款类型',
            'invoice_type'  =>'发票类型',
            'cooperation_type'   =>'下游合作形式',
            //'bill_type'     =>'票据',
            //'contract_type' =>'合同类型',
            'expenses_type' =>'支出类型',
           // 'rec_status'    =>'合同执行状态',
    ];
    //状态
    static public $status = [
            0 => '停用',
            1 => '启用',
    ];

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        return static::$status[$this->attributes['status']];
    }

}
