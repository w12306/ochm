<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**业务小组资金分配
 * Class BusinessTeamModel
 * @package App\Models
 */
class CompanyPartnerModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'company_partner';


    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
            'type',
            'company_id',
            'partner_id',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];

    public function companys()
    {
        return $this->belongsToMany(
                CompanyModel::class,
                'company_id',
                'id'
        );
    }
}
