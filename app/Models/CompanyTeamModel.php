<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**客户与部门小组关系表
 * Class BusinessTeamModel
 * @package App\Models
 */
class CompanyTeamModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'company_team';


    public $incrementing = false;



    /**
     * @var array
     */
    protected $fillable = [
            'type',
            'company_id',
            'team',
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
