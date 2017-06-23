<?php
namespace App\Models;

use Faker\Provider\sk_SK\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class BusinessModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'business';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = [
            'deleted_at',
            'updated_at',
            'created_at',];
    /**
     * @var array
     */
    protected $fillable = [
        'business_key',
        'company_id',
        'partner_id',
        'product_id',
        'business_type',
        'business_line',
        'contract_id',
        'contract_type',
        'btime',
        'etime',
        'paytime',
        'amount',
        'active_amount',
        'confirm_status',
        'payment_status',
        'audit_status',
        'backcash_status',
        'team',
        'remark',
		'executive_id',
        'del_time',
    ];

    /**
     * @var array
     */
    protected $hidden = [

    ];

    static public $confirm_status = [
        0 => '未发',
        1 => '已发未确认',
        2 => '已发已确认',
    ];
    //业务状态
    static public $audit_status = [
            0 => '等待审核',
            1 => '审核通过',
            2 => '审核未通过',
            -1 => '已删除' ,
    ];

    //业务这边显示的合同类型/创建业务时用到
    static public $contract_status = [
            1 => '框架合同',
            //2 => '子合同',
            3 => '独立合同',
            4 => '特殊无合同',
    ];

    /**
     *返回 确认函状认
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param string $value
     * @return array
     */
    public function getConfirmStatus($value = '')
    {
        $returnArr = [];
        foreach ($this->confirm_status as $k => $v) {
            $select = 0;
            if ($k === $value) {
                $select = 1;
            }
            $returnArr[] = ['key' => $k, 'value' => $v, 's' => $select];
        }

        return $returnArr;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contracts()
    {
        return $this->belongsToMany(
            Contract::class,
            'contract_business',
            'business_id',
            'contract_id'
        );
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id','id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businessteams()
    {
        return $this->hasMany(BusinessTeamModel::class,'business_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(PartnersModel::class, 'partner_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(CompanyModel::class, 'company_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliverys()
    {
        return $this->hasMany(DeliveryModel::class,'business_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses_deliveys()
    {
        return $this->hasMany(ExpensesDeliveyModel::class,'business_key','business_key');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class,'business_key','business_key');
    }

    /**
     * @return string
     */
    public function getAuditStatusTextAttribute()
    {
        return static::$audit_status[$this->attributes['audit_status']];
    }


    public function getContractType()
    {
        return static::$contract_status;
    }

    /**
     * @return string
     */
    public function getConfirmStatusTextAttribute()
    {
        return static::$confirm_status[$this->attributes['confirm_status']];
    }


    public  function payment_expensess(){
        return $this->hasMany(PaymentExpenses::class,'business_key','business_key');
    }

    public function invoice_deliveys(){
        return $this->hasMany(InvoiceDeliveyModel::class,'business_key','business_key');
    }

    public function badcashs(){
        return $this->hasMany(Badcash::class,'business_id','id');
    }


}
