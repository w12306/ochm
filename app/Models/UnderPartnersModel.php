<?php
namespace App\Models;

use Faker\Provider\da_DK\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class UnderPartnersModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'under_partners';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'company_type',
            'company_name',
            'balance',
            'taxkey',
            'nickname',
            'address',
            'tel',
            'boss',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];

    static public $typeArray=[1=>'代理', 2=>'直客'];

    //static public $cooperation_Array=['CPA'=>'CPA', 'CPS'=>'CPS','CPL'=>'CPL'];//下游合作方式


    /**
     * @return string
     */
    public function getCompanyTypeTextAttribute()
    {
        return static::$typeArray[$this->attributes['company_type']];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function undercompany()
    {
        return $this->belongsToMany(
                UnderCompanyModel::class,
                'under_company_partner',
                'partner_id',
                'company_id'
        );
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expenses()
    {
        return $this->hasMany(ExpensesModel::class,'under_partner_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expenses_deliveys()
    {
        return $this->hasMany(ExpensesDeliveyModel::class,'under_partner_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stand_expensess()
    {
        return $this->hasMany(StandExpensesModel::class,'id','under_partner_id');
    }

}
