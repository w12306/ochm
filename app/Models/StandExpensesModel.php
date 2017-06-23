<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**独立支出信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class StandExpensesModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'stand_expenses';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'expenses_key',
            'expenses_type',
            'under_partner_id',
            'cooperation_type',
            'contract_key',
            'payment_time',
            'contract_btime',
            'contract_etime',
            'amount',
            'remark',
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
    public function under_partner()
    {
        return $this->belongsTo(UnderPartnersModel::class,'under_partner_id','id' );
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stand_payments()
    {
        return $this->hasMany(StandPayment::class,'expenses_id','id' );
    }



}
