<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**下游客户关联下游合作方
 * Class BusinessTeamModel
 * @package App\Models
 */
class UnderCompanyPartnerModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'under_company_partner';


    public $incrementing = false;



    /**
     * @var array
     */
    protected $fillable = [
            'company_id',
            'partner_id',
    ];


}
