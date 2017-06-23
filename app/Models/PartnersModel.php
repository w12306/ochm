<?php
namespace App\Models;

use App\Repositories\InvoiceDeliveryRepository;
use Faker\Provider\da_DK\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class PartnersModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'partners';

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
    public function company()
    {
        return $this->belongsToMany(
                CompanyModel::class,
                'company_partner',
                'partner_id',
                'company_id'
        );
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public  function backcash_invoices(){
        return $this->hasMany(BackcashInvoiceModel::class, 'partner_id', 'id' );
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public  function invoice_invoices(){
        return $this->hasMany(InvoiceDeliveyModel::class, 'partner_id', 'id' );
    }

	/**
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public  function executives(){
		return $this->hasMany(Executive::class, 'partner_id', 'id' );
	}

}
