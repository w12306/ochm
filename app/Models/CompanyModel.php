<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class CompanyModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'company';

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
            'company_name',
            'level',
            'team',
            'partner_id',
            'understand_yly',
            'understand_bar',
            'ifbudget',
            'address',
            'boss',
            'media',
            'contacts',
            'remark',
            'userid',
            'real_name',
    ];


    /**
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partners()
    {
        return $this->belongsToMany(
                PartnersModel::class,
                'company_partner',
                'company_id',
                'partner_id'
        );
    }

    public function companyTeams()
    {
        return $this->hasMany(
                CompanyTeamModel::class,
                'company_id',
                'id'
        );
    }
    public function companyPartners()
    {
        return $this->hasMany(
                CompanyPartnerModel::class,
                'company_id',
                'id'
        );
    }

    public function products()
    {
        return $this->hasMany(
                    ProductModel::class,
                    'company_id',
                    'id'
        );
    }

    public function business(){
        return $this->hasMany(BusinessModel::class,'company_id','id');
    }

	/**
	 *
	 * @author YangWei<yangwei@foxmail.com>
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public  function executives(){
		return $this->hasMany(Executive::class, 'company_id', 'id' );
	}



}
