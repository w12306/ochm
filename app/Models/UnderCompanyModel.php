<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class UnderCompanyModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'under_company';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['deleted_at'];

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
            'updated_at',
            'created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function underpartners()
    {
        return $this->belongsToMany(
                UnderPartnersModel::class,
                'under_company_partner',
                'company_id',
                'partner_id'
        );
    }





}
